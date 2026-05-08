SET NAMES utf8mb4;

ALTER TABLE admins DROP FOREIGN KEY fk_admins_tenant;
DROP INDEX idx_admins_tenant_status ON admins;
ALTER TABLE admins DROP COLUMN tenant_id;

ALTER TABLE property_types DROP FOREIGN KEY fk_property_types_tenant;
DROP INDEX idx_property_types_tenant_category ON property_types;
ALTER TABLE property_types DROP INDEX uq_property_types_tenant_slug;
ALTER TABLE property_types ADD UNIQUE KEY slug (slug);
ALTER TABLE property_types DROP COLUMN tenant_id;

ALTER TABLE categories DROP FOREIGN KEY fk_categories_tenant;
DROP INDEX idx_categories_tenant_status ON categories;
ALTER TABLE categories DROP INDEX uq_categories_tenant_slug;
ALTER TABLE categories ADD UNIQUE KEY slug (slug);
ALTER TABLE categories DROP COLUMN tenant_id;

ALTER TABLE newsletter_subscribers DROP FOREIGN KEY fk_newsletter_tenant;
ALTER TABLE newsletter_subscribers DROP INDEX uq_newsletter_tenant_email;
ALTER TABLE newsletter_subscribers ADD UNIQUE KEY email (email);
ALTER TABLE newsletter_subscribers DROP COLUMN tenant_id;

ALTER TABLE pages DROP FOREIGN KEY fk_pages_tenant;
DROP INDEX idx_pages_tenant_published ON pages;
DROP INDEX idx_pages_tenant_footer ON pages;
ALTER TABLE pages DROP INDEX uq_pages_tenant_slug;
ALTER TABLE pages ADD UNIQUE KEY slug (slug);
ALTER TABLE pages DROP COLUMN tenant_id;

ALTER TABLE blogs DROP FOREIGN KEY fk_blogs_tenant;
DROP INDEX idx_blogs_tenant_status_published ON blogs;
ALTER TABLE blogs DROP INDEX uq_blogs_tenant_slug;
ALTER TABLE blogs ADD UNIQUE KEY slug (slug);
ALTER TABLE blogs DROP COLUMN tenant_id;
