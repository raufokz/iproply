-- =============================================================================
-- 005_cms_newsletter_catalog_admins_tenant.sql
-- Prerequisites: 001 applied (tenants exist)
-- Blogs/pages/newsletter/catalog scoped per tenant; admins get optional tenant_id
-- =============================================================================

SET NAMES utf8mb4;

-- --- blogs ---
ALTER TABLE blogs
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE blogs
    ADD CONSTRAINT fk_blogs_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE blogs DROP INDEX slug;

ALTER TABLE blogs
    ADD UNIQUE KEY uq_blogs_tenant_slug (tenant_id, slug);

CREATE INDEX idx_blogs_tenant_status_published ON blogs (tenant_id, status, published_at DESC);

-- --- pages ---
ALTER TABLE pages
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE pages
    ADD CONSTRAINT fk_pages_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE pages DROP INDEX slug;

ALTER TABLE pages
    ADD UNIQUE KEY uq_pages_tenant_slug (tenant_id, slug);

CREATE INDEX idx_pages_tenant_footer ON pages (
    tenant_id,
    status,
    show_in_footer,
    footer_section,
    footer_order
);

CREATE INDEX idx_pages_tenant_published ON pages (tenant_id, published_at DESC);

-- --- newsletter_subscribers ---
ALTER TABLE newsletter_subscribers
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE newsletter_subscribers
    ADD CONSTRAINT fk_newsletter_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE newsletter_subscribers DROP INDEX email;

ALTER TABLE newsletter_subscribers
    ADD UNIQUE KEY uq_newsletter_tenant_email (tenant_id, email);

-- --- categories (per-tenant catalog; legacy rows = tenant 1) ---
ALTER TABLE categories
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE categories
    ADD CONSTRAINT fk_categories_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE categories DROP INDEX slug;

ALTER TABLE categories
    ADD UNIQUE KEY uq_categories_tenant_slug (tenant_id, slug);

CREATE INDEX idx_categories_tenant_status ON categories (tenant_id, status);

-- --- property_types ---
ALTER TABLE property_types
    ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;

ALTER TABLE property_types
    ADD CONSTRAINT fk_property_types_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE property_types DROP INDEX slug;

ALTER TABLE property_types
    ADD UNIQUE KEY uq_property_types_tenant_slug (tenant_id, slug);

CREATE INDEX idx_property_types_tenant_category ON property_types (tenant_id, category_id, status);

-- --- admins: optional org scope; NULL = platform / legacy global admin ---
ALTER TABLE admins
    ADD COLUMN tenant_id INT UNSIGNED NULL DEFAULT NULL AFTER id;

ALTER TABLE admins
    ADD CONSTRAINT fk_admins_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

CREATE INDEX idx_admins_tenant_status ON admins (tenant_id, status);
