SET NAMES utf8mb4;

ALTER TABLE properties DROP FOREIGN KEY fk_properties_tenant;

DROP INDEX idx_properties_tenant_price ON properties;
DROP INDEX idx_properties_tenant_state_status ON properties;
DROP INDEX idx_properties_tenant_city_status ON properties;
DROP INDEX idx_properties_tenant_status_created ON properties;

ALTER TABLE properties DROP INDEX uq_properties_tenant_slug;

ALTER TABLE properties ADD UNIQUE KEY slug (slug);

ALTER TABLE properties DROP COLUMN tenant_id;
