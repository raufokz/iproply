-- Rollback 001 — only if no FKs reference tenants yet
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tenants;
SET FOREIGN_KEY_CHECKS = 1;
