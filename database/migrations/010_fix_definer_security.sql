-- =============================================================================
-- 010_fix_definer_security.sql
-- Recreate views and routines without environment-specific DEFINER metadata.
-- =============================================================================

SET NAMES utf8mb4;

-- Views imported from hosted databases can keep the hosted account as DEFINER.
-- SQL SECURITY INVOKER makes them run with the connected application user.
CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_active_properties AS
SELECT
    p.*,
    CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
    a.email AS agent_email,
    a.phone AS agent_phone,
    a.avatar AS agent_avatar,
    pt.name AS property_type_name,
    c.name AS category_name
FROM properties p
LEFT JOIN agents a ON p.agent_id = a.id
LEFT JOIN property_types pt ON p.property_type_id = pt.id
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.property_status = 'active';

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_featured_properties AS
SELECT
    p.*,
    CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
    a.email AS agent_email,
    a.phone AS agent_phone,
    a.avatar AS agent_avatar,
    pt.name AS property_type_name
FROM properties p
LEFT JOIN agents a ON p.agent_id = a.id
LEFT JOIN property_types pt ON p.property_type_id = pt.id
WHERE p.is_featured = 1 AND p.property_status = 'active'
ORDER BY p.created_at DESC;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_agent_stats AS
SELECT
    a.id,
    CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
    a.email,
    a.phone,
    a.status,
    COUNT(DISTINCT p.id) AS total_properties,
    COUNT(DISTINCT CASE WHEN p.property_status = 'active' THEN p.id END) AS active_properties,
    COUNT(DISTINCT CASE WHEN p.is_featured = 1 THEN p.id END) AS featured_properties,
    COUNT(DISTINCT i.id) AS total_inquiries,
    COUNT(DISTINCT CASE WHEN i.status = 'new' THEN i.id END) AS new_inquiries
FROM agents a
LEFT JOIN properties p ON a.id = p.agent_id
LEFT JOIN inquiries i ON a.id = i.agent_id
GROUP BY a.id;

DROP PROCEDURE IF EXISTS sp_search_properties;
DROP PROCEDURE IF EXISTS sp_get_property_details;
DROP PROCEDURE IF EXISTS sp_get_agent_dashboard;
DROP PROCEDURE IF EXISTS sp_get_admin_dashboard;

DELIMITER //

CREATE PROCEDURE sp_search_properties(
    IN p_keyword VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(50),
    IN p_property_type INT,
    IN p_status VARCHAR(20),
    IN p_min_price DECIMAL(15,2),
    IN p_max_price DECIMAL(15,2),
    IN p_bedrooms INT,
    IN p_bathrooms DECIMAL(3,1),
    IN p_limit INT,
    IN p_offset INT
)
SQL SECURITY INVOKER
BEGIN
    SELECT
        p.*,
        CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
        a.phone AS agent_phone,
        a.avatar AS agent_avatar,
        pt.name AS property_type_name,
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
    FROM properties p
    LEFT JOIN agents a ON p.agent_id = a.id
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    WHERE p.property_status = 'active'
        AND (p_keyword IS NULL OR MATCH(p.title, p.description, p.address, p.city) AGAINST(p_keyword IN NATURAL LANGUAGE MODE))
        AND (p_city IS NULL OR p.city LIKE CONCAT('%', p_city, '%'))
        AND (p_state IS NULL OR p.state = p_state)
        AND (p_property_type IS NULL OR p.property_type_id = p_property_type)
        AND (p_status IS NULL OR p.status = p_status)
        AND (p_min_price IS NULL OR p.price >= p_min_price)
        AND (p_max_price IS NULL OR p.price <= p_max_price)
        AND (p_bedrooms IS NULL OR p.bedrooms >= p_bedrooms)
        AND (p_bathrooms IS NULL OR p.bathrooms >= p_bathrooms)
    ORDER BY p.is_featured DESC, p.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END //

CREATE PROCEDURE sp_get_property_details(IN p_property_id INT)
SQL SECURITY INVOKER
BEGIN
    SELECT
        p.*,
        CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
        a.email AS agent_email,
        a.phone AS agent_phone,
        a.mobile AS agent_mobile,
        a.avatar AS agent_avatar,
        a.bio AS agent_bio,
        a.license_number AS agent_license,
        a.years_experience AS agent_experience,
        pt.name AS property_type_name,
        c.name AS category_name
    FROM properties p
    LEFT JOIN agents a ON p.agent_id = a.id
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = p_property_id;

    SELECT * FROM property_images WHERE property_id = p_property_id ORDER BY is_primary DESC, display_order ASC;
END //

CREATE PROCEDURE sp_get_agent_dashboard(IN p_agent_id INT)
SQL SECURITY INVOKER
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
SQL SECURITY INVOKER
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
