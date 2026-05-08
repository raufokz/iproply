SET NAMES utf8mb4;

ALTER TABLE properties DROP INDEX idx_properties_tenant_deleted;
ALTER TABLE properties DROP COLUMN deleted_at;

ALTER TABLE agents DROP INDEX idx_agents_deleted;
ALTER TABLE agents DROP COLUMN deleted_at;
