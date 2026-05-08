<?php
/**
 * Agent - delete own listing via POST.
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';

$auth = new Auth();
$auth->requireAgent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('error', 'Invalid request method.');
    redirect('agent/properties.php');
}

if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
    set_flash_message('error', 'Invalid request. Please try again.');
    redirect('agent/properties.php');
}

$id = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
if ($id <= 0) {
    set_flash_message('error', 'Invalid listing.');
    redirect('agent/properties.php');
}

$propertyModel = new Property();
$row = $propertyModel->getById($id);
if (!$row || (int) $row['agent_id'] !== (int) current_user_id()) {
    set_flash_message('error', 'Listing not found or you do not have permission to delete it.');
    redirect('agent/properties.php');
}

if ($propertyModel->delete($id)) {
    set_flash_message('success', 'Listing removed and images cleaned up.');
} else {
    set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to remove listing.');
}

redirect('agent/properties.php');
