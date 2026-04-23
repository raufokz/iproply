<?php
/**
 * iProply - Newsletter Subscribe Handler
 * Stores footer newsletter signups in `newsletter_subscribers`.
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    redirect('');
}

if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
    set_flash_message('error', 'Invalid request. Please try again.');
    redirect('');
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash_message('error', 'Please enter a valid email address.');
    redirect('');
}

try {
    $db = Database::getInstance();

    if ($db->exists('newsletter_subscribers', 'email = :email', ['email' => $email])) {
        $db->update('newsletter_subscribers', ['status' => 'active'], 'email = :email', ['email' => $email]);
    } else {
        $db->insert('newsletter_subscribers', [
            'email' => $email,
            'status' => 'active',
        ]);
    }

    set_flash_message('success', 'Thanks for joining — we’ll keep you updated.');
} catch (Exception $e) {
    set_flash_message('error', 'Sorry — we could not subscribe you right now. Please try again later.');
}

// Redirect back to the page the user came from (same host only).
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$refHost = $ref ? parse_url($ref, PHP_URL_HOST) : null;
$appHost = parse_url(APP_URL, PHP_URL_HOST);

if ($ref && $refHost && $refHost === $appHost) {
    header('Location: ' . $ref);
    exit;
}

redirect('');

