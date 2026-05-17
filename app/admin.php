<?php
/**
 * Helpers para el panel super admin.
 *
 * Métricas SaaS:    admin_metrics()
 * Licencias:        license_*
 * System settings:  setting_get / setting_set
 * Impersonation:    admin_impersonate_start / stop / is_impersonating
 * Anuncios:         announcements_active_for()
 */

// =====================================================================
// MÉTRICAS SAAS
// =====================================================================

/**
 * MRR (Monthly Recurring Revenue) basado en tenants activos.
 * Convierte planes anuales a equivalente mensual.
 */
function admin_mrr(): float {
    $row = db_one("
        SELECT COALESCE(SUM(
            CASE
                WHEN p.billing_cycle = 'yearly'   THEN p.price_usd / 12
                WHEN p.billing_cycle = 'lifetime' THEN 0
                ELSE p.price_usd
            END
        ), 0) m
        FROM tenants t
        JOIN plans p ON p.id = t.plan_id
        WHERE t.status = 'active'
    ");
    return (float)($row['m'] ?? 0);
}

function admin_arr(): float { return admin_mrr() * 12; }

/** Average Revenue Per User (tenant activo). */
function admin_arpu(): float {
    $row = db_one('SELECT COUNT(*) c FROM tenants WHERE status = "active"');
    $count = (int)($row['c'] ?? 0);
    if ($count === 0) return 0.0;
    return admin_mrr() / $count;
}

/** Churn rate (cancelados últimos 30d / activos hace 30d). */
function admin_churn_rate(): float {
    $cancelled = (int)(db_one("
        SELECT COUNT(*) c FROM tenants
        WHERE status = 'cancelled' AND updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")['c'] ?? 0);

    $base = (int)(db_one("
        SELECT COUNT(*) c FROM tenants
        WHERE status IN ('active','cancelled')
          AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")['c'] ?? 0);

    if ($base === 0) return 0.0;
    return ($cancelled / $base) * 100;
}

/** Trial → active conversion rate (90d). */
function admin_conversion_rate(): float {
    $row = db_one("
        SELECT
            (SELECT COUNT(*) FROM tenants WHERE created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)) as total,
            (SELECT COUNT(*) FROM tenants WHERE status = 'active' AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)) as conv
    ");
    $t = (int)($row['total'] ?? 0);
    if ($t === 0) return 0.0;
    return ((int)($row['conv'] ?? 0) / $t) * 100;
}

function admin_growth_30d(): array {
    return db_one("
        SELECT
            (SELECT COUNT(*) FROM tenants WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as new30,
            (SELECT COUNT(*) FROM tenants WHERE status = 'cancelled' AND updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as cancel30
    ") ?: ['new30' => 0, 'cancel30' => 0];
}

/** Series 30d para chart de signups vs cancellations. */
function admin_signups_series(int $days = 30): array {
    $rows = db_select("
        SELECT DATE(created_at) d, COUNT(*) c FROM tenants
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :n DAY)
        GROUP BY DATE(created_at) ORDER BY d ASC
    ", ['n' => $days]);
    $map = [];
    foreach ($rows as $r) $map[$r['d']] = (int)$r['c'];
    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $series[] = ['d' => $d, 'c' => $map[$d] ?? 0];
    }
    return $series;
}

function admin_revenue_series(int $days = 30): array {
    $rows = db_select("
        SELECT DATE(paid_at) d, COALESCE(SUM(amount_usd),0) s
        FROM admin_invoices
        WHERE status = 'paid' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL :n DAY)
        GROUP BY DATE(paid_at) ORDER BY d ASC
    ", ['n' => $days]);
    $map = [];
    foreach ($rows as $r) $map[$r['d']] = (float)$r['s'];
    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $series[] = ['d' => $d, 's' => $map[$d] ?? 0];
    }
    return $series;
}

/** Resumen de salud del SaaS. */
function admin_health(): array {
    return [
        'docs_total'         => (int)(db_one('SELECT COUNT(*) c FROM documents')['c'] ?? 0),
        'storage_mb'         => (int)round(((int)(db_one('SELECT COALESCE(SUM(size_bytes),0) s FROM documents')['s'] ?? 0)) / 1024 / 1024),
        'audit_today'        => (int)(db_one('SELECT COUNT(*) c FROM audit_log WHERE DATE(created_at) = CURDATE()')['c'] ?? 0),
        'failed_logins_24h'  => (int)(db_one("SELECT COUNT(*) c FROM audit_log WHERE event = 'auth.login.failed' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")['c'] ?? 0),
        'permission_denied_7d' => (int)(db_one("SELECT COUNT(*) c FROM audit_log WHERE event = 'auth.permission.denied' AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0),
        'workflows_runs_24h' => (int)(db_one("SELECT COUNT(*) c FROM workflow_runs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")['c'] ?? 0),
    ];
}

// =====================================================================
// LICENCIAS
// =====================================================================

function license_generate_key(): string {
    // KYR-XXXX-XXXX-XXXX-XXXX (4 grupos de 4 hex en mayúsculas)
    $hex = strtoupper(bin2hex(random_bytes(8)));
    return 'KYR-' . substr($hex, 0, 4) . '-' . substr($hex, 4, 4) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4);
}

function license_create(int $tenant_id, ?int $plan_id = null, ?int $valid_days = null, ?string $notes = null, ?int $issued_by = null): array {
    $key = license_generate_key();
    $expires = $valid_days ? date('Y-m-d H:i:s', strtotime("+{$valid_days} days")) : null;
    db_run('
        INSERT INTO license_keys (tenant_id, license_key, plan_id, expires_at, issued_by, notes)
        VALUES (:t,:k,:p,:e,:b,:n)
    ', ['t'=>$tenant_id, 'k'=>$key, 'p'=>$plan_id, 'e'=>$expires, 'b'=>$issued_by, 'n'=>$notes]);
    return ['key' => $key, 'expires' => $expires];
}

function license_revoke(int $license_id): void {
    db_run("UPDATE license_keys SET status = 'revoked', revoked_at = NOW() WHERE id = :id", ['id' => $license_id]);
}

function license_list_for_tenant(int $tenant_id): array {
    return db_select('
        SELECT l.*, p.name AS plan_name FROM license_keys l
        LEFT JOIN plans p ON p.id = l.plan_id
        WHERE l.tenant_id = :t ORDER BY l.issued_at DESC
    ', ['t' => $tenant_id]);
}

// =====================================================================
// SYSTEM SETTINGS
// =====================================================================

function setting_get(string $key, $default = null) {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $row = db_one('SELECT value_text FROM system_settings WHERE `key` = :k', ['k' => $key]);
    $cache[$key] = $row ? $row['value_text'] : $default;
    return $cache[$key];
}

function setting_set(string $key, $value, ?int $by = null): void {
    db_run('
        INSERT INTO system_settings (`key`, value_text, updated_by)
        VALUES (:k, :v, :u)
        ON DUPLICATE KEY UPDATE value_text = VALUES(value_text), updated_by = VALUES(updated_by)
    ', ['k' => $key, 'v' => (string)$value, 'u' => $by]);
}

function setting_all(): array {
    return db_select('SELECT `key`, value_text, description, updated_at FROM system_settings ORDER BY `key`');
}

function maintenance_mode_active(): bool {
    return (string)setting_get('maintenance_mode', '0') === '1';
}

// =====================================================================
// IMPERSONATION
// =====================================================================

/**
 * El super admin entra como un usuario tenant. Guarda el estado original
 * en sesión para poder volver. Audita inicio y fin.
 */
function admin_impersonate_start(int $target_user_id, ?string $reason = null): bool {
    $sa = current_user();
    if (!$sa || $sa['role'] !== 'super_admin') return false;

    $target = db_one('SELECT u.*, t.slug AS tenant_slug FROM users u JOIN tenants t ON t.id = u.tenant_id WHERE u.id = :id', ['id' => $target_user_id]);
    if (!$target || !$target['tenant_id']) return false;

    // Persistir registro
    db_run('
        INSERT INTO impersonations (super_admin_id, target_user_id, target_tenant_id, ip, reason)
        VALUES (:sa,:tu,:tt,:ip,:r)
    ', [
        'sa' => (int)$sa['id'],
        'tu' => (int)$target['id'],
        'tt' => (int)$target['tenant_id'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'r'  => $reason,
    ]);
    $impId = (int)db_last_id();

    // Backup del super admin actual en sesión
    $_SESSION['_impersonate_origin'] = [
        'user_id' => $sa['id'],
        'role'    => $sa['role'],
        'name'    => $sa['name'],
        'imp_id'  => $impId,
    ];

    // Cambiar identidad de la sesión
    $_SESSION['user_id']        = (int)$target['id'];
    $_SESSION['user_role']      = $target['role'];
    $_SESSION['user_tenant_id'] = (int)$target['tenant_id'];
    $_SESSION['user']           = $target;

    audit_record('admin.impersonate.start', (int)$sa['id'], [
        'target_user_id' => $target['id'],
        'tenant_id'      => $target['tenant_id'],
    ]);
    return true;
}

function admin_impersonate_stop(): bool {
    if (empty($_SESSION['_impersonate_origin'])) return false;
    $origin = $_SESSION['_impersonate_origin'];
    $impId = $origin['imp_id'] ?? null;

    // Restaurar super admin
    $sa = db_one('SELECT * FROM users WHERE id = :id', ['id' => $origin['user_id']]);
    if (!$sa) { unset($_SESSION['_impersonate_origin']); return false; }

    $_SESSION['user_id']        = (int)$sa['id'];
    $_SESSION['user_role']      = $sa['role'];
    $_SESSION['user_tenant_id'] = $sa['tenant_id'];
    $_SESSION['user']           = $sa;
    unset($_SESSION['_impersonate_origin']);

    if ($impId) db_run('UPDATE impersonations SET ended_at = NOW() WHERE id = :id', ['id' => $impId]);
    audit_record('admin.impersonate.stop', (int)$sa['id']);
    return true;
}

function is_impersonating(): bool {
    return !empty($_SESSION['_impersonate_origin']);
}

// =====================================================================
// ANUNCIOS GLOBALES
// =====================================================================

/** Tenants que están al 80%+ de algún límite (riesgo de exceder plan). */
function admin_tenants_over_limit(int $thresholdPct = 80): array {
    return db_select("
        SELECT t.id, t.slug, t.name, t.status, p.name AS plan_name,
               p.max_users, p.max_cases, p.max_storage_mb,
               (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) users_count,
               (SELECT COUNT(*) FROM cases WHERE tenant_id = t.id) cases_count,
               (SELECT COALESCE(ROUND(SUM(size_bytes)/1048576), 0) FROM documents WHERE tenant_id = t.id) storage_mb,
               GREATEST(
                   (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) / NULLIF(p.max_users, 0),
                   (SELECT COUNT(*) FROM cases WHERE tenant_id = t.id) / NULLIF(p.max_cases, 0),
                   (SELECT COALESCE(SUM(size_bytes)/1048576, 0) FROM documents WHERE tenant_id = t.id) / NULLIF(p.max_storage_mb, 0)
               ) pct
        FROM tenants t
        JOIN plans p ON p.id = t.plan_id
        WHERE t.status IN ('active','trial')
        HAVING pct >= :threshold
        ORDER BY pct DESC LIMIT 10
    ", ['threshold' => $thresholdPct / 100]);
}

// =====================================================================
// GENERACIÓN AUTOMÁTICA DE INVOICES
// =====================================================================

/**
 * Genera invoices mensuales para tenants activos en el periodo en curso.
 * Idempotente: si ya existe una factura para el (tenant, periodo), no la duplica.
 *
 * @return int número de facturas creadas
 */
function admin_generate_monthly_invoices(): int {
    $periodStart = date('Y-m-01');
    $periodEnd   = date('Y-m-t');
    $count = 0;

    $tenants = db_select("
        SELECT t.id, t.plan_id, t.name, p.price_usd, p.billing_cycle
        FROM tenants t
        JOIN plans p ON p.id = t.plan_id
        WHERE t.status = 'active' AND p.price_usd > 0
    ");

    foreach ($tenants as $t) {
        // Skip si ya existe invoice del mismo periodo
        $exists = db_one("
            SELECT id FROM admin_invoices
            WHERE tenant_id = :t AND period_start = :ps AND period_end = :pe
        ", ['t' => $t['id'], 'ps' => $periodStart, 'pe' => $periodEnd]);
        if ($exists) continue;

        $amount = (float)$t['price_usd'];
        if ($t['billing_cycle'] === 'yearly')   $amount = $amount;       // anual: cargo único en el mes
        if ($t['billing_cycle'] === 'lifetime') continue;

        $number = 'INV-' . date('Ym') . '-' . str_pad((string)$t['id'], 5, '0', STR_PAD_LEFT);
        db_run("
            INSERT INTO admin_invoices (tenant_id, plan_id, amount_usd, currency, status,
                period_start, period_end, invoice_number)
            VALUES (:t,:p,:a,'USD','pending',:ps,:pe,:n)
        ", ['t'=>$t['id'],'p'=>$t['plan_id'],'a'=>$amount,'ps'=>$periodStart,'pe'=>$periodEnd,'n'=>$number]);
        $count++;
    }
    return $count;
}

function announcements_active_for(string $tenantStatus = 'active'): array {
    return db_select("
        SELECT * FROM announcements
        WHERE is_active = 1
          AND (audience = 'all' OR audience = :s)
          AND (starts_at IS NULL OR starts_at <= NOW())
          AND (ends_at   IS NULL OR ends_at   >= NOW())
        ORDER BY created_at DESC
    ", ['s' => $tenantStatus]);
}
