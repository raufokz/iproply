-- =============================================================================
-- 004_inquiries_property_analytics_tenant.sql
-- Prerequisites: 003 applied
-- =============================================================================

SET NAMES utf8mb4;

-- Inquiries: fast tenant-scoped agent inboxes + integrity for future RLS-style filters
ALTER TABLE inquiries
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

UPDATE inquiries i
INNER JOIN properties p ON p.id = i.property_id
SET i.tenant_id = p.tenant_id;

ALTER TABLE inquiries
    ADD CONSTRAINT fk_inquiries_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

CREATE INDEX idx_inquiries_tenant_agent_status_created
    ON inquiries (tenant_id, agent_id, status, created_at DESC);

-- property_analytics (table may be empty) — align rows with property tenant for partitioning / reporting
-- If table does not exist yet (older installs), skip tenant column until table is created.
SET @db := DATABASE();
SET @exists := (
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = @db AND table_name = 'property_analytics'
);

SET @sql := IF(
    @exists > 0,
    'ALTER TABLE property_analytics ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id',
    'SELECT "skip: property_analytics missing" AS notice'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql2 := IF(
    @exists > 0,
    'UPDATE property_analytics pa INNER JOIN properties p ON p.id = pa.property_id SET pa.tenant_id = p.tenant_id',
    'SELECT 1'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql3 := IF(
    @exists > 0,
    'ALTER TABLE property_analytics ADD CONSTRAINT fk_property_analytics_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @sql4 := IF(
    @exists > 0,
    'CREATE INDEX idx_property_analytics_tenant_date ON property_analytics (tenant_id, view_date)',
    'SELECT 1'
);
PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;
