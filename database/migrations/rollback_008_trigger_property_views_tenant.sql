-- Restores analytics trigger compatible with legacy property_analytics (no tenant_id column).
-- If tenant_id exists on property_analytics, MySQL fills DEFAULT / implicit values only when column NOT NULL -
-- Prefer running this rollback only AFTER rollback_004 removed tenant_id, or edit trigger to omit tenant_id.

DROP TRIGGER IF EXISTS trg_increment_property_views;

DELIMITER //

CREATE TRIGGER trg_increment_property_views
AFTER UPDATE ON properties
FOR EACH ROW
BEGIN
    IF OLD.view_count <> NEW.view_count THEN
        INSERT INTO property_analytics (property_id, view_date, view_count)
        VALUES (NEW.id, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE view_count = property_analytics.view_count + 1;
    END IF;
END//

DELIMITER ;
