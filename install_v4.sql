-- KyrosCounsel - Schema v4
-- Notificaciones persistentes + portal del cliente

USE kyroscounsel;

-- Tabla de notificaciones in-app
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NULL COMMENT 'Destinatario; NULL = broadcast tenant',
    type         VARCHAR(50) NOT NULL COMMENT 'task_overdue, case_rfe, case_status, document_uploaded, etc.',
    title        VARCHAR(255) NOT NULL,
    body         TEXT NULL,
    url          VARCHAR(500) NULL,
    icon         VARCHAR(20) NULL,
    severity     ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    read_at      DATETIME NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_notif_tenant_user_read (tenant_id, user_id, read_at),
    KEY ix_notif_created (created_at),
    CONSTRAINT fk_notif_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tokens del portal del cliente (link compartible, expira)
DROP TABLE IF EXISTS client_portal_tokens;
CREATE TABLE client_portal_tokens (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id      INT UNSIGNED NOT NULL,
    client_id      INT UNSIGNED NOT NULL,
    token_hash     CHAR(64) NOT NULL COMMENT 'sha256 del token; el plaintext solo se muestra al crear',
    expires_at     DATETIME NOT NULL,
    last_used_at   DATETIME NULL,
    revoked_at     DATETIME NULL,
    created_by     INT UNSIGNED NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token_hash (token_hash),
    KEY ix_portal_tenant_client (tenant_id, client_id),
    CONSTRAINT fk_portal_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
