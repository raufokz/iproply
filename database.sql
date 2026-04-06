-- Real Estate Management System - Database Schema
-- Created for US-based real estate market
-- Core PHP Application with MySQL

-- Drop tables if they exist (for clean installation)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS property_types;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS agents;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS site_settings;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- TABLE: site_settings
-- Stores website configuration and settings
-- ============================================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL DEFAULT 'iProply',
    site_tagline VARCHAR(500) DEFAULT 'Find Your Dream Home Today',
    site_email VARCHAR(255) DEFAULT 'info@iproply.com',
    site_phone VARCHAR(50) DEFAULT '(555) 123-4567',
    site_address TEXT,
    site_logo VARCHAR(255),
    smtp_host VARCHAR(255),
    smtp_port INT DEFAULT 587,
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    smtp_encryption VARCHAR(20) DEFAULT 'tls',
    facebook_url VARCHAR(500),
    twitter_url VARCHAR(500),
    instagram_url VARCHAR(500),
    linkedin_url VARCHAR(500),
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default site settings
INSERT INTO site_settings (site_name, site_tagline, site_email, site_phone, site_address, meta_title, meta_description) VALUES
('iProply', 'Find Your Dream Home Today', 'info@iproply.com', '(555) 123-4567', '123 Real Estate Ave, New York, NY 10001', 'iProply - Find Your Dream Home', 'Discover thousands of properties for sale and rent across the US. Your perfect home is just a click away.');

-- ============================================
-- TABLE: admins
-- Administrator accounts with full system access
-- ============================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(50),
    avatar VARCHAR(255),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123 - change immediately after installation)
INSERT INTO admins (username, email, password, first_name, last_name, role, status) VALUES
('admin', 'admin@iproply.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'super_admin', 'active');

-- ============================================
-- TABLE: agents
-- Real estate agent accounts
-- ============================================
CREATE TABLE agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    mobile VARCHAR(50),
    license_number VARCHAR(100),
    bio TEXT,
    avatar VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(50),
    zip_code VARCHAR(20),
    website VARCHAR(255),
    facebook VARCHAR(255),
    twitter VARCHAR(255),
    instagram VARCHAR(255),
    linkedin VARCHAR(255),
    years_experience INT DEFAULT 0,
    specialties TEXT,
    status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'pending',
    is_featured TINYINT(1) DEFAULT 0,
    email_notifications TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample agents
INSERT INTO agents (username, email, password, first_name, last_name, phone, license_number, bio, city, state, years_experience, status, is_featured) VALUES
('sarahjohnson', 'sarah.j@iproply.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', '(555) 234-5678', 'RE-12345', 'Experienced real estate professional with over 10 years in residential sales. Specializing in luxury homes and first-time buyers.', 'New York', 'NY', 10, 'active', 1),
('michaelchen', 'michael.c@iproply.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Chen', '(555) 345-6789', 'RE-23456', 'Commercial and residential property specialist. Expert in market analysis and investment properties.', 'Los Angeles', 'CA', 8, 'active', 1),
('emilydavis', 'emily.d@iproply.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily', 'Davis', '(555) 456-7890', 'RE-34567', 'Dedicated to helping families find their perfect home. Expert in suburban neighborhoods and school districts.', 'Chicago', 'IL', 5, 'active', 0),
('jameswilson', 'james.w@iproply.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Wilson', '(555) 567-8901', 'RE-45678', 'Luxury property specialist with a focus on waterfront estates and high-end condominiums.', 'Miami', 'FL', 15, 'active', 1);

-- ============================================
-- TABLE: categories
-- Property categories (Residential, Commercial, etc.)
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, slug, description, display_order, status) VALUES
('Residential', 'residential', 'Homes, apartments, and residential properties', 1, 'active'),
('Commercial', 'commercial', 'Office spaces, retail, and commercial buildings', 2, 'active'),
('Industrial', 'industrial', 'Warehouses, factories, and industrial properties', 3, 'active'),
('Land', 'land', 'Vacant land and lots for development', 4, 'active'),
('Luxury', 'luxury', 'High-end and luxury properties', 5, 'active');

-- ============================================
-- TABLE: property_types
-- Types of properties (House, Apartment, Condo, etc.)
-- ============================================
CREATE TABLE property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert property types
INSERT INTO property_types (category_id, name, slug, description, display_order, status) VALUES
(1, 'House', 'house', 'Single-family detached homes', 1, 'active'),
(1, 'Apartment', 'apartment', 'Apartment units in multi-family buildings', 2, 'active'),
(1, 'Condo', 'condo', 'Condominium units', 3, 'active'),
(1, 'Townhouse', 'townhouse', 'Townhouse and row house properties', 4, 'active'),
(1, 'Duplex', 'duplex', 'Two-unit residential buildings', 5, 'active'),
(2, 'Office', 'office', 'Office spaces and buildings', 6, 'active'),
(2, 'Retail', 'retail', 'Retail stores and shopping spaces', 7, 'active'),
(2, 'Restaurant', 'restaurant', 'Restaurant and food service spaces', 8, 'active'),
(3, 'Warehouse', 'warehouse', 'Warehouses and storage facilities', 9, 'active'),
(4, 'Vacant Land', 'vacant-land', 'Undeveloped land and lots', 10, 'active');

-- ============================================
-- TABLE: properties
-- Main property listings table
-- ============================================
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    category_id INT,
    property_type_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description LONGTEXT,
    short_description TEXT,
    price DECIMAL(15, 2) NOT NULL,
    price_type ENUM('fixed', 'negotiable', 'auction') DEFAULT 'fixed',
    status ENUM('sale', 'rent', 'sold', 'pending') DEFAULT 'sale',
    property_status ENUM('active', 'inactive', 'pending', 'featured', 'sold', 'rented') DEFAULT 'pending',
    bedrooms INT DEFAULT 0,
    bathrooms DECIMAL(3, 1) DEFAULT 0,
    area_sqft DECIMAL(10, 2) DEFAULT 0,
    lot_size DECIMAL(10, 2) DEFAULT 0,
    year_built INT,
    parking_spaces INT DEFAULT 0,
    garage_spaces INT DEFAULT 0,
    floors INT DEFAULT 1,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(50) DEFAULT 'USA',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    map_address TEXT,
    features LONGTEXT,
    amenities LONGTEXT,
    nearby_places LONGTEXT,
    virtual_tour_url VARCHAR(500),
    video_url VARCHAR(500),
    floor_plan VARCHAR(255),
    featured_image VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_premium TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    inquiry_count INT DEFAULT 0,
    admin_notes TEXT,
    approved_by INT,
    approved_at DATETIME,
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_price (price),
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_state (state),
    INDEX idx_property_status (property_status),
    INDEX idx_featured (is_featured),
    FULLTEXT INDEX idx_search (title, description, address, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample properties
INSERT INTO properties (agent_id, category_id, property_type_id, title, slug, description, short_description, price, status, property_status, bedrooms, bathrooms, area_sqft, address, city, state, zip_code, is_featured, features, amenities) VALUES
(1, 1, 1, 'Beautiful Modern House in Suburban Area', 'beautiful-modern-house-suburban', 'This stunning modern house features an open floor plan with high ceilings, large windows for natural light, and premium finishes throughout. The gourmet kitchen includes granite countertops, stainless steel appliances, and a large island. The master suite offers a walk-in closet and luxurious bathroom with soaking tub and separate shower.', 'Stunning modern home with open floor plan, gourmet kitchen, and luxurious master suite in a quiet suburban neighborhood.', 750000.00, 'sale', 'active', 4, 3.5, 3200, '123 Maple Street', 'Austin', 'TX', '78701', 1, '["Open Floor Plan", "High Ceilings", "Walk-in Closets", "Fireplace", "Hardwood Floors"]', '["Air Conditioning", "Heating", "Garage", "Garden", "Patio"]'),
(2, 1, 3, 'Luxury Downtown Condo with City Views', 'luxury-downtown-condo-city-views', 'Experience urban living at its finest in this luxurious downtown condo. Floor-to-ceiling windows offer breathtaking city views. The building features a rooftop pool, fitness center, 24/7 concierge, and secure parking. Walking distance to restaurants, shopping, and entertainment.', 'Luxury downtown condo with stunning city views, rooftop pool, and premium amenities in the heart of the city.', 1250000.00, 'sale', 'active', 2, 2.0, 1800, '456 Skyline Avenue, Unit 2501', 'Los Angeles', 'CA', '90017', 1, '["City Views", "Floor-to-Ceiling Windows", "Balcony", "Walk-in Closet", "Gourmet Kitchen"]', '["Gym", "Pool", "Concierge", "Security", "Parking", "Elevator"]'),
(3, 1, 2, 'Spacious Family Apartment Near Schools', 'spacious-family-apartment-near-schools', 'Perfect for families, this spacious apartment offers 3 bedrooms, 2 bathrooms, and plenty of living space. Located in a top-rated school district with parks and playgrounds nearby. The complex includes a community pool, playground, and on-site laundry facilities.', 'Family-friendly apartment in excellent school district with community amenities and nearby parks.', 2800.00, 'rent', 'active', 3, 2.0, 1450, '789 Oakwood Drive, Apt 3B', 'Chicago', 'IL', '60614', 0, '["Spacious Living Room", "Eat-in Kitchen", "Storage Space", "Pet Friendly"]', '["Community Pool", "Playground", "On-site Laundry", "Parking"]'),
(4, 5, 1, 'Waterfront Estate with Private Dock', 'waterfront-estate-private-dock', 'Magnificent waterfront estate on 2 acres with private dock and panoramic water views. This 6-bedroom, 5-bathroom home features a gourmet kitchen, home theater, wine cellar, and infinity pool. The master suite includes a private balcony and spa-like bathroom.', 'Magnificent waterfront estate with private dock, infinity pool, and panoramic views on 2 acres.', 3500000.00, 'sale', 'active', 6, 5.0, 6500, '1010 Ocean Drive', 'Miami Beach', 'FL', '33139', 1, '["Waterfront", "Private Dock", "Infinity Pool", "Home Theater", "Wine Cellar", "Guest House"]', '["Pool", "Spa", "Boat Dock", "Security System", "Smart Home", "Outdoor Kitchen"]'),
(1, 1, 4, 'Charming Townhouse in Historic District', 'charming-townhouse-historic-district', 'Beautifully renovated townhouse in the historic district featuring original hardwood floors, exposed brick walls, and modern updates. Walking distance to shops, restaurants, and public transportation. Private courtyard perfect for entertaining.', 'Historic townhouse with original character, modern updates, and private courtyard in walkable district.', 485000.00, 'sale', 'active', 3, 2.5, 2100, '222 Heritage Lane', 'Boston', 'MA', '02127', 0, '["Historic Features", "Exposed Brick", "Hardwood Floors", "Private Courtyard", "Fireplace"]', '["Central Heating", "Updated Kitchen", "Walk-in Closet"]'),
(2, 2, 6, 'Prime Office Space in Business District', 'prime-office-space-business-district', 'Professional office space in the heart of the business district. Features reception area, 5 private offices, conference room, and break room. Building amenities include 24/7 access, security, and parking. Close to public transportation.', 'Professional office space with multiple private offices and conference room in prime business location.', 4500.00, 'rent', 'active', 0, 2.0, 2500, '300 Commerce Street, Suite 400', 'Seattle', 'WA', '98101', 0, '["Reception Area", "Private Offices", "Conference Room", "Break Room", "High-speed Internet"]', '["24/7 Access", "Security", "Parking", "Elevator", "HVAC"]');

-- ============================================
-- TABLE: property_images
-- Multiple images per property
-- ============================================
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    caption VARCHAR(255),
    display_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: inquiries
-- Property inquiries from potential buyers/renters
-- ============================================
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    agent_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'responded', 'closed', 'spam') DEFAULT 'new',
    ip_address VARCHAR(45),
    user_agent TEXT,
    responded_at DATETIME,
    responded_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES agents(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_agent (agent_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VIEWS for common queries
-- ============================================

-- View: Active properties with agent info
CREATE OR REPLACE VIEW vw_active_properties AS
SELECT 
    p.*,
    CONCAT(a.first_name, ' ', a.last_name) as agent_name,
    a.email as agent_email,
    a.phone as agent_phone,
    a.avatar as agent_avatar,
    pt.name as property_type_name,
    c.name as category_name
FROM properties p
LEFT JOIN agents a ON p.agent_id = a.id
LEFT JOIN property_types pt ON p.property_type_id = pt.id
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.property_status = 'active';

-- View: Featured properties
CREATE OR REPLACE VIEW vw_featured_properties AS
SELECT 
    p.*,
    CONCAT(a.first_name, ' ', a.last_name) as agent_name,
    a.email as agent_email,
    a.phone as agent_phone,
    a.avatar as agent_avatar,
    pt.name as property_type_name
FROM properties p
LEFT JOIN agents a ON p.agent_id = a.id
LEFT JOIN property_types pt ON p.property_type_id = pt.id
WHERE p.is_featured = 1 AND p.property_status = 'active'
ORDER BY p.created_at DESC;

-- View: Agent statistics
CREATE OR REPLACE VIEW vw_agent_stats AS
SELECT 
    a.id,
    CONCAT(a.first_name, ' ', a.last_name) as agent_name,
    a.email,
    a.phone,
    a.status,
    COUNT(DISTINCT p.id) as total_properties,
    COUNT(DISTINCT CASE WHEN p.property_status = 'active' THEN p.id END) as active_properties,
    COUNT(DISTINCT CASE WHEN p.is_featured = 1 THEN p.id END) as featured_properties,
    COUNT(DISTINCT i.id) as total_inquiries,
    COUNT(DISTINCT CASE WHEN i.status = 'new' THEN i.id END) as new_inquiries
FROM agents a
LEFT JOIN properties p ON a.id = p.agent_id
LEFT JOIN inquiries i ON a.id = i.agent_id
GROUP BY a.id;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER //

-- Procedure: Search properties with filters
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
BEGIN
    SELECT 
        p.*,
        CONCAT(a.first_name, ' ', a.last_name) as agent_name,
        a.phone as agent_phone,
        a.avatar as agent_avatar,
        pt.name as property_type_name,
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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

-- Procedure: Get property details with images
CREATE PROCEDURE sp_get_property_details(IN p_property_id INT)
BEGIN
    SELECT 
        p.*,
        CONCAT(a.first_name, ' ', a.last_name) as agent_name,
        a.email as agent_email,
        a.phone as agent_phone,
        a.mobile as agent_mobile,
        a.avatar as agent_avatar,
        a.bio as agent_bio,
        a.license_number as agent_license,
        a.years_experience as agent_experience,
        pt.name as property_type_name,
        c.name as category_name
    FROM properties p
    LEFT JOIN agents a ON p.agent_id = a.id
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = p_property_id;
    
    SELECT * FROM property_images WHERE property_id = p_property_id ORDER BY is_primary DESC, display_order ASC;
END //

-- Procedure: Get agent dashboard stats
CREATE PROCEDURE sp_get_agent_dashboard(IN p_agent_id INT)
BEGIN
    SELECT 
        COUNT(DISTINCT p.id) as total_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'active' THEN p.id END) as active_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'pending' THEN p.id END) as pending_properties,
        COUNT(DISTINCT CASE WHEN p.property_status = 'sold' THEN p.id END) as sold_properties,
        COUNT(DISTINCT i.id) as total_inquiries,
        COUNT(DISTINCT CASE WHEN i.status = 'new' THEN i.id END) as new_inquiries,
        COUNT(DISTINCT CASE WHEN i.status = 'responded' THEN i.id END) as responded_inquiries
    FROM agents a
    LEFT JOIN properties p ON a.id = p.agent_id
    LEFT JOIN inquiries i ON a.id = i.agent_id
    WHERE a.id = p_agent_id;
END //

-- Procedure: Get admin dashboard stats
CREATE PROCEDURE sp_get_admin_dashboard()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM properties) as total_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'active') as active_properties,
        (SELECT COUNT(*) FROM properties WHERE property_status = 'pending') as pending_properties,
        (SELECT COUNT(*) FROM agents) as total_agents,
        (SELECT COUNT(*) FROM agents WHERE status = 'active') as active_agents,
        (SELECT COUNT(*) FROM agents WHERE status = 'pending') as pending_agents,
        (SELECT COUNT(*) FROM inquiries) as total_inquiries,
        (SELECT COUNT(*) FROM inquiries WHERE status = 'new') as new_inquiries,
        (SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = CURDATE()) as today_inquiries;
END //

DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

-- Trigger: Update inquiry count when new inquiry is added
CREATE TRIGGER trg_after_inquiry_insert 
AFTER INSERT ON inquiries
FOR EACH ROW
BEGIN
    UPDATE properties 
    SET inquiry_count = inquiry_count + 1 
    WHERE id = NEW.property_id;
END //

-- Trigger: Update view count trigger (called from application)
CREATE TRIGGER trg_increment_property_views
AFTER UPDATE ON properties
FOR EACH ROW
BEGIN
    IF OLD.view_count != NEW.view_count THEN
        INSERT INTO property_analytics (property_id, view_date, view_count)
        VALUES (NEW.id, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE view_count = view_count + 1;
    END IF;
END //

DELIMITER ;

-- ============================================
-- ANALYTICS TABLE (optional)
-- ============================================
CREATE TABLE property_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    view_date DATE NOT NULL,
    view_count INT DEFAULT 1,
    inquiry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_property_date (property_id, view_date),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

-- Instructions:
-- 1. Create a MySQL database (e.g., 'realty_db')
-- 2. Run this SQL file: mysql -u username -p realty_db < database.sql
-- 3. Update config/database.php with your database credentials
-- 4. Default admin login: admin / admin123 (CHANGE IMMEDIATELY!)
-- 5. Sample agents login: username / password (all use 'password' as password for demo)