-- KyrosCounsel - Schema upgrade v2 (immigration-specific)
-- Aplicar despues de install.sql. Idempotente.

USE kyroscounsel;
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================================
-- Cases: campos USCIS + finanzas
-- ========================================================================
ALTER TABLE cases
    ADD COLUMN IF NOT EXISTS uscis_receipt   VARCHAR(20)    NULL AFTER decision_at,
    ADD COLUMN IF NOT EXISTS receipt_date    DATE           NULL AFTER uscis_receipt,
    ADD COLUMN IF NOT EXISTS biometrics_at   DATE           NULL AFTER receipt_date,
    ADD COLUMN IF NOT EXISTS interview_at    DATE           NULL AFTER biometrics_at,
    ADD COLUMN IF NOT EXISTS rfe_due_at      DATE           NULL AFTER interview_at,
    ADD COLUMN IF NOT EXISTS filing_fee_usd  DECIMAL(10,2)  NULL DEFAULT 0 AFTER rfe_due_at,
    ADD COLUMN IF NOT EXISTS attorney_fee_usd DECIMAL(10,2) NULL DEFAULT 0 AFTER filing_fee_usd,
    ADD COLUMN IF NOT EXISTS service_center  VARCHAR(20)    NULL AFTER attorney_fee_usd;

-- Tenants: branding
ALTER TABLE tenants
    ADD COLUMN IF NOT EXISTS brand_color  VARCHAR(7) NULL DEFAULT '#4f46e5' AFTER settings_json,
    ADD COLUMN IF NOT EXISTS logo_path    VARCHAR(255) NULL AFTER brand_color,
    ADD COLUMN IF NOT EXISTS locale       VARCHAR(5) NOT NULL DEFAULT 'es' AFTER logo_path;

-- ========================================================================
-- Beneficiarios y dependientes del caso
-- ========================================================================
DROP TABLE IF EXISTS case_beneficiaries;
CREATE TABLE case_beneficiaries (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id       INT UNSIGNED NOT NULL,
    case_id         INT UNSIGNED NOT NULL,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    relationship    VARCHAR(50)  NOT NULL COMMENT 'spouse, child, parent, sibling, derivative, principal',
    date_of_birth   DATE NULL,
    nationality     CHAR(2) NULL,
    passport_enc    TEXT NULL,
    alien_number_enc TEXT NULL,
    notes           TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_benef_tenant_case (tenant_id, case_id),
    CONSTRAINT fk_benef_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Pagos al bufete y filing fees
-- ========================================================================
DROP TABLE IF EXISTS case_payments;
CREATE TABLE case_payments (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NOT NULL,
    concept      VARCHAR(150) NOT NULL,
    category     ENUM('attorney_fee','filing_fee','biometrics','translation','other') NOT NULL DEFAULT 'attorney_fee',
    amount_usd   DECIMAL(10,2) NOT NULL,
    paid_at      DATE NULL,
    method       ENUM('cash','transfer','card','check','other') NULL,
    reference    VARCHAR(100) NULL,
    status       ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    notes        TEXT NULL,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_pay_tenant_case (tenant_id, case_id),
    KEY ix_pay_tenant_status (tenant_id, status),
    CONSTRAINT fk_pay_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Citas / appointments (entrevistas USCIS, biometrics, abogado)
-- ========================================================================
DROP TABLE IF EXISTS case_appointments;
CREATE TABLE case_appointments (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NOT NULL,
    title        VARCHAR(200) NOT NULL,
    type         ENUM('interview','biometrics','consultation','court','other') NOT NULL DEFAULT 'consultation',
    starts_at    DATETIME NOT NULL,
    duration_min INT UNSIGNED DEFAULT 60,
    location     VARCHAR(255) NULL,
    notes        TEXT NULL,
    reminded_at  DATETIME NULL,
    status       ENUM('scheduled','confirmed','done','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_appt_tenant_case (tenant_id, case_id),
    KEY ix_appt_tenant_starts (tenant_id, starts_at),
    CONSTRAINT fk_appt_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Items de evidencia / checklist por caso
-- ========================================================================
DROP TABLE IF EXISTS case_evidence;
CREATE TABLE case_evidence (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NOT NULL,
    name         VARCHAR(200) NOT NULL,
    category     VARCHAR(50) NULL,
    is_required  TINYINT(1) NOT NULL DEFAULT 1,
    is_received  TINYINT(1) NOT NULL DEFAULT 0,
    received_at  DATE NULL,
    document_id  INT UNSIGNED NULL COMMENT 'Doc subido que satisface el item',
    notes        TEXT NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_ev_tenant_case (tenant_id, case_id),
    CONSTRAINT fk_ev_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE,
    CONSTRAINT fk_ev_doc  FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
