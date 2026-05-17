<?php
/**
 * Super Admin controller — control total del SaaS.
 *
 * Secciones:
 *   1. Dashboard       — KPIs, MRR/ARR, charts, salud, audit reciente
 *   2. Tenants         — CRUD + acciones (suspend, activate, extend trial,
 *                        impersonate, reset password, eliminar)
 *   3. Plans           — CRUD completo
 *   4. Users           — vista cross-tenant + acciones
 *   5. Licencias       — listar, generar, revocar
 *   6. Audit           — log global filtrable
 *   7. Billing         — invoices ledger, MRR breakdown
 *   8. Announcements   — banners globales
 *   9. System          — settings, mantenimiento, backup
 *  10. Security        — failed logins, sesiones, IPs sospechosas
 *  11. Impersonation   — start / stop
 */

// =====================================================================
// 1. DASHBOARD
// =====================================================================

function admin_ctrl_dashboard($params) {
    $stats = [
        'tenants_total'  => (int)(db_one('SELECT COUNT(*) c FROM tenants')['c'] ?? 0),
        'tenants_active' => (int)(db_one("SELECT COUNT(*) c FROM tenants WHERE status = 'active'")['c'] ?? 0),
        'tenants_trial'  => (int)(db_one("SELECT COUNT(*) c FROM tenants WHERE status = 'trial'")['c'] ?? 0),
        'tenants_susp'   => (int)(db_one("SELECT COUNT(*) c FROM tenants WHERE status IN ('suspended','cancelled')")['c'] ?? 0),
        'users_total'    => (int)(db_one("SELECT COUNT(*) c FROM users WHERE role <> 'super_admin'")['c'] ?? 0),
        'cases_total'    => (int)(db_one('SELECT COUNT(*) c FROM cases')['c'] ?? 0),
        'clients_total'  => (int)(db_one('SELECT COUNT(*) c FROM clients')['c'] ?? 0),
        'docs_total'     => (int)(db_one('SELECT COUNT(*) c FROM documents')['c'] ?? 0),
    ];

    $byCountry = db_select('SELECT country, COUNT(*) c FROM tenants GROUP BY country ORDER BY c DESC LIMIT 10');
    $byPlan = db_select("
        SELECT p.name, p.code, COUNT(t.id) c, p.price_usd
        FROM plans p LEFT JOIN tenants t ON t.plan_id = p.id AND t.status IN ('active','trial')
        GROUP BY p.id, p.name, p.code, p.price_usd ORDER BY p.sort_order ASC
    ");

    $latestTenants = db_select('
        SELECT t.*, p.name AS plan_name FROM tenants t
        LEFT JOIN plans p ON p.id = t.plan_id
        ORDER BY t.created_at DESC LIMIT 8
    ');

    $topTenants = db_select("
        SELECT t.id, t.slug, t.name, t.country,
               (SELECT COUNT(*) FROM cases  WHERE tenant_id = t.id) cases_count,
               (SELECT COUNT(*) FROM users  WHERE tenant_id = t.id) users_count
        FROM tenants t
        WHERE t.status IN ('active','trial')
        ORDER BY cases_count DESC LIMIT 6
    ");

    $recentAudit = db_select('
        SELECT a.event, a.created_at, a.context_json, a.ip,
               t.name AS tenant_name, t.slug AS tenant_slug,
               u.name AS actor_name
        FROM audit_log a
        LEFT JOIN tenants t ON t.id = a.tenant_id
        LEFT JOIN users   u ON u.id = a.actor_user_id
        ORDER BY a.created_at DESC LIMIT 15
    ');

    $unpaidInvoices = (int)(db_one("SELECT COUNT(*) c FROM admin_invoices WHERE status IN ('pending','failed')")['c'] ?? 0);
    $monthlyRevenue = (float)(db_one("
        SELECT COALESCE(SUM(amount_usd),0) s FROM admin_invoices
        WHERE status = 'paid' AND DATE_FORMAT(paid_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
    ")['s'] ?? 0);

    render_with_layout('admin', 'admin.dashboard', [
        'title'           => 'Dashboard SaaS',
        'stats'           => $stats,
        'mrr'             => admin_mrr(),
        'arr'             => admin_arr(),
        'arpu'            => admin_arpu(),
        'churn_rate'      => admin_churn_rate(),
        'conversion_rate' => admin_conversion_rate(),
        'growth'          => admin_growth_30d(),
        'series_signups'  => admin_signups_series(30),
        'series_revenue'  => admin_revenue_series(30),
        'health'          => admin_health(),
        'by_country'      => $byCountry,
        'by_plan'         => $byPlan,
        'latest_tenants'  => $latestTenants,
        'top_tenants'     => $topTenants,
        'recent_audit'    => $recentAudit,
        'unpaid_invoices' => $unpaidInvoices,
        'monthly_revenue' => $monthlyRevenue,
        'over_limit'      => admin_tenants_over_limit(80),
    ]);
}

// =====================================================================
// 2. TENANTS
// =====================================================================

function admin_ctrl_tenants_index($params) {
    $q       = trim($_GET['q'] ?? '');
    $status  = $_GET['status'] ?? '';
    $country = strtoupper($_GET['country'] ?? '');
    $planId  = (int)($_GET['plan_id'] ?? 0);
    $sort    = $_GET['sort'] ?? 'created_desc';

    $where  = ['1=1'];
    $params_sql = [];
    if ($q !== '')       { $where[] = '(t.name LIKE :q OR t.slug LIKE :q OR t.billing_email LIKE :q)'; $params_sql['q'] = "%{$q}%"; }
    if ($status !== '')  { $where[] = 't.status = :s'; $params_sql['s'] = $status; }
    if ($country !== '') { $where[] = 't.country = :c'; $params_sql['c'] = $country; }
    if ($planId > 0)     { $where[] = 't.plan_id = :p'; $params_sql['p'] = $planId; }

    $orderMap = [
        'created_desc' => 't.created_at DESC',
        'created_asc'  => 't.created_at ASC',
        'name'         => 't.name ASC',
        'revenue'      => 't.monthly_revenue DESC',
    ];
    $order = $orderMap[$sort] ?? $orderMap['created_desc'];

    $tenants = db_select("
        SELECT t.*, p.name AS plan_name, p.price_usd,
               (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) users_count,
               (SELECT COUNT(*) FROM cases WHERE tenant_id = t.id) cases_count
        FROM tenants t
        LEFT JOIN plans p ON p.id = t.plan_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY {$order}
    ", $params_sql);

    $plans = db_select('SELECT id, name FROM plans WHERE is_active = 1 ORDER BY sort_order');

    render_with_layout('admin', 'admin.tenants_index', [
        'title'   => 'Tenants',
        'tenants' => $tenants,
        'plans'   => $plans,
        'filters' => compact('q', 'status', 'country', 'planId', 'sort'),
    ]);
}

function admin_ctrl_tenants_new($params) {
    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');
    render_with_layout('admin', 'admin.tenants_new', [
        'title' => 'Nuevo tenant', 'plans' => $plans,
    ]);
}

function admin_ctrl_tenants_create($params) {
    $name    = trim($_POST['name'] ?? '');
    $slug    = mb_strtolower(trim($_POST['slug'] ?? ''));
    $country = strtoupper($_POST['country'] ?? 'US');
    $planId  = (int)($_POST['plan_id'] ?? 0);
    $email   = trim($_POST['billing_email'] ?? '');

    if (mb_strlen($name) < 3 || !is_valid_slug($slug)) {
        flash_set('error', 'Datos inválidos.');
        redirect(url('admin/tenants/new'));
    }
    if (db_one('SELECT id FROM tenants WHERE slug = :s', ['s' => $slug])) {
        flash_set('error', 'Ese slug ya existe.');
        redirect(url('admin/tenants/new'));
    }

    db_run('
        INSERT INTO tenants (slug, name, country, status, plan_id, billing_email)
        VALUES (:s,:n,:c,"active",:p,:e)
    ', ['s'=>$slug, 'n'=>$name, 'c'=>$country, 'p'=>$planId ?: null, 'e'=>$email ?: null]);

    audit_record('tenant.created', $_SESSION['user_id'] ?? null, ['slug'=>$slug, 'by'=>'super_admin']);
    flash_set('success', 'Tenant creado.');
    redirect(url('admin/tenants'));
}

function admin_ctrl_tenants_show($params) {
    $id = (int)$params['id'];
    $t = db_one('
        SELECT t.*, p.name AS plan_name, p.code AS plan_code, p.max_users, p.max_cases,
               p.max_storage_mb, p.price_usd, p.billing_cycle
        FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
        WHERE t.id = :id
    ', ['id' => $id]);
    if (!$t) abort(404);

    $stats = [
        'users'         => (int)(db_one('SELECT COUNT(*) c FROM users      WHERE tenant_id = :id', ['id'=>$id])['c'] ?? 0),
        'cases'         => (int)(db_one('SELECT COUNT(*) c FROM cases      WHERE tenant_id = :id', ['id'=>$id])['c'] ?? 0),
        'clients'       => (int)(db_one('SELECT COUNT(*) c FROM clients    WHERE tenant_id = :id', ['id'=>$id])['c'] ?? 0),
        'docs'          => (int)(db_one('SELECT COUNT(*) c FROM documents  WHERE tenant_id = :id', ['id'=>$id])['c'] ?? 0),
        'storage_mb'    => (int)round(((int)(db_one('SELECT COALESCE(SUM(size_bytes),0) s FROM documents WHERE tenant_id = :id', ['id'=>$id])['s'] ?? 0)) / 1024 / 1024),
        'tasks_pending' => (int)(db_one("SELECT COUNT(*) c FROM tasks WHERE tenant_id = :id AND status = 'pending'", ['id'=>$id])['c'] ?? 0),
    ];

    $users = db_select('
        SELECT id, email, name, role, status, last_login_at, two_factor_enabled, created_at
        FROM users WHERE tenant_id = :id ORDER BY created_at DESC LIMIT 50
    ', ['id'=>$id]);

    $audit = db_select('
        SELECT a.event, a.created_at, a.ip, a.context_json, u.name AS actor_name
        FROM audit_log a LEFT JOIN users u ON u.id = a.actor_user_id
        WHERE a.tenant_id = :id ORDER BY a.created_at DESC LIMIT 30
    ', ['id'=>$id]);

    $invoices = db_select('
        SELECT * FROM admin_invoices WHERE tenant_id = :id ORDER BY created_at DESC LIMIT 20
    ', ['id'=>$id]);

    $licenses = license_list_for_tenant($id);

    $byStatus = db_select('
        SELECT status, COUNT(*) c FROM cases WHERE tenant_id = :id GROUP BY status ORDER BY c DESC
    ', ['id'=>$id]);

    $rawByDay = db_select('
        SELECT DATE(created_at) d, COUNT(*) c FROM clients
        WHERE tenant_id = :id AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at) ORDER BY d ASC
    ', ['id'=>$id]);
    $map = []; foreach ($rawByDay as $r) $map[$r['d']] = (int)$r['c'];
    $byDay = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $byDay[] = ['d'=>$d, 'c'=>$map[$d] ?? 0];
    }

    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');

    render_with_layout('admin', 'admin.tenants_show', [
        'title'    => $t['name'],
        'tenant'   => $t,
        'stats'    => $stats,
        'plans'    => $plans,
        'users'    => $users,
        'audit'    => $audit,
        'invoices' => $invoices,
        'licenses' => $licenses,
        'by_status'=> $byStatus,
        'by_day'   => $byDay,
    ]);
}

function admin_ctrl_tenants_update($params) {
    $id = (int)$params['id'];
    if (!db_one('SELECT id FROM tenants WHERE id = :id', ['id'=>$id])) abort(404);
    db_run('
        UPDATE tenants SET name = :n, country = :c, plan_id = :p,
               billing_email = :e, billing_phone = :ph, notes = :nt
        WHERE id = :id
    ', [
        'n'  => trim($_POST['name'] ?? ''),
        'c'  => strtoupper($_POST['country'] ?? 'US'),
        'p'  => (int)($_POST['plan_id'] ?? 0) ?: null,
        'e'  => trim($_POST['billing_email'] ?? '') ?: null,
        'ph' => trim($_POST['billing_phone'] ?? '') ?: null,
        'nt' => trim($_POST['notes'] ?? '') ?: null,
        'id' => $id,
    ]);
    audit_record('tenant.updated', $_SESSION['user_id'] ?? null, ['tenant_id'=>$id]);
    flash_set('success', 'Tenant actualizado.');
    redirect(url('admin/tenants/' . $id));
}

function admin_ctrl_tenants_status($params) {
    $id = (int)$params['id'];
    $status = $_POST['status'] ?? '';
    $reason = trim($_POST['reason'] ?? '') ?: null;
    if (!in_array($status, ['active','suspended','cancelled','pending','trial'], true)) {
        flash_set('error', 'Estado inválido.');
        redirect(url('admin/tenants/' . $id));
    }
    db_run('UPDATE tenants SET status = :s, suspended_reason = :r WHERE id = :id', ['s'=>$status, 'r'=>$reason, 'id'=>$id]);
    audit_record('tenant.status.changed', $_SESSION['user_id'] ?? null, ['tenant_id'=>$id, 'status'=>$status, 'reason'=>$reason]);
    flash_set('success', "Estado cambiado a {$status}.");
    redirect(url('admin/tenants/' . $id));
}

function admin_ctrl_tenants_extend_trial($params) {
    $id = (int)$params['id'];
    $days = max(1, min(365, (int)($_POST['days'] ?? 14)));
    $row = db_one('SELECT trial_ends_at FROM tenants WHERE id = :id', ['id'=>$id]);
    if (!$row) abort(404);
    $base = $row['trial_ends_at'] ? strtotime($row['trial_ends_at']) : time();
    if ($base < time()) $base = time();
    $newEnd = date('Y-m-d H:i:s', $base + $days * 86400);
    db_run("UPDATE tenants SET trial_ends_at = :e, status = 'trial' WHERE id = :id", ['e'=>$newEnd, 'id'=>$id]);
    audit_record('tenant.trial.extended', $_SESSION['user_id'] ?? null, ['tenant_id'=>$id, 'days'=>$days, 'new_end'=>$newEnd]);
    flash_set('success', "Trial extendido {$days} días (hasta {$newEnd}).");
    redirect(url('admin/tenants/' . $id));
}

function admin_ctrl_tenants_change_plan($params) {
    $id = (int)$params['id'];
    $planId = (int)($_POST['plan_id'] ?? 0);
    if (!$planId) { flash_set('error','Plan inválido.'); redirect(url('admin/tenants/'.$id)); }
    $plan = db_one('SELECT id, price_usd FROM plans WHERE id = :id', ['id'=>$planId]);
    if (!$plan) abort(404);
    db_run('UPDATE tenants SET plan_id = :p, monthly_revenue = :m WHERE id = :id', ['p'=>$planId, 'm'=>$plan['price_usd'], 'id'=>$id]);
    audit_record('tenant.plan.changed', $_SESSION['user_id'] ?? null, ['tenant_id'=>$id, 'plan_id'=>$planId]);
    flash_set('success', 'Plan actualizado.');
    redirect(url('admin/tenants/' . $id));
}

function admin_ctrl_tenants_reset_password($params) {
    $tenantId = (int)$params['id'];
    $userId = (int)($_POST['user_id'] ?? 0);
    $user = db_one('SELECT id, email, tenant_id FROM users WHERE id = :id', ['id'=>$userId]);
    if (!$user || (int)$user['tenant_id'] !== $tenantId) abort(404);
    // Generar password temporal
    $temp = bin2hex(random_bytes(6));
    db_run('UPDATE users SET password_hash = :h WHERE id = :id', [
        'h' => password_hash_make($temp),
        'id' => $userId,
    ]);
    audit_record('user.password.reset', $_SESSION['user_id'] ?? null, ['user_id'=>$userId, 'tenant_id'=>$tenantId]);
    flash_set('success', "Contraseña temporal: {$temp} (cópiala — no se mostrará de nuevo).");
    redirect(url('admin/tenants/' . $tenantId));
}

function admin_ctrl_tenants_bulk($params) {
    $action = $_POST['bulk_action'] ?? '';
    $ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
    if (empty($ids)) {
        flash_set('error', 'Selecciona al menos un tenant.');
        redirect(url('admin/tenants'));
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $count = count($ids);

    switch ($action) {
        case 'suspend':
        case 'activate':
        case 'cancel':
            $statusMap = ['suspend'=>'suspended','activate'=>'active','cancel'=>'cancelled'];
            $status = $statusMap[$action];
            db_run("UPDATE tenants SET status = ? WHERE id IN ($placeholders)", array_merge([$status], $ids));
            audit_record('tenant.bulk.status', $_SESSION['user_id'] ?? null, ['ids'=>$ids, 'status'=>$status]);
            flash_set('success', "$count tenants → {$status}.");
            break;
        case 'change_plan':
            $planId = (int)($_POST['plan_id'] ?? 0);
            if (!$planId) { flash_set('error', 'Selecciona un plan.'); break; }
            $plan = db_one('SELECT price_usd FROM plans WHERE id = :id', ['id'=>$planId]);
            if (!$plan) { flash_set('error', 'Plan inválido.'); break; }
            db_run("UPDATE tenants SET plan_id = ?, monthly_revenue = ? WHERE id IN ($placeholders)", array_merge([$planId, $plan['price_usd']], $ids));
            audit_record('tenant.bulk.plan', $_SESSION['user_id'] ?? null, ['ids'=>$ids, 'plan_id'=>$planId]);
            flash_set('success', "$count tenants cambiados al plan #{$planId}.");
            break;
        case 'extend_trial':
            $days = max(1, min(365, (int)($_POST['days'] ?? 14)));
            foreach ($ids as $id) {
                $row = db_one('SELECT trial_ends_at FROM tenants WHERE id = :id', ['id'=>$id]);
                if (!$row) continue;
                $base = $row['trial_ends_at'] ? strtotime($row['trial_ends_at']) : time();
                if ($base < time()) $base = time();
                $newEnd = date('Y-m-d H:i:s', $base + $days * 86400);
                db_run("UPDATE tenants SET trial_ends_at = :e, status = 'trial' WHERE id = :id", ['e'=>$newEnd, 'id'=>$id]);
            }
            audit_record('tenant.bulk.extend_trial', $_SESSION['user_id'] ?? null, ['ids'=>$ids, 'days'=>$days]);
            flash_set('success', "$count trials extendidos {$days} días.");
            break;
        default:
            flash_set('error', 'Acción no reconocida.');
    }
    redirect(url('admin/tenants'));
}

function admin_ctrl_tenants_delete($params) {
    $id = (int)$params['id'];
    $confirm = $_POST['confirm_slug'] ?? '';
    $t = db_one('SELECT slug FROM tenants WHERE id = :id', ['id'=>$id]);
    if (!$t) abort(404);
    if ($confirm !== $t['slug']) {
        flash_set('error', 'Confirmación incorrecta. Escribe el slug exacto.');
        redirect(url('admin/tenants/' . $id));
    }
    db_run('DELETE FROM tenants WHERE id = :id', ['id'=>$id]);
    audit_record('tenant.deleted', $_SESSION['user_id'] ?? null, ['slug'=>$t['slug']]);
    flash_set('success', 'Tenant eliminado.');
    redirect(url('admin/tenants'));
}

// =====================================================================
// 3. PLANS
// =====================================================================

function admin_ctrl_plans_index($params) {
    $plans = db_select('
        SELECT p.*,
               (SELECT COUNT(*) FROM tenants t WHERE t.plan_id = p.id) tenants_count
        FROM plans p ORDER BY sort_order ASC
    ');
    render_with_layout('admin', 'admin.plans_index', [
        'title' => 'Planes y precios', 'plans' => $plans,
    ]);
}

function admin_ctrl_plans_new($params) {
    render_with_layout('admin', 'admin.plans_form', [
        'title' => 'Nuevo plan', 'plan' => null,
    ]);
}

function admin_ctrl_plans_edit($params) {
    $plan = db_one('SELECT * FROM plans WHERE id = :id', ['id'=>(int)$params['id']]);
    if (!$plan) abort(404);
    render_with_layout('admin', 'admin.plans_form', [
        'title' => 'Editar plan', 'plan' => $plan,
    ]);
}

function admin_ctrl_plans_save($params) {
    $id = isset($params['id']) ? (int)$params['id'] : 0;
    $code = mb_strtolower(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '') ?: null;
    $price = (float)($_POST['price_usd'] ?? 0);
    $cycle = $_POST['billing_cycle'] ?? 'monthly';
    $maxUsers = (int)($_POST['max_users'] ?? 5);
    $maxCases = (int)($_POST['max_cases'] ?? 100);
    $maxMb    = (int)($_POST['max_storage_mb'] ?? 1024);
    $isActive = !empty($_POST['is_active']) ? 1 : 0;
    $isPublic = !empty($_POST['is_public']) ? 1 : 0;
    $sort = (int)($_POST['sort_order'] ?? 0);

    $features = [
        'emails'           => (int)($_POST['feat_emails'] ?? 0),
        'workflows'        => !empty($_POST['feat_workflows']),
        'two_factor'       => !empty($_POST['feat_two_factor']),
        'priority_support' => !empty($_POST['feat_priority_support']),
        'api_access'       => !empty($_POST['feat_api_access']),
        'white_label'      => !empty($_POST['feat_white_label']),
    ];

    if ($code === '' || $name === '') {
        flash_set('error', 'Código y nombre son obligatorios.');
        redirect(url($id ? "admin/plans/{$id}/edit" : 'admin/plans/new'));
    }

    if ($id) {
        db_run('
            UPDATE plans SET code=:c, name=:n, description=:d, price_usd=:p, billing_cycle=:bc,
                   max_users=:mu, max_cases=:mc, max_storage_mb=:ms, features_json=:fj,
                   is_active=:ia, is_public=:ip, sort_order=:so
            WHERE id = :id
        ', [
            'c'=>$code,'n'=>$name,'d'=>$desc,'p'=>$price,'bc'=>$cycle,
            'mu'=>$maxUsers,'mc'=>$maxCases,'ms'=>$maxMb,'fj'=>json_encode($features),
            'ia'=>$isActive,'ip'=>$isPublic,'so'=>$sort,'id'=>$id,
        ]);
        audit_record('plan.updated', $_SESSION['user_id'] ?? null, ['plan_id'=>$id]);
        flash_set('success', 'Plan actualizado.');
    } else {
        db_run('
            INSERT INTO plans (code, name, description, price_usd, billing_cycle,
                   max_users, max_cases, max_storage_mb, features_json, is_active, is_public, sort_order)
            VALUES (:c,:n,:d,:p,:bc,:mu,:mc,:ms,:fj,:ia,:ip,:so)
        ', [
            'c'=>$code,'n'=>$name,'d'=>$desc,'p'=>$price,'bc'=>$cycle,
            'mu'=>$maxUsers,'mc'=>$maxCases,'ms'=>$maxMb,'fj'=>json_encode($features),
            'ia'=>$isActive,'ip'=>$isPublic,'so'=>$sort,
        ]);
        audit_record('plan.created', $_SESSION['user_id'] ?? null, ['code'=>$code]);
        flash_set('success', 'Plan creado.');
    }
    redirect(url('admin/plans'));
}

function admin_ctrl_plans_toggle($params) {
    $id = (int)$params['id'];
    db_run('UPDATE plans SET is_active = 1 - is_active WHERE id = :id', ['id'=>$id]);
    flash_set('success', 'Estado del plan actualizado.');
    redirect(url('admin/plans'));
}

// =====================================================================
// 4. USERS (cross-tenant)
// =====================================================================

function admin_ctrl_users_index($params) {
    $q     = trim($_GET['q'] ?? '');
    $role  = $_GET['role'] ?? '';
    $where = ['1=1'];
    $sqlp  = [];
    if ($q !== '') { $where[] = '(u.name LIKE :q OR u.email LIKE :q)'; $sqlp['q'] = "%{$q}%"; }
    if ($role !== '') { $where[] = 'u.role = :r'; $sqlp['r'] = $role; }

    $users = db_select("
        SELECT u.*, t.name AS tenant_name, t.slug AS tenant_slug, t.status AS tenant_status
        FROM users u LEFT JOIN tenants t ON t.id = u.tenant_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY u.created_at DESC LIMIT 200
    ", $sqlp);

    render_with_layout('admin', 'admin.users_index', [
        'title' => 'Usuarios',
        'users' => $users,
        'filters' => compact('q','role'),
    ]);
}

function admin_ctrl_users_disable($params) {
    $id = (int)$params['id'];
    db_run("UPDATE users SET status = 'disabled' WHERE id = :id AND role <> 'super_admin'", ['id'=>$id]);
    audit_record('user.disabled', $_SESSION['user_id'] ?? null, ['user_id'=>$id]);
    flash_set('success', 'Usuario deshabilitado.');
    redirect(url('admin/users'));
}

function admin_ctrl_users_enable($params) {
    $id = (int)$params['id'];
    db_run("UPDATE users SET status = 'active' WHERE id = :id", ['id'=>$id]);
    flash_set('success', 'Usuario habilitado.');
    redirect(url('admin/users'));
}

// =====================================================================
// 5. LICENCIAS
// =====================================================================

function admin_ctrl_licenses_index($params) {
    $licenses = db_select('
        SELECT l.*, t.name AS tenant_name, t.slug AS tenant_slug, p.name AS plan_name
        FROM license_keys l
        LEFT JOIN tenants t ON t.id = l.tenant_id
        LEFT JOIN plans   p ON p.id = l.plan_id
        ORDER BY l.issued_at DESC LIMIT 200
    ');
    $tenants = db_select('SELECT id, name, slug FROM tenants ORDER BY name');
    $plans   = db_select('SELECT id, name FROM plans WHERE is_active = 1 ORDER BY sort_order');
    render_with_layout('admin', 'admin.licenses_index', [
        'title' => 'Licencias',
        'licenses' => $licenses,
        'tenants' => $tenants,
        'plans'   => $plans,
    ]);
}

function admin_ctrl_licenses_create($params) {
    $tenantId = (int)($_POST['tenant_id'] ?? 0);
    $planId   = (int)($_POST['plan_id']   ?? 0) ?: null;
    $days     = (int)($_POST['valid_days'] ?? 0) ?: null;
    $notes    = trim($_POST['notes'] ?? '') ?: null;
    if (!$tenantId) { flash_set('error', 'Selecciona un tenant.'); redirect(url('admin/licenses')); }
    $r = license_create($tenantId, $planId, $days, $notes, $_SESSION['user_id'] ?? null);
    audit_record('license.created', $_SESSION['user_id'] ?? null, ['tenant_id'=>$tenantId, 'key'=>$r['key']]);
    flash_set('success', 'Licencia generada: ' . $r['key']);
    redirect(url('admin/licenses'));
}

function admin_ctrl_licenses_revoke($params) {
    $id = (int)$params['id'];
    license_revoke($id);
    audit_record('license.revoked', $_SESSION['user_id'] ?? null, ['license_id'=>$id]);
    flash_set('success', 'Licencia revocada.');
    redirect(url('admin/licenses'));
}

// =====================================================================
// 6. AUDIT GLOBAL
// =====================================================================

function admin_ctrl_audit_index($params) {
    $event = trim($_GET['event'] ?? '');
    $tenantId = (int)($_GET['tenant_id'] ?? 0);
    $where = ['1=1'];
    $sqlp = [];
    if ($event)   { $where[] = 'a.event LIKE :ev'; $sqlp['ev'] = "%{$event}%"; }
    if ($tenantId){ $where[] = 'a.tenant_id = :t'; $sqlp['t'] = $tenantId; }

    $events = db_select("
        SELECT a.*, t.name AS tenant_name, t.slug AS tenant_slug, u.name AS actor_name, u.email AS actor_email
        FROM audit_log a
        LEFT JOIN tenants t ON t.id = a.tenant_id
        LEFT JOIN users   u ON u.id = a.actor_user_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.created_at DESC LIMIT 300
    ", $sqlp);
    $tenants = db_select('SELECT id, name FROM tenants ORDER BY name');

    render_with_layout('admin', 'admin.audit_index', [
        'title'   => 'Auditoría global',
        'events'  => $events,
        'tenants' => $tenants,
        'filters' => compact('event','tenantId'),
    ]);
}

// =====================================================================
// 7. BILLING
// =====================================================================

function admin_ctrl_billing_index($params) {
    $invoices = db_select('
        SELECT i.*, t.name AS tenant_name, t.slug AS tenant_slug, p.name AS plan_name
        FROM admin_invoices i
        LEFT JOIN tenants t ON t.id = i.tenant_id
        LEFT JOIN plans   p ON p.id = i.plan_id
        ORDER BY i.created_at DESC LIMIT 200
    ');
    $totals = db_one("
        SELECT
            COALESCE(SUM(CASE WHEN status='paid'    THEN amount_usd ELSE 0 END), 0) paid,
            COALESCE(SUM(CASE WHEN status='pending' THEN amount_usd ELSE 0 END), 0) pending,
            COALESCE(SUM(CASE WHEN status='failed'  THEN amount_usd ELSE 0 END), 0) failed,
            COALESCE(SUM(CASE WHEN status='refunded'THEN amount_usd ELSE 0 END), 0) refunded
        FROM admin_invoices
    ");
    render_with_layout('admin', 'admin.billing_index', [
        'title'    => 'Facturación',
        'invoices' => $invoices,
        'totals'   => $totals,
        'mrr'      => admin_mrr(),
        'arr'      => admin_arr(),
        'arpu'     => admin_arpu(),
        'series'   => admin_revenue_series(30),
    ]);
}

function admin_ctrl_billing_invoice_mark($params) {
    $id = (int)$params['id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['paid','pending','failed','refunded'], true)) {
        flash_set('error', 'Estado inválido.'); redirect(url('admin/billing'));
    }
    $extra = ($status === 'paid') ? ', paid_at = NOW()' : '';
    db_run("UPDATE admin_invoices SET status = :s {$extra} WHERE id = :id", ['s'=>$status, 'id'=>$id]);
    flash_set('success', 'Factura actualizada.');
    redirect(url('admin/billing'));
}

// =====================================================================
// 8. ANNOUNCEMENTS
// =====================================================================

function admin_ctrl_announcements_index($params) {
    $announcements = db_select('SELECT * FROM announcements ORDER BY created_at DESC');
    render_with_layout('admin', 'admin.announcements_index', [
        'title' => 'Anuncios globales',
        'announcements' => $announcements,
    ]);
}

function admin_ctrl_announcements_save($params) {
    $id = isset($params['id']) ? (int)$params['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $sev = $_POST['severity'] ?? 'info';
    $aud = $_POST['audience'] ?? 'all';
    $active = !empty($_POST['is_active']) ? 1 : 0;
    $startsAt = $_POST['starts_at'] ?? null;
    $endsAt   = $_POST['ends_at']   ?? null;

    if ($title === '' || $body === '') {
        flash_set('error', 'Título y cuerpo son obligatorios.');
        redirect(url('admin/announcements'));
    }
    if ($id) {
        db_run('UPDATE announcements SET title=:t, body=:b, severity=:s, audience=:a, is_active=:ac, starts_at=:sa, ends_at=:ea WHERE id=:id', [
            't'=>$title,'b'=>$body,'s'=>$sev,'a'=>$aud,'ac'=>$active,'sa'=>$startsAt ?: null,'ea'=>$endsAt ?: null,'id'=>$id,
        ]);
    } else {
        db_run('INSERT INTO announcements (title, body, severity, audience, is_active, starts_at, ends_at, created_by) VALUES (:t,:b,:s,:a,:ac,:sa,:ea,:cb)', [
            't'=>$title,'b'=>$body,'s'=>$sev,'a'=>$aud,'ac'=>$active,'sa'=>$startsAt ?: null,'ea'=>$endsAt ?: null,'cb'=>$_SESSION['user_id'] ?? null,
        ]);
    }
    flash_set('success', 'Anuncio guardado.');
    redirect(url('admin/announcements'));
}

function admin_ctrl_announcements_toggle($params) {
    $id = (int)$params['id'];
    db_run('UPDATE announcements SET is_active = 1 - is_active WHERE id = :id', ['id'=>$id]);
    flash_set('success', 'Anuncio actualizado.');
    redirect(url('admin/announcements'));
}

function admin_ctrl_announcements_delete($params) {
    $id = (int)$params['id'];
    db_run('DELETE FROM announcements WHERE id = :id', ['id'=>$id]);
    flash_set('success', 'Anuncio eliminado.');
    redirect(url('admin/announcements'));
}

// =====================================================================
// 9. SYSTEM
// =====================================================================

function admin_ctrl_system_index($params) {
    $settings = setting_all();
    render_with_layout('admin', 'admin.system_index', [
        'title' => 'Sistema',
        'settings' => $settings,
        'maintenance' => maintenance_mode_active(),
        'php_version' => PHP_VERSION,
        'mysql_version' => db_one('SELECT VERSION() v')['v'] ?? 'unknown',
        'storage_used_mb' => (int)round(((int)(db_one('SELECT COALESCE(SUM(size_bytes),0) s FROM documents')['s'] ?? 0)) / 1024 / 1024),
    ]);
}

function admin_ctrl_system_settings_save($params) {
    $by = $_SESSION['user_id'] ?? null;
    foreach (($_POST['settings'] ?? []) as $k => $v) {
        setting_set($k, $v, $by);
    }
    audit_record('system.settings.updated', $by, ['keys' => array_keys($_POST['settings'] ?? [])]);
    flash_set('success', 'Configuración guardada.');
    redirect(url('admin/system'));
}

function admin_ctrl_system_maintenance_toggle($params) {
    $current = (string)setting_get('maintenance_mode', '0');
    setting_set('maintenance_mode', $current === '1' ? '0' : '1', $_SESSION['user_id'] ?? null);
    flash_set('success', 'Modo mantenimiento ' . ($current === '1' ? 'desactivado' : 'activado') . '.');
    redirect(url('admin/system'));
}

function admin_ctrl_system_cache_clear($params) {
    // En este stack no hay APCu/opcache obligatorio; limpiamos rate_limits viejos como housekeeping
    db_run('DELETE FROM rate_limits WHERE created_at < UNIX_TIMESTAMP() - 86400');
    if (function_exists('opcache_reset')) opcache_reset();
    audit_record('system.cache.cleared', $_SESSION['user_id'] ?? null);
    flash_set('success', 'Caché limpiada.');
    redirect(url('admin/system'));
}

// =====================================================================
// 10. SECURITY
// =====================================================================

function admin_ctrl_security_index($params) {
    $failedLogins = db_select("
        SELECT a.ip, COUNT(*) c, MAX(a.created_at) last_at
        FROM audit_log a
        WHERE a.event = 'auth.login.failed'
          AND a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY a.ip ORDER BY c DESC LIMIT 30
    ");
    $permissionDenied = db_select("
        SELECT a.created_at, a.ip, a.context_json, u.name AS actor_name, t.name AS tenant_name
        FROM audit_log a
        LEFT JOIN users   u ON u.id = a.actor_user_id
        LEFT JOIN tenants t ON t.id = a.tenant_id
        WHERE a.event = 'auth.permission.denied'
          AND a.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY a.created_at DESC LIMIT 30
    ");
    $impersonations = db_select('
        SELECT i.*, sa.name AS sa_name, u.name AS target_name, t.name AS tenant_name, t.slug AS tenant_slug
        FROM impersonations i
        LEFT JOIN users   sa ON sa.id = i.super_admin_id
        LEFT JOIN users   u  ON u.id  = i.target_user_id
        LEFT JOIN tenants t  ON t.id  = i.target_tenant_id
        ORDER BY i.started_at DESC LIMIT 50
    ');
    $usersWith2FA = (int)(db_one("SELECT COUNT(*) c FROM users WHERE two_factor_enabled = 1 AND role <> 'super_admin'")['c'] ?? 0);
    $usersWithout2FA = (int)(db_one("SELECT COUNT(*) c FROM users WHERE two_factor_enabled = 0 AND role <> 'super_admin' AND status = 'active'")['c'] ?? 0);

    render_with_layout('admin', 'admin.security_index', [
        'title' => 'Seguridad',
        'failed_logins' => $failedLogins,
        'permission_denied' => $permissionDenied,
        'impersonations' => $impersonations,
        'users_with_2fa' => $usersWith2FA,
        'users_without_2fa' => $usersWithout2FA,
    ]);
}

// =====================================================================
// 11. IMPERSONATION
// =====================================================================

function admin_ctrl_impersonate_start($params) {
    $tenantId = (int)$params['id'];
    $reason = trim($_POST['reason'] ?? '') ?: null;
    // Tomamos el primer tenant_admin del tenant
    $u = db_one("
        SELECT id FROM users WHERE tenant_id = :t AND role = 'tenant_admin' AND status = 'active'
        ORDER BY created_at ASC LIMIT 1
    ", ['t'=>$tenantId]);
    if (!$u) { flash_set('error', 'Sin tenant_admin activo en ese tenant.'); redirect(url('admin/tenants/'.$tenantId)); }

    if (admin_impersonate_start((int)$u['id'], $reason)) {
        // Redirigir al dashboard del tenant
        $t = db_one('SELECT slug FROM tenants WHERE id = :id', ['id'=>$tenantId]);
        redirect(url('t/' . $t['slug'] . '/dashboard'));
    }
    flash_set('error', 'No se pudo iniciar impersonation.');
    redirect(url('admin/tenants/' . $tenantId));
}

function admin_ctrl_impersonate_stop($params) {
    if (admin_impersonate_stop()) {
        flash_set('success', 'Saliste del modo impersonation.');
        redirect(url('admin'));
    }
    redirect(url('admin'));
}

// =====================================================================
// 12. EXPORTES CSV
// =====================================================================

function _admin_send_csv(string $filename, array $headers, iterable $rows): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 (Excel friendly)
    fputcsv($out, $headers);
    foreach ($rows as $row) fputcsv($out, $row);
    fclose($out);
    exit;
}

function admin_ctrl_export_tenants($params) {
    $rows = db_select('
        SELECT t.id, t.slug, t.name, t.country, t.status, p.name AS plan_name,
               t.monthly_revenue, t.billing_email, t.billing_phone, t.trial_ends_at,
               t.created_at, t.updated_at,
               (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) users_count,
               (SELECT COUNT(*) FROM cases WHERE tenant_id = t.id) cases_count
        FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
        ORDER BY t.created_at DESC
    ');
    $data = array_map(fn($r) => [
        $r['id'], $r['slug'], $r['name'], $r['country'], $r['status'], $r['plan_name'],
        $r['monthly_revenue'], $r['billing_email'], $r['billing_phone'],
        $r['trial_ends_at'], $r['created_at'], $r['updated_at'],
        $r['users_count'], $r['cases_count'],
    ], $rows);
    _admin_send_csv('tenants_' . date('Ymd') . '.csv',
        ['id','slug','name','country','status','plan','mrr','billing_email','billing_phone','trial_ends_at','created_at','updated_at','users','cases'],
        $data);
}

function admin_ctrl_export_audit($params) {
    $rows = db_select('
        SELECT a.id, a.created_at, a.event, a.ip, t.name AS tenant_name, u.email AS actor_email
        FROM audit_log a
        LEFT JOIN tenants t ON t.id = a.tenant_id
        LEFT JOIN users   u ON u.id = a.actor_user_id
        ORDER BY a.created_at DESC LIMIT 10000
    ');
    $data = array_map(fn($r) => [$r['id'], $r['created_at'], $r['event'], $r['actor_email'], $r['tenant_name'], $r['ip']], $rows);
    _admin_send_csv('audit_' . date('Ymd') . '.csv',
        ['id','created_at','event','actor_email','tenant','ip'],
        $data);
}

function admin_ctrl_export_invoices($params) {
    $rows = db_select('
        SELECT i.id, i.invoice_number, t.name AS tenant_name, p.name AS plan_name,
               i.amount_usd, i.currency, i.status, i.period_start, i.period_end, i.paid_at, i.created_at
        FROM admin_invoices i
        LEFT JOIN tenants t ON t.id = i.tenant_id
        LEFT JOIN plans   p ON p.id = i.plan_id
        ORDER BY i.created_at DESC
    ');
    $data = array_map(fn($r) => [
        $r['id'], $r['invoice_number'], $r['tenant_name'], $r['plan_name'],
        $r['amount_usd'], $r['currency'], $r['status'], $r['period_start'], $r['period_end'],
        $r['paid_at'], $r['created_at'],
    ], $rows);
    _admin_send_csv('invoices_' . date('Ymd') . '.csv',
        ['id','invoice_number','tenant','plan','amount','currency','status','period_start','period_end','paid_at','created_at'],
        $data);
}

// =====================================================================
// 13. HEALTH CHECK (JSON)
// =====================================================================

function admin_ctrl_health($params) {
    $start = microtime(true);
    $dbOk = true;
    try { db_one('SELECT 1'); } catch (Throwable $e) { $dbOk = false; }
    $elapsedMs = round((microtime(true) - $start) * 1000, 2);

    $payload = [
        'status'   => $dbOk ? 'ok' : 'degraded',
        'time'     => date('c'),
        'maintenance' => maintenance_mode_active(),
        'version'  => '1.0',
        'db' => [
            'connected'  => $dbOk,
            'latency_ms' => $elapsedMs,
        ],
        'metrics' => [
            'tenants_active' => (int)(db_one("SELECT COUNT(*) c FROM tenants WHERE status='active'")['c'] ?? 0),
            'tenants_trial'  => (int)(db_one("SELECT COUNT(*) c FROM tenants WHERE status='trial'")['c'] ?? 0),
            'mrr'            => admin_mrr(),
            'failed_logins_24h' => (int)(db_one("SELECT COUNT(*) c FROM audit_log WHERE event='auth.login.failed' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")['c'] ?? 0),
        ],
        'php' => PHP_VERSION,
    ];
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    http_response_code($dbOk ? 200 : 503);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// =====================================================================
// 14. GENERAR INVOICES MENSUALES (manual desde admin)
// =====================================================================

function admin_ctrl_billing_generate_invoices($params) {
    $generated = admin_generate_monthly_invoices();
    audit_record('billing.invoices.generated', $_SESSION['user_id'] ?? null, ['count' => $generated]);
    flash_set('success', "{$generated} facturas generadas para el periodo en curso.");
    redirect(url('admin/billing'));
}
