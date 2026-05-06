<?php
/**
 * iProply - Register (redirect to agent portal)
 */
require_once 'config/config.php';
$plan = $_GET['plan'] ?? '';
$dest = base_url('agent/login.php' . ($plan ? '?plan=' . urlencode($plan) : ''));
header('Location: ' . $dest);
exit;