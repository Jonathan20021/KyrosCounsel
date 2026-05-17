USE kyroscounsel;

-- Plan de pagos (cuotas)
DROP TABLE IF EXISTS payment_plans;
CREATE TABLE payment_plans (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NOT NULL,
    name         VARCHAR(150) NOT NULL,
    total_usd    DECIMAL(10,2) NOT NULL,
    installments_count INT UNSIGNED NOT NULL DEFAULT 1,
    status       ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
    notes        TEXT NULL,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_pp_tenant_case (tenant_id, case_id),
    CONSTRAINT fk_pp_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cuotas individuales (cada installment se vincula a un case_payment)
ALTER TABLE case_payments
    ADD COLUMN IF NOT EXISTS payment_plan_id INT UNSIGNED NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS installment_no INT UNSIGNED NULL AFTER payment_plan_id,
    ADD COLUMN IF NOT EXISTS due_date DATE NULL AFTER installment_no;

-- Reset de contrasena del cliente (link por email)
DROP TABLE IF EXISTS client_password_resets;
CREATE TABLE client_password_resets (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    client_id    INT UNSIGNED NOT NULL,
    token_hash   CHAR(64) NOT NULL,
    expires_at   DATETIME NOT NULL,
    used_at      DATETIME NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cpr_hash (token_hash),
    KEY ix_cpr_client (client_id),
    CONSTRAINT fk_cpr_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
