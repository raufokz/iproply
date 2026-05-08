-- =============================================================================
-- 003_properties_tenant_slug.sql — tenant column + per-tenant slug uniqueness
-- Prerequisites: 002 applied
-- =============================================================================

SET NAMES utf8mb4;

ALTER TABLE properties
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

-- Align with owning agent (covers any future agent moves)
UPDATE properties p
INNER JOIN agents a ON a.id = p.agent_id
SET p.tenant_id = a.tenant_id;

ALTER TABLE properties
    ADD CONSTRAINT fk_properties_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

-- Global slug unique → per-tenant (required for SaaS)
ALTER TABLE properties DROP INDEX slug;

ALTER TABLE properties
    ADD UNIQUE KEY uq_properties_tenant_slug (tenant_id, slug);

-- Hot-path listing indexes (public search + admin filters)
CREATE INDEX idx_properties_tenant_status_created
    ON properties (tenant_id, property_status, created_at DESC);

CREATE INDEX idx_properties_tenant_city_status
    ON properties (tenant_id, city(64), property_status);

CREATE INDEX idx_properties_tenant_state_status
    ON properties (tenant_id, state, property_status);

CREATE INDEX idx_properties_tenant_price
    ON properties (tenant_id, price);
