-- =============================================================================
-- 009_property_management_hardening.sql
-- Property workflow statuses, gallery ordering, and listing indexes.
-- =============================================================================

SET NAMES utf8mb4;

-- Convert the legacy "featured" workflow value into the dedicated feature flag.
UPDATE properties
SET is_featured = 1,
    property_status = 'active',
    updated_at = NOW()
WHERE property_status = 'featured';

-- Workflow status drives public visibility. Only "active" is publicly listed.
ALTER TABLE properties
    MODIFY property_status ENUM('active', 'inactive', 'draft', 'pending', 'sold', 'rented') NOT NULL DEFAULT 'draft';

-- Backfill a primary image where a property has images but none is marked primary.
UPDATE property_images pi
INNER JOIN (
    SELECT property_id, MIN(id) AS image_id
    FROM property_images
    WHERE property_id NOT IN (
        SELECT DISTINCT property_id
        FROM property_images
        WHERE is_primary = 1
    )
    GROUP BY property_id
) fallback ON fallback.image_id = pi.id
SET pi.is_primary = 1;

ALTER TABLE property_images
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX idx_properties_public_listing
    ON properties (tenant_id, property_status, is_featured, created_at);

CREATE INDEX idx_properties_agent_status
    ON properties (tenant_id, agent_id, property_status, created_at);

CREATE INDEX idx_properties_type_status_price
    ON properties (tenant_id, property_type_id, property_status, price);

CREATE INDEX idx_properties_category_status_price
    ON properties (tenant_id, category_id, property_status, price);

CREATE INDEX idx_property_images_primary_order
    ON property_images (property_id, is_primary, display_order, id);

CREATE INDEX idx_categories_status_order
    ON categories (status, display_order);

CREATE INDEX idx_property_types_status_order
    ON property_types (status, display_order);

DROP PROCEDURE IF EXISTS sp_get_agent_dashboard;
DROP PROCEDURE IF EXISTS sp_get_admin_dashboard;

DELIMITER //

CREATE PROCEDURE sp_get_agent_dashboard(IN p_agent_id INT)
BEGIN
    SELECT
        COUNT(DISTINCT p.id) AS total_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'active' THEN p.id END) AS active_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'inactive' THEN p.id END) AS inactive_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'draft' THEN p.id END) AS draft_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'pending' THEN p.id END) AS pending_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'sold' THEN p.id END) AS sold_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'rented' THEN p.id END) AS rented_properties,
        COUNT(DISTINCT i.id) AS total_inquiries,
        COUNT(DISTINCT CASE WHEN i.status = 'new' THEN i.id END) AS new_inquiries,
        COUNT(DISTINCT CASE WHEN i.status = 'responded' THEN i.id END) AS responded_inquiries
    FROM agents a
    LEFT JOIN properties p ON a.id = p.agent_id
    LEFT JOIN inquiries i ON a.id = i.agent_id
    WHERE a.id = p_agent_id;
END //

CREATE PROCEDURE sp_get_admin_dashboard()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM properties) AS total_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'active') AS active_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'inactive') AS inactive_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'draft') AS draft_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'pending') AS pending_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'sold') AS sold_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'rented') AS rented_properties,
        (SELECT COUNT(*) FROM agents) AS total_agents,
        (SELECT COUNT(*) FROM agents WHERE status = 'active') AS active_agents,
        (SELECT COUNT(*) FROM agents WHERE status = 'pending') AS pending_agents,
        (SELECT COUNT(*) FROM inquiries) AS total_inquiries,
        (SELECT COUNT(*) FROM inquiries WHERE status = 'new') AS new_inquiries,
        (SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = CURDATE()) AS today_inquiries;
END //

DELIMITER ;
