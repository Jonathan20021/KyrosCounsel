USE kyroscounsel;

DROP TABLE IF EXISTS email_templates;
CREATE TABLE email_templates (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    name         VARCHAR(150) NOT NULL,
    slug         VARCHAR(80) NOT NULL,
    category     VARCHAR(50) NULL COMMENT 'welcome, status_update, reminder, document_request, etc.',
    subject      VARCHAR(255) NOT NULL,
    body_html    TEXT NOT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tpl_tenant_slug (tenant_id, slug),
    KEY ix_tpl_tenant (tenant_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
