-- =============================================================================
-- 001_tenants_and_seed.sql — SaaS core: tenants table + default row
-- Prerequisites: Existing iProply schema (agents, properties, ...)
-- Safe: additive only (no DROP). Idempotent-ish: fails if tenants already exists with data you duplicate.
--
-- APPLY:  mysql ... < 001_tenants_and_seed.sql
-- ROLLBACK: run rollback_001_tenants_and_seed.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_ref CHAR(36) NULL COMMENT 'Optional UUID / external billing id',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL COMMENT 'Stable key for subdomain or routing',
    status ENUM('active','suspended','trial','cancelled') NOT NULL DEFAULT 'active',
    plan_tier VARCHAR(50) NULL COMMENT 'commercial plan label (stripe product, etc.)',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tenants_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deterministic bootstrap tenant used to back-fill all legacy rows (id must stay 1 for DEFAULT 1 FKs).
INSERT INTO tenants (id, name, slug, status, plan_tier)
VALUES (1, 'Default organization', 'default', 'active', 'legacy')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    status = VALUES(status);

-- Next auto id after explicit 1
ALTER TABLE tenants AUTO_INCREMENT = 2;

SET FOREIGN_KEY_CHECKS = 1;
