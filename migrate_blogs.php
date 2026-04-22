<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();

echo "Starting migration...\n";

// 1. Add cover_image column to blogs table if it doesn't exist
try {
    $db->exec('ALTER TABLE blogs ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL AFTER content');
    echo "Added cover_image column to blogs table.\n";
} catch (Exception $e) {
    echo "Note: cover_image column might already exist or error: " . $e->getMessage() . "\n";
}

// 2. Add author_name column to blogs table if it doesn't exist (because earlier import used author_name, but production blogs uses created_by = admin.id). Actually, blogs has created_by. We can leave author_name out if we just rely on created_by, or add it. Let's not add it unless needed, the Blog model uses a join `CONCAT(a.first_name, ' ', a.last_name) AS author_name`. For the imported blogs, we can assign them to admin user ID 1.

// 3. Check if blog_posts exists and copy over data
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

if (in_array('blog_posts', $tables)) {
    echo "Found blog_posts table. Copying to blogs...\n";
    $oldBlogs = $db->query("SELECT * FROM blog_posts")->fetchAll(PDO::FETCH_ASSOC);
    
    $insertedCount = 0;
    foreach ($oldBlogs as $post) {
        // Check if already exists in blogs
        if ($db->exists('blogs', 'slug = :slug', ['slug' => $post['slug']])) {
            continue;
        }
        
        $payload = [
            'title' => $post['title'],
            'slug' => $post['slug'],
            'excerpt' => $post['excerpt'],
            'content' => $post['content'],
            'cover_image' => $post['cover_image'] ?? null,
            'status' => 'published',
            'published_at' => $post['created_at'],
            'created_by' => 1, // Default admin ID
            'updated_by' => 1,
            'created_at' => $post['created_at'],
            'updated_at' => $post['updated_at'] ?? $post['created_at']
        ];
        
        $db->insert('blogs', $payload);
        $insertedCount++;
    }
    
    echo "Successfully migrated $insertedCount posts from blog_posts to blogs.\n";
} else {
    echo "No blog_posts table found to copy from.\n";
}

echo "Done.\n";
