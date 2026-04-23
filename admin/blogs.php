<?php
/**
 * iProply - Admin Blog Management
 */
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Blog.php';

$auth = new Auth();
$auth->requireAdmin();

$blogModel = new Blog();

$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingPost = $editingId ? $blogModel->getById($editingId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('admin/blogs.php');
    }

    if ($_POST['action'] === 'save_blog') {
        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : null;
        $savedId = $blogModel->save($_POST, current_user_id(), $postId ?: null);

        if ($savedId) {
            set_flash_message('success', 'Blog post saved successfully.');
        } else {
            set_flash_message('error', 'Title and content are required.');
        }
    }

    if ($_POST['action'] === 'delete_blog' && !empty($_POST['post_id'])) {
        $blogModel->delete((int) $_POST['post_id']);
        set_flash_message('success', 'Blog post deleted successfully.');
    }

    redirect('admin/blogs.php?status=' . urlencode($status) . '&search=' . urlencode($search));
}

$posts = $blogModel->getAllForAdmin($status, $search);
$csrfToken = generate_csrf_token();
$pageTitle = 'Manage Blogs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .form-grid { display: grid; gap: 1rem; }
        .form-grid input, .form-grid textarea, .form-grid select {
            width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-md);
            font: 0.875rem var(--font-family);
        }
        .form-grid textarea { min-height: 180px; resize: vertical; }
        .table-wrap { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 0.9rem; border-bottom: 1px solid var(--border); text-align: left; }
        .table th { font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); }
        .badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-published { background: #f0fff4; color: #2f855a; }
        .badge-draft { background: #fffaf0; color: #c05621; }
        .actions { display: flex; gap: 0.5rem; }
        .btn-danger { background: #e53e3e; color: #fff; border: none; padding: 0.45rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="properties.php"><i class="fas fa-building"></i> Properties</a>
            <a href="agents.php"><i class="fas fa-users"></i> Agents</a>
            <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
            <a href="blogs.php" class="active"><i class="fas fa-blog"></i> Blogs</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title"><h1>Manage Blogs</h1></div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name']); ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            </div>
        </header>

        <main class="content">
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?php echo sanitize($message['type']); ?>">
                        <?php echo sanitize($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2><?php echo $editingPost ? 'Edit Post' : 'Create Post'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="save_blog">
                        <input type="hidden" name="post_id" value="<?php echo (int) ($editingPost['id'] ?? 0); ?>">

                        <input type="text" name="title" placeholder="Blog title" required value="<?php echo sanitize($editingPost['title'] ?? ''); ?>">
                        <textarea name="excerpt" placeholder="Short excerpt"><?php echo sanitize($editingPost['excerpt'] ?? ''); ?></textarea>
                        <textarea name="content" placeholder="Blog content" required><?php echo sanitize($editingPost['content'] ?? ''); ?></textarea>
                        <select name="status">
                            <option value="draft" <?php echo (($editingPost['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (($editingPost['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>

                        <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                            <?php if ($editingPost): ?>
                                <a href="blogs.php" class="btn btn-secondary">Cancel Edit</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Post</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>All Blog Posts</h2>
                </div>
                <div class="card-body">
                    <form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
                        <input type="text" name="search" value="<?php echo sanitize($search); ?>" placeholder="Search title or content" style="flex:1; min-width:220px; padding:0.65rem;">
                        <select name="status" style="padding:0.65rem;">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    </form>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Published</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr><td colspan="4">No blog posts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?php echo sanitize($post['title']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $post['status'] === 'published' ? 'badge-published' : 'badge-draft'; ?>">
                                                    <?php echo ucfirst($post['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $post['published_at'] ? format_date($post['published_at']) : '-'; ?></td>
                                            <td class="actions">
                                                <a class="btn btn-primary" href="blogs.php?edit=<?php echo (int) $post['id']; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">Edit</a>
                                                <form method="POST" onsubmit="return confirm('Delete this post?');">
                                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="delete_blog">
                                                    <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                                                    <button type="submit" class="btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
