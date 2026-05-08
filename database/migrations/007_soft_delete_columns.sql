-- =============================================================================
-- 007_soft_delete_columns.sql — optional soft-delete (non-destructive)
-- =============================================================================

SET NAMES utf8mb4;

ALTER TABLE agents
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at,
    ADD INDEX idx_agents_deleted (tenant_id, deleted_at);

ALTER TABLE properties
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at,
    ADD INDEX idx_properties_tenant_deleted (tenant_id, deleted_at);
