-- =============================================================================
-- 008_trigger_property_views_tenant.sql
-- Writes tenant_id into property_analytics when logging daily view increments.
-- Requires: properties.tenant_id (003) AND property_analytics.tenant_id (004)
-- Requires: property_analytics table exists.
-- Execute with mysql client (uses DELIMITER).
-- =============================================================================

DROP TRIGGER IF EXISTS trg_increment_property_views;

DELIMITER //

CREATE TRIGGER trg_increment_property_views
AFTER UPDATE ON properties
FOR EACH ROW
BEGIN
    IF OLD.view_count <> NEW.view_count THEN
        INSERT INTO property_analytics (property_id, tenant_id, view_date, view_count)
        VALUES (NEW.id, NEW.tenant_id, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE view_count = property_analytics.view_count + 1;
    END IF;
END//

DELIMITER ;
