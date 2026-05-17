USE kyroscounsel;

DROP TABLE IF EXISTS saved_views;
CREATE TABLE saved_views (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id    INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NULL COMMENT 'NULL = compartida con todo el bufete',
    module       VARCHAR(40) NOT NULL COMMENT 'cases, clients, tasks',
    name         VARCHAR(150) NOT NULL,
    filters_json TEXT NOT NULL,
    icon         VARCHAR(20) NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_views_tenant_user_mod (tenant_id, user_id, module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
