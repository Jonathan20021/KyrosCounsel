-- KyrosCounsel - Schema completo
-- Ejecutar en phpMyAdmin o consola mysql.

CREATE DATABASE IF NOT EXISTS kyroscounsel
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kyroscounsel;
SET sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS workflow_runs;
DROP TABLE IF EXISTS workflows;
DROP TABLE IF EXISTS email_log;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS case_status_history;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS plans;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS rate_limits;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS tenants;
SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- TABLAS GLOBALES
-- ====================================================================

CREATE TABLE plans (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code          VARCHAR(32)  NOT NULL,
    name          VARCHAR(100) NOT NULL,
    price_usd     DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_users     INT UNSIGNED NOT NULL DEFAULT 5,
    max_cases     INT UNSIGNED NOT NULL DEFAULT 100,
    max_storage_mb INT UNSIGNED NOT NULL DEFAULT 1024,
    features_json JSON NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    sort_order    INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_plans_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tenants (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug          VARCHAR(64)  NOT NULL,
    name          VARCHAR(200) NOT NULL,
    country       CHAR(2)      NOT NULL DEFAULT 'US',
    status        ENUM('active','suspended','cancelled','pending','trial') NOT NULL DEFAULT 'trial',
    plan_id       INT UNSIGNED NULL,
    trial_ends_at DATETIME NULL,
    settings_json JSON NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tenants_slug (slug),
    KEY ix_tenants_status (status),
    KEY ix_tenants_country (country),
    CONSTRAINT fk_tenants_plan FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id         INT UNSIGNED NULL,
    email             VARCHAR(191) NOT NULL,
    email_verified_at DATETIME NULL,
    password_hash     VARCHAR(255) NOT NULL,
    name              VARCHAR(150) NOT NULL,
    role              ENUM('super_admin','tenant_admin','attorney','paralegal','staff','client')
                          NOT NULL DEFAULT 'staff',
    status            ENUM('active','invited','suspended','disabled') NOT NULL DEFAULT 'active',
    two_factor_secret VARCHAR(255) NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at     DATETIME NULL,
    last_login_ip     VARCHAR(45) NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY ix_users_tenant (tenant_id),
    KEY ix_users_tenant_role (tenant_id, role),
    CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limits (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    bucket     VARCHAR(64) NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY ix_rl_bucket_time (bucket, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLAS POR-TENANT (todas tienen tenant_id)
-- ====================================================================

CREATE TABLE audit_log (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id     INT UNSIGNED NULL,
    actor_user_id INT UNSIGNED NULL,
    event         VARCHAR(100) NOT NULL,
    ip            VARCHAR(45) NULL,
    user_agent    VARCHAR(500) NULL,
    context_json  JSON NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_audit_tenant_time (tenant_id, created_at),
    KEY ix_audit_actor (actor_user_id),
    KEY ix_audit_event (event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id       INT UNSIGNED NOT NULL,
    plan_id         INT UNSIGNED NOT NULL,
    status          ENUM('trial','active','past_due','cancelled') NOT NULL DEFAULT 'trial',
    starts_at       DATETIME NOT NULL,
    ends_at         DATETIME NULL,
    next_billing_at DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_sub_tenant (tenant_id),
    KEY ix_sub_status (status),
    CONSTRAINT fk_sub_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_plan FOREIGN KEY (plan_id) REFERENCES plans (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id          INT UNSIGNED NOT NULL,
    first_name         VARCHAR(100) NOT NULL,
    last_name          VARCHAR(100) NOT NULL,
    email              VARCHAR(191) NULL,
    phone              VARCHAR(40)  NULL,
    nationality        CHAR(2)      NULL COMMENT 'ISO alpha-2',
    country_residence  CHAR(2)      NULL,
    -- PII cifrada con sodium (pii_encrypt). NUNCA almacenar en claro.
    passport_enc       TEXT NULL,
    passport_index     CHAR(64) NULL COMMENT 'blind index para busqueda exacta',
    alien_number_enc   TEXT NULL,
    alien_number_index CHAR(64) NULL,
    date_of_birth_enc  TEXT NULL,
    address            VARCHAR(300) NULL,
    notes              TEXT NULL,
    status             ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
    created_by         INT UNSIGNED NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_clients_tenant (tenant_id),
    KEY ix_clients_tenant_status (tenant_id, status),
    KEY ix_clients_tenant_name (tenant_id, last_name, first_name),
    KEY ix_clients_passport (passport_index),
    KEY ix_clients_alien (alien_number_index),
    CONSTRAINT fk_clients_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cases (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    client_id    INT UNSIGNED NOT NULL,
    case_number  VARCHAR(50) NOT NULL,
    title        VARCHAR(200) NOT NULL,
    case_type    VARCHAR(50) NOT NULL COMMENT 'Codigo: I-130, I-485, asylum, etc.',
    country      CHAR(2) NOT NULL DEFAULT 'US',
    status       ENUM('intake','preparing','filed','rfe','approved','denied','withdrawn','closed')
                     NOT NULL DEFAULT 'intake',
    priority     ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    attorney_id  INT UNSIGNED NULL,
    opened_at    DATE NOT NULL,
    filed_at     DATE NULL,
    decision_at  DATE NULL,
    description  TEXT NULL,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cases_number (tenant_id, case_number),
    KEY ix_cases_tenant_status (tenant_id, status),
    KEY ix_cases_tenant_client (tenant_id, client_id),
    KEY ix_cases_tenant_attorney (tenant_id, attorney_id),
    KEY ix_cases_tenant_type (tenant_id, case_type),
    CONSTRAINT fk_cases_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_cases_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE case_status_history (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id     INT UNSIGNED NOT NULL,
    case_id       INT UNSIGNED NOT NULL,
    from_status   VARCHAR(20) NULL,
    to_status     VARCHAR(20) NOT NULL,
    note          TEXT NULL,
    changed_by    INT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_csh_tenant_case (tenant_id, case_id),
    CONSTRAINT fk_csh_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id     INT UNSIGNED NOT NULL,
    case_id       INT UNSIGNED NULL,
    client_id     INT UNSIGNED NULL,
    name          VARCHAR(255) NOT NULL,
    category      VARCHAR(50) NULL COMMENT 'passport, birth_cert, i797, etc.',
    mime_type     VARCHAR(100) NOT NULL,
    size_bytes    BIGINT UNSIGNED NOT NULL,
    storage_path  VARCHAR(255) NOT NULL COMMENT 'ruta relativa en /uploads/{tenant}/...',
    sha256_hash   CHAR(64) NOT NULL COMMENT 'integridad',
    encrypted     TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by   INT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_docs_tenant (tenant_id),
    KEY ix_docs_tenant_case (tenant_id, case_id),
    KEY ix_docs_tenant_client (tenant_id, client_id),
    CONSTRAINT fk_docs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_docs_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE SET NULL,
    CONSTRAINT fk_docs_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NULL,
    client_id    INT UNSIGNED NULL,
    title        VARCHAR(200) NOT NULL,
    description  TEXT NULL,
    assignee_id  INT UNSIGNED NULL,
    due_date     DATE NULL,
    priority     ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    status       ENUM('pending','in_progress','done','cancelled') NOT NULL DEFAULT 'pending',
    completed_at DATETIME NULL,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_tasks_tenant_status (tenant_id, status),
    KEY ix_tasks_tenant_assignee (tenant_id, assignee_id),
    KEY ix_tasks_tenant_due (tenant_id, due_date),
    CONSTRAINT fk_tasks_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notes (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id   INT UNSIGNED NOT NULL,
    case_id     INT UNSIGNED NULL,
    client_id   INT UNSIGNED NULL,
    body        TEXT NOT NULL,
    author_id   INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_notes_tenant_case (tenant_id, case_id),
    KEY ix_notes_tenant_client (tenant_id, client_id),
    CONSTRAINT fk_notes_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workflows (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id   INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    trigger_event VARCHAR(50) NOT NULL COMMENT 'client.created, case.status_changed, etc.',
    conditions_json JSON NULL,
    actions_json JSON NOT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_by  INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_wf_tenant_event (tenant_id, trigger_event),
    CONSTRAINT fk_wf_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workflow_runs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id     INT UNSIGNED NOT NULL,
    workflow_id   INT UNSIGNED NOT NULL,
    event         VARCHAR(50) NOT NULL,
    payload_json  JSON NULL,
    status        ENUM('success','failed','skipped') NOT NULL,
    error_message TEXT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_wfr_tenant_wf (tenant_id, workflow_id),
    CONSTRAINT fk_wfr_workflow FOREIGN KEY (workflow_id) REFERENCES workflows (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_log (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NULL,
    to_email     VARCHAR(191) NOT NULL,
    subject      VARCHAR(255) NOT NULL,
    template     VARCHAR(80) NULL,
    status       ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
    provider_id  VARCHAR(100) NULL,
    error_message TEXT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at      DATETIME NULL,
    PRIMARY KEY (id),
    KEY ix_email_tenant (tenant_id),
    KEY ix_email_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- DATOS INICIALES (planes)
-- ====================================================================

INSERT INTO plans (code, name, price_usd, max_users, max_cases, max_storage_mb, features_json, sort_order) VALUES
('basic', 'Basico', 49.00, 50, 5000, 51200,
    JSON_OBJECT('emails', 1000, 'workflows', 1, 'two_factor', 1, 'documents', 1, 'reports', 1, 'calendar', 1,
                'finance', 1, 'templates', 1, 'time_tracking', 1, 'payment_links', 1, 'client_portal', 1,
                'cover_letters', 1, 'audit', 1, 'priority_support', 0), 1),
('pro', 'Pro', 149.00, 100, 10000, 102400,
    JSON_OBJECT('emails', 5000, 'workflows', 1, 'two_factor', 1, 'documents', 1, 'reports', 1, 'calendar', 1,
                'finance', 1, 'templates', 1, 'time_tracking', 1, 'payment_links', 1, 'client_portal', 1,
                'cover_letters', 1, 'audit', 1, 'priority_support', 0), 2),
('enterprise', 'Enterprise', 399.00, 500, 100000, 1048576,
    JSON_OBJECT('emails', 50000, 'workflows', 1, 'two_factor', 1, 'documents', 1, 'reports', 1, 'calendar', 1,
                'finance', 1, 'templates', 1, 'time_tracking', 1, 'payment_links', 1, 'client_portal', 1,
                'cover_letters', 1, 'audit', 1, 'priority_support', 1), 3);
