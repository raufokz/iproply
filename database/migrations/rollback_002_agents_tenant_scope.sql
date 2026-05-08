-- Rollback 002 — restores global unique username/email (single-tenant only safe)
SET NAMES utf8mb4;

ALTER TABLE agents DROP FOREIGN KEY fk_agents_tenant;
ALTER TABLE agents DROP INDEX idx_agents_tenant_status;
ALTER TABLE agents DROP INDEX uq_agents_tenant_username;
ALTER TABLE agents DROP INDEX uq_agents_tenant_email;

ALTER TABLE agents
    ADD UNIQUE KEY username (username),
    ADD UNIQUE KEY email (email);

ALTER TABLE agents DROP COLUMN tenant_id;
