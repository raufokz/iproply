<?php
/**
 * iProply - Real Estate Management System
 * Main Configuration File (Production Ready)
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Application Settings
define('APP_NAME', 'iProply');
define('APP_VERSION', '1.0.0');

// Detect environment
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (PHP_SAPI === 'cli' || strpos($httpHost, 'localhost') !== false || $httpHost === '127.0.0.1') {
    define('APP_ENV', 'development');
    define('APP_URL', 'http://localhost/iproply');
} else {
    define('APP_ENV', 'production');
    define('APP_URL', 'https://' . $httpHost);
}

// ========================
// DATABASE CONFIGURATION
// ========================
if (APP_ENV === 'development') {

    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'realty');
    define('DB_USER', 'root');
    define('DB_PASS', '');

} else {

    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'u867057961_realty');
    define('DB_USER', 'u867057961_iproply');
    define('DB_PASS', 'Sales@SoftoSol77'); // ⚠️ Change in production

}

define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', '');

// ========================
// EMAIL CONFIGURATION
// ========================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'noreply@iproply.com');
define('SMTP_FROM_NAME', 'iProply');

// ========================
// FILE UPLOAD SETTINGS
// ========================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');

// ========================
// IMAGE SETTINGS
// ========================
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('THUMBNAIL_WIDTH', 400);
define('THUMBNAIL_HEIGHT', 300);

// ========================
// PAGINATION
// ========================
define('ITEMS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 20);

// ========================
// SESSION SETTINGS
// ========================
define('SESSION_NAME', 'iproply_session');
define('SESSION_LIFETIME', 7200);

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// ========================
// SECURITY SETTINGS
// ========================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// ========================
// CACHE SETTINGS
// ========================
define('CACHE_ENABLED', false);
define('CACHE_PATH', BASE_PATH . '/cache/');
define('CACHE_TTL', 3600);

// ========================
// OPTIONAL FEATURES
// ========================
define('RECAPTCHA_ENABLED', false);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

define('GOOGLE_MAPS_API_KEY', '');

// ========================
// ERROR REPORTING
// ========================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// ========================
// TIMEZONE
// ========================
date_default_timezone_set('America/New_York');

// ========================
// SESSION START
// ========================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// ========================
// AUTOLOADER
// ========================
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/includes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ========================
// HELPER FUNCTIONS
// ========================

// URL Helpers
function base_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect($path) {
    header('Location: ' . base_url($path));
    exit;
}

// Sanitize
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format price
function format_price($price, $status = 'sale') {
    return ($status === 'rent')
        ? '$' . number_format($price, 0) . '/mo'
        : '$' . number_format($price, 0);
}

// Format date
function format_date($date, $format = 'M d, Y') {
    return $date ? date($format, strtotime($date)) : 'N/A';
}

// Truncate text
function truncate($text, $length = 100, $suffix = '...') {
    return strlen($text) <= $length ? $text : substr($text, 0, $length) . $suffix;
}

// Slug generator
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

// Random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
}

// Auth helpers
function is_logged_in() {
    return isset($_SESSION['user_id'], $_SESSION['user_type']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['user_type'] === 'admin';
}

function is_agent() {
    return is_logged_in() && $_SESSION['user_type'] === 'agent';
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_type() {
    return $_SESSION['user_type'] ?? null;
}

// Flash messages
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][] = compact('type', 'message');
}

function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// CSRF
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verify_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) &&
        hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Error display
function display_errors($errors) {
    if (empty($errors)) return '';

    $html = '<div class="alert alert-danger"><ul>';
    foreach ($errors as $error) {
        $html .= '<li>' . sanitize($error) . '</li>';
    }
    return $html . '</ul></div>';
}