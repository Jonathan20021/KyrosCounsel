-- KyrosCounsel - Schema upgrade v3
-- Time tracking + hourly rates en users

USE kyroscounsel;

-- Hourly rate del usuario (para billing)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS hourly_rate_usd DECIMAL(8,2) NULL DEFAULT 150.00 AFTER status;

-- Tabla de time entries (horas billable por caso)
DROP TABLE IF EXISTS case_time_entries;
CREATE TABLE case_time_entries (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id       INT UNSIGNED NOT NULL,
    case_id         INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    description     VARCHAR(255) NOT NULL,
    minutes         INT UNSIGNED NOT NULL,
    hourly_rate_usd DECIMAL(8,2) NOT NULL DEFAULT 0,
    is_billable     TINYINT(1) NOT NULL DEFAULT 1,
    is_billed       TINYINT(1) NOT NULL DEFAULT 0,
    entry_date      DATE NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_te_tenant_case (tenant_id, case_id),
    KEY ix_te_user_date (user_id, entry_date),
    CONSTRAINT fk_te_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE,
    CONSTRAINT fk_te_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
