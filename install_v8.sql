USE kyroscounsel;

-- Login propio del cliente (email + password) ademas de tokens
ALTER TABLE clients
    ADD COLUMN IF NOT EXISTS portal_password_hash VARCHAR(255) NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS portal_enabled       TINYINT(1) NOT NULL DEFAULT 0 AFTER portal_password_hash,
    ADD COLUMN IF NOT EXISTS portal_last_login_at DATETIME NULL AFTER portal_enabled;

-- Sesiones del cliente (independiente de la tabla de sesiones del staff)
DROP TABLE IF EXISTS client_sessions;
CREATE TABLE client_sessions (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id     INT UNSIGNED NOT NULL,
    client_id     INT UNSIGNED NOT NULL,
    session_hash  CHAR(64) NOT NULL,
    ip            VARCHAR(45) NULL,
    user_agent    VARCHAR(500) NULL,
    expires_at    DATETIME NOT NULL,
    revoked_at    DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_csession_hash (session_hash),
    KEY ix_csession_client (client_id),
    CONSTRAINT fk_csession_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payment links (Stripe checkout - simulado pero compatible con Stripe Sessions API)
DROP TABLE IF EXISTS payment_links;
CREATE TABLE payment_links (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id      INT UNSIGNED NOT NULL,
    payment_id     INT UNSIGNED NOT NULL COMMENT 'FK case_payments',
    case_id        INT UNSIGNED NOT NULL,
    token          CHAR(40) NOT NULL,
    amount_usd     DECIMAL(10,2) NOT NULL,
    currency       CHAR(3) NOT NULL DEFAULT 'USD',
    status         ENUM('open','paid','expired','cancelled') NOT NULL DEFAULT 'open',
    stripe_session_id VARCHAR(120) NULL COMMENT 'cs_test_xxx (real Stripe) o sim_xxx',
    paid_at        DATETIME NULL,
    expires_at     DATETIME NOT NULL,
    created_by     INT UNSIGNED NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pl_token (token),
    KEY ix_pl_tenant_case (tenant_id, case_id),
    KEY ix_pl_status (status),
    CONSTRAINT fk_pl_payment FOREIGN KEY (payment_id) REFERENCES case_payments (id) ON DELETE CASCADE,
    CONSTRAINT fk_pl_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
