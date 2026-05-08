-- =============================================================================
-- 002_agents_tenant_scope.sql — agents belong to a tenant; composite uniqueness
-- Prerequisites: 001 applied
-- =============================================================================

SET NAMES utf8mb4;

-- 1) Add column (legacy rows become tenant 1)
ALTER TABLE agents
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE agents
    ADD CONSTRAINT fk_agents_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

-- 2) Replace global UNIQUE(username/email) with per-tenant uniqueness (SaaS isolation)
-- InnoDB index names match column name for single-column UNIQUE in this schema.
ALTER TABLE agents DROP INDEX username;
ALTER TABLE agents DROP INDEX email;

ALTER TABLE agents
    ADD UNIQUE KEY uq_agents_tenant_username (tenant_id, username),
    ADD UNIQUE KEY uq_agents_tenant_email (tenant_id, email);

CREATE INDEX idx_agents_tenant_status ON agents (tenant_id, status);
