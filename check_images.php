<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();
$blogs = $db->query("SELECT id, title, content FROM blogs")->fetchAll();

$imageMentions = [];
foreach ($blogs as $blog) {
    preg_match_all('/Image:.{0,100}/is', $blog['content'], $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $imageMentions[] = "Blog ID {$blog['id']}: " . trim(str_replace("\n", ' ', $match));
        }
    }
}

if (empty($imageMentions)) {
    echo "No obvious inline image URLs found in the content.";
} else {
    echo implode("\n", $imageMentions);
}
