<?php
/**
 * Realty - Admin Logout
 */

require_once '../config/config.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->logout();

set_flash_message('success', 'You have been logged out successfully.');
redirect('admin/login.php');
