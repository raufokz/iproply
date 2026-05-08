-- =============================================================================
-- 006_tenant_settings.sql — per-tenant branding / SMTP overrides (additive)
-- Legacy site_settings row remains for single-tenant / bootstrap compatibility.
-- Application can merge: COALESCE(tenant_settings.x, site_settings.x)
-- =============================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS tenant_settings (
    tenant_id INT UNSIGNED NOT NULL PRIMARY KEY,
    site_name VARCHAR(255) NULL,
    site_tagline VARCHAR(500) NULL,
    site_email VARCHAR(255) NULL,
    site_phone VARCHAR(50) NULL,
    site_address TEXT NULL,
    site_logo VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    smtp_host VARCHAR(255) NULL,
    smtp_port INT NULL,
    smtp_username VARCHAR(255) NULL,
    smtp_password_encrypted VARBINARY(512) NULL COMMENT 'Use app-level encryption; never store plain text',
    smtp_encryption VARCHAR(20) NULL,
    facebook_url VARCHAR(500) NULL,
    twitter_url VARCHAR(500) NULL,
    instagram_url VARCHAR(500) NULL,
    linkedin_url VARCHAR(500) NULL,
    feature_flags JSON NULL COMMENT 'JSON map: { "feature_x": true }',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tenant_settings_tenant
        FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed empty row for default tenant (optional; app may lazy-create)
INSERT INTO tenant_settings (tenant_id)
VALUES (1)
ON DUPLICATE KEY UPDATE tenant_id = tenant_id;
