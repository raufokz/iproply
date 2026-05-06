<?php
/**
 * Agent — delete own listing (GET with confirm from UI).
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';

$auth = new Auth();
$auth->requireAgent();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash_message('error', 'Invalid listing.');
    redirect('agent/properties.php');
}

$propertyModel = new Property();
$row = $propertyModel->getById($id);
if (!$row || (int)$row['agent_id'] !== (int)current_user_id()) {
    set_flash_message('error', 'Listing not found or you do not have permission to delete it.');
    redirect('agent/properties.php');
}

$propertyModel->delete($id);
set_flash_message('success', 'Listing removed.');
redirect('agent/properties.php');
