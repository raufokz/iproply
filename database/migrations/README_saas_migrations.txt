iProply SaaS-oriented schema migrations (MySQL 8.x recommended; 5.7 compatible except JSON columns — tenant_settings.feature_flags requires 5.7.8+)

FORWARD ORDER (run once per environment after backup):
  001_tenants_and_seed.sql
  002_agents_tenant_scope.sql
  003_properties_tenant_slug.sql
  004_inquiries_property_analytics_tenant.sql
  005_cms_newsletter_catalog_admins_tenant.sql
  006_tenant_settings.sql
  007_soft_delete_columns.sql          (optional; omit if you do not want soft-delete)
  008_trigger_property_views_tenant.sql  (requires mysql CLI or client that honors DELIMITER)

ROLLBACK ORDER (reverse; only before production traffic depends on tenants):
  rollback_008_trigger_property_views_tenant.sql
  rollback_007_soft_delete_columns.sql          (if 007 applied)
  rollback_006_tenant_settings.sql
  rollback_005_cms_newsletter_catalog_admins_tenant.sql
  rollback_004_inquiries_property_analytics_tenant.sql
  rollback_003_properties_tenant_slug.sql
  rollback_002_agents_tenant_scope.sql
  rollback_001_tenants_and_seed.sql

PREREQS:
  Full database backup / snapshot.
  If property_analytics never existed, run DDL from database.sql for that table only BEFORE 008, or extend 004 "skip" path with CREATE TABLE — see main audit docs.

APPLICATION FOLLOW-UP (required for real isolation):
  Resolve current tenant from subdomain / session / JWT on every SQL path.
  Add tenant predicate (tenant_id = :tid) or strict joins from agents/properties.
  On INSERT agents/properties/inquiries/etc., set tenant_id explicitly (avoid relying only on DEFAULT 1).

GREENFIELD FIX:
  Reorder database.sql so CREATE TABLE property_analytics appears BEFORE CREATE TRIGGER trg_increment_property_views.

PRODUCTION / GITHUB ACTIONS:
  After rsync, .github/workflows/deploy.yml runs database/migrations/run_all_migrations.sh over SSH.
  Add repository secrets (Settings -> Secrets):
    HOSTINGER_DB_HOST   (often 127.0.0.1 on VPS)
    HOSTINGER_DB_USER
    HOSTINGER_DB_PASSWORD
    HOSTINGER_DB_NAME
  If any DB secret is empty, the migration step is skipped with a warning.

  Server requirements: mysql or mariadb client in PATH on the Hostinger shell.
  Alternative: store only on server a file e.g. ~/.mysql/iproply.cnf (chmod 600) with [client] host,user,password,database
  and change the workflow SSH command to export MYSQL_CNF=... instead of MYSQL_PASSWORD (advanced).

  Tracking: table schema_migrations records applied *.sql basenames; safe to re-run deploy.
