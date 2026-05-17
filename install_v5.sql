-- KyrosCounsel - Schema v5
-- Mensajeria abogado <-> cliente (portal)

USE kyroscounsel;

DROP TABLE IF EXISTS case_messages;
CREATE TABLE case_messages (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    case_id      INT UNSIGNED NOT NULL,
    client_id    INT UNSIGNED NOT NULL,
    sender_type  ENUM('staff','client') NOT NULL,
    sender_user_id INT UNSIGNED NULL,
    body         TEXT NOT NULL,
    read_at      DATETIME NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_msg_tenant_case (tenant_id, case_id, created_at),
    KEY ix_msg_unread (tenant_id, sender_type, read_at),
    CONSTRAINT fk_msg_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
