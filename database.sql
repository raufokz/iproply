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
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS agents;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS blogs;
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
-- TABLE: blogs
-- Blog posts managed by admin users
-- ============================================
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at DATETIME NULL,
    created_by INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_blog_status (status),
    INDEX idx_blog_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: pages
-- CMS-style content pages (Terms, Privacy, Careers, etc.)
-- Managed via Admin Panel (CRUD + publish/unpublish + footer nav)
-- ============================================
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at DATETIME NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    show_in_footer TINYINT(1) NOT NULL DEFAULT 0,
    footer_section VARCHAR(50) NULL,
    footer_order INT NOT NULL DEFAULT 0,
    created_by INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: newsletter_subscribers
-- Footer newsletter signup storage
-- ============================================
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert standard CMS pages (all published by default)
INSERT INTO pages (title, slug, content, status, published_at, meta_title, meta_description, show_in_footer, footer_section, footer_order, created_by, updated_by) VALUES
('About iProply', 'about',
 'H2: A trusted real estate platform\n- iProply helps buyers and renters discover properties with confidence\n- We support agents with tools to manage listings and respond quickly\n\nH2: What you can do on iProply\n- Browse listings for sale and rent\n- Filter by location, price, and property type\n- Contact agents directly from listing pages\n\nH2: Our commitment\n- Clear, consistent information\n- Professional presentation across the site\n- Fair housing and equal opportunity principles',
 'published', NOW(),
 'About iProply | iProply', 'Learn about iProply, a premium platform connecting buyers, renters, and agents with a focus on clarity, trust, and usability.',
 0, 'about', 0, 1, 1),
('Become an Agent', 'become-agent',
 'H2: Grow your business with iProply\n- Premium listing exposure across the iProply marketplace\n- Lead and inquiry management tools built for speed\n- A polished agent profile to showcase your expertise\n\nH2: What you get\n- Centralized inbox for buyer and renter inquiries\n- Listing tools to publish, update, and manage your inventory\n- Performance insights to help you improve conversion\n\nH2: Who this is for\n- Licensed agents and broker associates\n- Teams looking to scale lead flow and operations\n- Independent agents building a premium brand\n\nH2: How to get started\n- Create your agent account and complete your profile\n- Add your first listing (sale or rent)\n- Respond quickly to inquiries to improve ranking and trust',
 'published', NOW(),
 'Become an Agent | iProply', 'Join iProply as a real estate agent and grow your business with modern tools, premium exposure, and streamlined inquiry management.',
 1, 'join', 10, 1, 1),
('Partner With Us', 'partners',
 'H2: Partnerships that create better home experiences\n- Builders and developers: showcase new communities and pre-construction inventory\n- Lenders: connect with qualified borrowers at the right moment\n- Home services: movers, staging, inspections, and repairs\n\nH2: What we offer\n- Sponsored placements and featured packages\n- Co-marketing opportunities and content partnerships\n- Dedicated support for onboarding and measurement\n\nH2: Get in touch\n- Use our Contact page to request a partnership conversation\n- Include your company name, service area, and partnership goals',
 'published', NOW(),
 'Partner With iProply | iProply', 'Partner with iProply to reach motivated homebuyers and renters through sponsorships, co-marketing, and featured placements.',
 1, 'join', 20, 1, 1),
('Careers', 'careers',
 'H2: Work with purpose\n- Help people find homes with confidence\n- Build products that make real estate simpler and more transparent\n\nH2: How we hire\n- Application review\n- Role-specific interview loop\n- Team conversation and references\n\nH2: What we value\n- Customer obsession and clear communication\n- Ownership and continuous improvement\n- Inclusion and respect\n\nH2: Interested?\n- Reach out via the Contact page with your resume and the role you are pursuing',
 'published', NOW(),
 'Careers | iProply', 'Explore careers at iProply and help build a modern real estate experience with a customer-first, high-trust team.',
 1, 'join', 30, 1, 1),
('Why iProply?', 'why-iproply',
 'H2: A premium real estate experience\n- Curated listings with clear details and high-quality media\n- Fast search, filters, and saved favorites\n- Trusted agents and responsive inquiry workflows\n\nH2: Built for buyers, renters, and agents\n- Buyers and renters: find the right home faster\n- Agents: manage listings and messages in one place\n- Admin tools: keep content and quality consistent\n\nH2: Our standards\n- Transparency in listing information\n- Fair housing commitment\n- Professional design and accessibility-first UX',
 'published', NOW(),
 'Why iProply | iProply', 'Learn why iProply is a modern, premium real estate platform for buyers, renters, and agents with a focus on transparency and usability.',
 1, 'about', 10, 1, 1),
('Our Story', 'our-story',
 'H2: Our mission\n- Make real estate simpler, more transparent, and more human\n\nH2: What we do\n- Connect buyers and renters with quality listings\n- Empower agents with modern tools and insights\n- Maintain a consistent standard of presentation across the platform\n\nH2: Where we’re headed\n- Better search and richer listing details\n- Faster agent-to-client communication\n- More guidance content for every step of the journey',
 'published', NOW(),
 'Our Story | iProply', 'Read the iProply story and our mission to modernize real estate with tools that improve trust, clarity, and outcomes.',
 1, 'about', 20, 1, 1),
('Community Impact', 'community-impact',
 'H2: Investing in communities\n- Supporting housing stability initiatives\n- Promoting education and financial literacy\n- Encouraging responsible development and local engagement\n\nH2: How we contribute\n- Volunteer programs and local partnerships\n- Donation matching during seasonal campaigns\n- Highlighting community-focused listings and resources',
 'published', NOW(),
 'Community Impact | iProply', 'See how iProply supports communities through housing initiatives, partnerships, and responsible real estate practices.',
 1, 'about', 30, 1, 1),
('Diversity & Inclusion', 'inclusion',
 'H2: Our commitment\n- We support equal housing opportunity and fair housing practices\n- We respect and celebrate the diversity of the communities we serve\n\nH2: What inclusion means to us\n- Building products that work for everyone\n- Reducing bias through clear standards and transparent processes\n- Creating a welcoming environment for clients, agents, and partners',
 'published', NOW(),
 'Diversity & Inclusion | iProply', 'Learn about iProply’s commitment to diversity, inclusion, and fair housing across our platform and community.',
 1, 'about', 40, 1, 1),
('Press & Media', 'press',
 'H2: Press inquiries\n- For press and media requests, please contact our team via the Contact page\n\nH2: Brand assets\n- Logos and brand guidelines are available upon request\n\nH2: Company overview\n- iProply is a premium real estate platform focused on clarity, trust, and usability for buyers, renters, and agents',
 'published', NOW(),
 'Press & Media | iProply', 'Press and media resources for iProply, including inquiries and brand assets.',
 1, 'about', 50, 1, 1),
('Help Center', 'help-center',
 'H2: Get help fast\n- Searching and filtering listings\n- Managing favorites and saved searches\n- Contacting an agent and responding to messages\n\nH2: Agents\n- Updating your profile\n- Publishing and managing listings\n- Responding to inquiries effectively\n\nH2: Still need help?\n- Reach out through the Contact page and we’ll respond as quickly as possible',
 'published', NOW(),
 'Help Center | iProply', 'Find help with searching listings, contacting agents, managing your account, and using iProply’s tools.',
 1, 'resources', 20, 1, 1),
('Market Reports', 'market-reports',
 'H2: Market insights\n- Trend summaries by region\n- Pricing and inventory signals\n- Rental demand snapshots\n\nH2: How to use market reports\n- Compare neighborhoods and property types\n- Track pricing changes over time\n- Use insights to plan offers and timing\n\nH2: Disclaimer\n- Market data is provided for informational purposes and may not reflect real-time conditions',
 'published', NOW(),
 'Market Reports | iProply', 'Market reports and real estate insights to help buyers, renters, and sellers understand trends and pricing signals.',
 1, 'resources', 30, 1, 1),
('Mortgage Calculator', 'mortgage-calculator',
 'H2: Estimate your monthly payment\n- Enter home price, down payment, interest rate, and term\n- Consider taxes, insurance, and HOA where applicable\n\nH2: Tips\n- Use conservative assumptions for taxes and insurance\n- Compare multiple rates and terms\n- Keep a buffer for maintenance and closing costs\n\nH2: Disclaimer\n- Estimates are for informational purposes and are not a loan offer or approval',
 'published', NOW(),
 'Mortgage Calculator | iProply', 'Use a mortgage calculator to estimate monthly payments and plan your home budget with confidence.',
 1, 'resources', 40, 1, 1),
('Advertise', 'advertise',
 'H2: Reach motivated home shoppers\n- Featured placements for listings and partners\n- Sponsored content opportunities\n- Brand visibility across high-intent discovery surfaces\n\nH2: Options\n- Spotlight listings\n- Partner directory placement\n- Seasonal campaigns\n\nH2: Get started\n- Contact us with your goals, target market, and budget range',
 'published', NOW(),
 'Advertise | iProply', 'Advertising options for real estate brands and partners to reach motivated buyers and renters on iProply.',
 1, 'resources', 50, 1, 1),
('Terms of Use', 'terms-of-use',
 'H2: Overview\n- These Terms of Use govern your access to and use of the iProply website and services\n- By using the site, you agree to these terms\n\nH2: Use of the site\n- Do not misuse the platform, attempt unauthorized access, or disrupt service\n- You are responsible for activity under your account\n\nH2: Listings and content\n- Listing information is provided by third parties and may change\n- We do not guarantee accuracy, completeness, or availability\n\nH2: Disclaimers\n- The site is provided “as is” without warranties\n- To the fullest extent permitted by law, we disclaim liability for damages arising from use\n\nH2: Contact\n- Questions about these terms can be sent through the Contact page',
 'published', NOW(),
 'Terms of Use | iProply', 'Read iProply’s Terms of Use covering site access, acceptable use, listing information, and legal disclaimers.',
 0, 'legal', 10, 1, 1),
('Privacy Policy', 'privacy-policy',
 'H2: Overview\n- This Privacy Policy explains how iProply collects, uses, and shares information\n\nH2: Information we collect\n- Contact information you submit through forms\n- Account information for agents and administrators\n- Usage data to improve performance and reliability\n\nH2: How we use information\n- Provide and improve services\n- Respond to inquiries and support requests\n- Maintain security and prevent abuse\n\nH2: Sharing\n- We may share information with service providers who help operate the site\n- We do not sell personal information as part of normal operations\n\nH2: Your choices\n- You can request access, correction, or deletion via the Contact page\n\nH2: Contact\n- Privacy questions can be sent through the Contact page',
 'published', NOW(),
 'Privacy Policy | iProply', 'Read iProply’s Privacy Policy covering the information we collect, how we use it, and your choices.',
 0, 'legal', 20, 1, 1),
('Do Not Sell or Share My Personal Information', 'do-not-sell',
 'H2: Your privacy choices\n- You may request that we do not sell or share your personal information where applicable\n\nH2: How to submit a request\n- Use the Contact page and include:\n- Your name and email address\n- The request type: “Do Not Sell or Share”\n- Any additional details needed to verify your request\n\nH2: Verification\n- We may need to verify your identity before completing the request\n\nH2: Note\n- This page is provided for informational purposes and does not create legal rights beyond what is provided by applicable law',
 'published', NOW(),
 'Do Not Sell or Share My Personal Information | iProply', 'Submit a request regarding the sale or sharing of personal information, where applicable.',
 0, 'legal', 30, 1, 1),
('Fair Housing', 'fair-housing',
 'H2: Equal Housing Opportunity\n- iProply supports the Fair Housing Act and equal opportunity in housing\n\nH2: Prohibited discrimination\n- Housing-related discrimination may be prohibited based on protected characteristics under applicable law\n\nH2: Reporting concerns\n- If you believe a listing or interaction violates fair housing principles, contact us through the Contact page\n\nH2: Resources\n- For general fair housing information, you may also consult federal, state, and local fair housing agencies',
 'published', NOW(),
 'Fair Housing | iProply', 'iProply is committed to equal housing opportunity and Fair Housing Act principles.',
 0, 'legal', 40, 1, 1),
('Fair Housing Policy', 'fair-housing-policy',
 'H2: Policy statement\n- iProply is committed to fair housing and equal opportunity\n\nH2: Our approach\n- We maintain clear platform standards for listings\n- We encourage accurate and non-discriminatory listing language\n- We provide pathways to report concerns\n\nH2: Platform enforcement\n- Content that violates policy may be removed\n- Accounts may be restricted for repeated violations\n\nH2: Contact\n- Use the Contact page to report concerns or request information',
 'published', NOW(),
 'Fair Housing Policy | iProply', 'Read iProply’s Fair Housing Policy and our standards for listings, conduct, and enforcement.',
 0, 'legal', 50, 1, 1),
('Texas: Info About Brokerage Services', 'texas-ibs',
 'H2: Texas Information About Brokerage Services\n- This page provides a reference link for Texas consumers\n\nH2: Notice\n- Brokerage and consumer protection notices may be required for certain transactions\n- Please consult a licensed professional for guidance\n\nH2: Official resources\n- Texas Real Estate Commission (TREC): Consumer Protection Notice and related forms are available on the TREC website',
 'published', NOW(),
 'Texas Info About Brokerage Services | iProply', 'Texas consumer information about brokerage services and related notices.',
 0, 'legal', 60, 1, 1);

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
