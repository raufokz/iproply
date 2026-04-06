<?php
/**
 * iProply - Real Estate Management System
 * Main Configuration File
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Application Settings
define('APP_NAME', 'iProply');
define('APP_VERSION', '1.0.0');

// Auto-detect environment and set URL
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    define('APP_URL', 'http://localhost/iproply'); // Local development
    define('APP_ENV', 'development');
} else {
    define('APP_URL', 'https://iproply.com'); // Production
    define('APP_ENV', 'production');
}

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'u867057961_realty_db');
define('DB_USER', 'u867057961_iproply');
define('DB_PASS', 'Sales@SoftoSol77');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', '');

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', ''); // Your email
define('SMTP_PASSWORD', ''); // Your app password
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl
define('SMTP_FROM_EMAIL', 'noreply@iproply.com');
define('SMTP_FROM_NAME', 'iProply');

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');

// Image Settings
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('THUMBNAIL_WIDTH', 400);
define('THUMBNAIL_HEIGHT', 300);

// Pagination
define('ITEMS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Session Settings
define('SESSION_NAME', 'iproply_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Cache Settings
define('CACHE_ENABLED', false);
define('CACHE_PATH', BASE_PATH . '/cache/');
define('CACHE_TTL', 3600); // 1 hour

// reCAPTCHA (optional - for forms)
define('RECAPTCHA_ENABLED', false);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Google Maps API (optional - for maps)
define('GOOGLE_MAPS_API_KEY', '');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// Timezone
date_default_timezone_set('America/New_York');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Autoloader
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/includes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Helper function to get base URL
function base_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

// Helper function to get asset URL
function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

// Helper function to redirect
function redirect($path) {
    header('Location: ' . base_url($path));
    exit;
}

/**
 * Helper function to sanitize input
 *
 * FIX: Added null coalescing ($input ?? '') so that passing null does not
 * trigger "Deprecated: trim(): Passing null to parameter #1" on PHP 8+.
 * Also added an array branch so nested arrays are sanitized recursively.
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    // FIX: coerce null → empty string before trim() is called
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

// Helper function to format price
function format_price($price, $status = 'sale') {
    if ($status === 'rent') {
        return '$' . number_format($price, 0) . '/mo';
    }
    return '$' . number_format($price, 0);
}

// Helper function to format date
function format_date($date, $format = 'M d, Y') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

// Helper function to truncate text
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Helper function to generate slug
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}

// Helper function to generate random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Helper function to check if admin is logged in
function is_admin() {
    return is_logged_in() && $_SESSION['user_type'] === 'admin';
}

// Helper function to check if agent is logged in
function is_agent() {
    return is_logged_in() && $_SESSION['user_type'] === 'agent';
}

// Helper function to get current user ID
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to get current user type
function current_user_type() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Helper function to set flash message
 *
 * Signature: set_flash_message($type, $message)
 * Example:   set_flash_message('success', 'Property approved.')
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

// Helper function to get flash messages
function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// Helper function to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Helper function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Helper function to display errors
function display_errors($errors) {
    if (empty($errors)) return '';

    $html  = '<div class="alert alert-danger">';
    $html .= '<ul class="mb-0">';
    foreach ($errors as $error) {
        $html .= '<li>' . sanitize($error) . '</li>';
    }
    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}