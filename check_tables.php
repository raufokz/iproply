<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();
$count = $db->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
echo "\nBlogs table count: $count\n";

$blog = $db->query("SELECT * FROM blogs LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Blog columns:\n";
print_r(array_keys($blog));
