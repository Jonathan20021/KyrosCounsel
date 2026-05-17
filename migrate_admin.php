<?php
/**
 * Migration idempotente para el panel super admin renovado.
 *
 * Ejecutar:  php migrate_admin.php
 *
 * Crea/actualiza:
 *  - tenants:        billing_email, billing_phone, notes, current_period_end, monthly_revenue
 *  - plans:          billing_cycle, is_public
 *  - license_keys    (nueva)
 *  - system_settings (nueva)
 *  - announcements   (nueva)
 *  - impersonations  (nueva)
 *  - admin_invoices  (nueva, ledger global de pagos)
 */

declare(strict_types=1);
define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/bootstrap.php';

function safe_run($sql, $label) {
    try {
        db_run($sql);
        echo "  ✓ {$label}\n";
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate') !== false
            || stripos($msg, 'already exists') !== false
            || stripos($msg, 'Duplicate column') !== false) {
            echo "  · {$label} (ya existe)\n";
        } else {
            echo "  ✗ {$label}: {$msg}\n";
        }
    }
}

echo "Migration admin panel · " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";

// ====================================================================
// ALTER tenants
// ====================================================================
echo "\n[1] tenants: nuevas columnas\n";
safe_run("ALTER TABLE tenants ADD COLUMN billing_email VARCHAR(191) NULL AFTER name", "billing_email");
safe_run("ALTER TABLE tenants ADD COLUMN billing_phone VARCHAR(40) NULL AFTER billing_email", "billing_phone");
safe_run("ALTER TABLE tenants ADD COLUMN notes TEXT NULL AFTER settings_json", "notes");
safe_run("ALTER TABLE tenants ADD COLUMN current_period_end DATETIME NULL AFTER trial_ends_at", "current_period_end");
safe_run("ALTER TABLE tenants ADD COLUMN monthly_revenue DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER current_period_end", "monthly_revenue");
safe_run("ALTER TABLE tenants ADD COLUMN suspended_reason VARCHAR(255) NULL AFTER monthly_revenue", "suspended_reason");

// ====================================================================
// ALTER plans
// ====================================================================
echo "\n[2] plans: nuevas columnas\n";
safe_run("ALTER TABLE plans ADD COLUMN billing_cycle ENUM('monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly' AFTER price_usd", "billing_cycle");
safe_run("ALTER TABLE plans ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER is_active", "is_public");
safe_run("ALTER TABLE plans ADD COLUMN description VARCHAR(500) NULL AFTER name", "description");

// ====================================================================
// CREATE license_keys
// ====================================================================
echo "\n[3] license_keys: nueva tabla\n";
safe_run("
CREATE TABLE IF NOT EXISTS license_keys (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NULL,
    license_key  VARCHAR(64) NOT NULL,
    plan_id      INT UNSIGNED NULL,
    status       ENUM('active','revoked','expired') NOT NULL DEFAULT 'active',
    issued_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME NULL,
    revoked_at   DATETIME NULL,
    issued_by    INT UNSIGNED NULL,
    notes        VARCHAR(500) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_license_key (license_key),
    KEY ix_license_tenant (tenant_id),
    KEY ix_license_status (status),
    CONSTRAINT fk_license_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_license_plan   FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "tabla license_keys");

// ====================================================================
// CREATE system_settings
// ====================================================================
echo "\n[4] system_settings: nueva tabla\n";
safe_run("
CREATE TABLE IF NOT EXISTS system_settings (
    `key`        VARCHAR(100) NOT NULL,
    value_text   TEXT NULL,
    value_json   JSON NULL,
    description  VARCHAR(255) NULL,
    updated_by   INT UNSIGNED NULL,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "tabla system_settings");

// Defaults
safe_run("INSERT IGNORE INTO system_settings (`key`, value_text, description) VALUES
    ('maintenance_mode', '0', 'Modo mantenimiento global (1=on, 0=off)'),
    ('maintenance_message', 'Estamos realizando mejoras. Volvemos pronto.', 'Mensaje de mantenimiento'),
    ('signup_open', '1', '¿Permite nuevos registros públicos?'),
    ('default_trial_days', '14', 'Días de trial por defecto al registrarse'),
    ('global_announcement', '', 'Anuncio global mostrado en todos los tenants')",
    "settings default");

// ====================================================================
// CREATE announcements
// ====================================================================
echo "\n[5] announcements: nueva tabla\n";
safe_run("
CREATE TABLE IF NOT EXISTS announcements (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(200) NOT NULL,
    body         TEXT NOT NULL,
    severity     ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    audience     ENUM('all','trial','active','suspended') NOT NULL DEFAULT 'all',
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    starts_at    DATETIME NULL,
    ends_at      DATETIME NULL,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_ann_active (is_active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "tabla announcements");

// ====================================================================
// CREATE impersonations
// ====================================================================
echo "\n[6] impersonations: nueva tabla\n";
safe_run("
CREATE TABLE IF NOT EXISTS impersonations (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    super_admin_id  INT UNSIGNED NOT NULL,
    target_user_id  INT UNSIGNED NOT NULL,
    target_tenant_id INT UNSIGNED NOT NULL,
    started_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ended_at        DATETIME NULL,
    ip              VARCHAR(45) NULL,
    reason          VARCHAR(500) NULL,
    PRIMARY KEY (id),
    KEY ix_imp_admin (super_admin_id),
    KEY ix_imp_target (target_tenant_id, target_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "tabla impersonations");

// ====================================================================
// CREATE admin_invoices (ledger global de pagos)
// ====================================================================
echo "\n[7] admin_invoices: nueva tabla\n";
safe_run("
CREATE TABLE IF NOT EXISTS admin_invoices (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id      INT UNSIGNED NOT NULL,
    plan_id        INT UNSIGNED NULL,
    amount_usd     DECIMAL(10,2) NOT NULL,
    currency       CHAR(3) NOT NULL DEFAULT 'USD',
    status         ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    period_start   DATE NULL,
    period_end     DATE NULL,
    paid_at        DATETIME NULL,
    invoice_number VARCHAR(40) NULL,
    notes          VARCHAR(500) NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_inv_tenant (tenant_id),
    KEY ix_inv_status (status),
    KEY ix_inv_paid_at (paid_at),
    UNIQUE KEY uq_inv_number (invoice_number),
    CONSTRAINT fk_inv_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_plan   FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "tabla admin_invoices");

// ====================================================================
// Backfill: monthly_revenue desde plan asociado
// ====================================================================
echo "\n[8] Backfill: monthly_revenue de tenants activos\n";
safe_run("
UPDATE tenants t
JOIN plans p ON p.id = t.plan_id
SET t.monthly_revenue = p.price_usd
WHERE t.status = 'active' AND t.monthly_revenue = 0
", "monthly_revenue backfill");

echo "\n" . str_repeat('=', 60) . "\n";
echo "Migration completa.\n";
