# iProply - Real Estate Management System

A complete, modern, and scalable Real Estate Web Application built with Core PHP (no frameworks) and MySQL database. Designed for the US-based real estate market with a focus on clean, premium UI/UX, mobile responsiveness, and easy navigation.

## Features

### Three User Roles
- **Admin**: Full system control, manage agents, approve properties, view all inquiries, site settings
- **Agent**: Add/edit/delete properties, manage inquiries, track performance
- **Guest/User**: Browse properties, advanced search, view details, send inquiries

### Key Features
- **Property Management**: Add, edit, delete properties with multiple image uploads
- **Advanced Search**: Filter by location, price, property type, bedrooms, bathrooms
- **Inquiry System**: Direct email notifications to agents with dashboard tracking
- **Responsive Design**: Mobile-first, works on all devices
- **SEO Friendly**: Clean URLs, meta tags, optimized structure
- **Secure**: PDO prepared statements, CSRF protection, password hashing

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for pretty URLs)

## Installation

### Step 1: Download and Extract
Download the application and extract it to your web server directory (e.g., `htdocs/iproply` or `www/iproply`).

### Step 2: Create Database
1. Create a new MySQL database (e.g., `iproply_db`)
2. Import the database schema from `database.sql`

```bash
mysql -u username -p iproply_db < database.sql
Or use phpMyAdmin to import the SQL file.
Step 3: Configure Database
Edit config/config.php and update the database credentials:
php
Copy
define('DB_HOST', 'localhost');
define('DB_NAME', 'iproply_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
Step 4: Configure Application URL
Update the application URL in config/config.php:
php
Copy
define('APP_URL', 'http://localhost/iproply'); // Change to your domain
Step 5: Set Permissions
Ensure the following directories are writable by the web server:
bash
Copy
chmod 755 assets/uploads/
chmod 755 assets/uploads/properties/
chmod 755 assets/uploads/agents/
chmod 755 assets/uploads/thumbnails/
Step 6: Email Configuration (Optional)
To enable email notifications, configure SMTP settings in config/config.php:
php
Copy
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
Default Login Credentials
Admin Login
URL: http://your-domain.com/admin/login.php
Username: admin
Password: admin123
Important: Change the default password immediately after first login!
Agent Login
URL: http://your-domain.com/agent/login.php
Sample agents are pre-created with username/password combinations
Folder Structure
plain
Copy
iproply/
├── admin/              # Admin panel files
├── agent/              # Agent panel files
├── assets/             # CSS, JS, images, uploads
│   ├── css/
│   ├── js/
│   └── uploads/
├── config/             # Configuration files
├── includes/           # PHP classes and functions
├── partials/           # Header, footer templates
├── database.sql        # Database schema
├── index.php          # Homepage
├── listings.php       # Property listings
├── property.php       # Property detail page
└── README.md          # This file
Usage Guide
For Administrators
Login to the admin panel
Manage agents (approve, suspend, delete)
Approve property listings before they go live
View all inquiries across the platform
Configure site settings
For Agents
Login to the agent dashboard
Add new properties with images and details
Manage your property listings
Respond to inquiries from potential buyers
Track your performance statistics
For Visitors
Browse properties on the homepage
Use the search bar or filter options
View detailed property information
Contact agents through the inquiry form
No registration required
Security Features
PDO Prepared Statements: Prevents SQL injection
CSRF Protection: All forms include CSRF tokens
Password Hashing: Uses bcrypt for secure password storage
Input Sanitization: All user inputs are sanitized
File Upload Validation: Type and size checks for images
Session Security: Secure session handling
Customization
Changing Colors
Edit the CSS variables in assets/css/style.css:
css
Copy
:root {
    --primary: #1e3b5a;        /* Main brand color */
    --primary-light: #2c5282;  /* Hover state */
    --secondary: #f5f5f5;      /* Background */
    /* ... */
}
Adding Pages
Create a new PHP file in the root directory
Include the config and required classes
Include header and footer partials
Troubleshooting
404 Errors
Ensure mod_rewrite is enabled and .htaccess file exists in the root directory.
Image Upload Issues
Check directory permissions (755)
Verify PHP upload_max_filesize setting
Check post_max_size in php.ini
Database Connection Errors
Verify database credentials in config/config.php
Ensure MySQL server is running
Check if database exists
Email Not Sending
Verify SMTP settings
Check if PHP mail() function is enabled
For Gmail, use App Password instead of regular password
License
This project is open source and available under the MIT License.
Support
For support, please contact: support@iproply.com
Credits
Built with Core PHP and MySQL
UI inspired by modern real estate platforms
Icons by Font Awesome
Fonts by Google Fonts
Version: 1.0.0
Last Updated: March 2026