SET NAMES utf8mb4;

DROP INDEX idx_inquiries_tenant_agent_status_created ON inquiries;
ALTER TABLE inquiries DROP FOREIGN KEY fk_inquiries_tenant;
ALTER TABLE inquiries DROP COLUMN tenant_id;

-- property_analytics optional columns
SET @db := DATABASE();
SET @exists := (
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = @db AND table_name = 'property_analytics'
);
SET @sql := IF(
    @exists > 0,
    'ALTER TABLE property_analytics DROP FOREIGN KEY fk_property_analytics_tenant',
    'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF(
    @exists > 0,
    'DROP INDEX idx_property_analytics_tenant_date ON property_analytics',
    'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF(
    @exists > 0,
    'ALTER TABLE property_analytics DROP COLUMN tenant_id',
    'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
