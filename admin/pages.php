<?php
/**
 * iProply - Admin Pages (CMS)
 * Manage content pages with metadata + footer navigation (CRUD + publish/unpublish).
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Page.php';

$auth = new Auth();
$auth->requireAdmin();

$pageModel = new Page();

$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingPage = $editingId ? $pageModel->getById($editingId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('admin/pages.php');
    }

    if ($_POST['action'] === 'save_page') {
        $id = !empty($_POST['page_id']) ? (int) $_POST['page_id'] : null;
        $savedId = $pageModel->save($_POST, current_user_id(), $id ?: null);

        if ($savedId) {
            set_flash_message('success', 'Page saved successfully.');
        } else {
            set_flash_message('error', 'Title and content are required.');
        }
    }

    if ($_POST['action'] === 'delete_page' && !empty($_POST['page_id'])) {
        $pageModel->delete((int) $_POST['page_id']);
        set_flash_message('success', 'Page deleted successfully.');
    }

    redirect('admin/pages.php?status=' . urlencode($status) . '&search=' . urlencode($search));
}

$pages = $pageModel->getAllForAdmin($status, $search);
$csrfToken = generate_csrf_token();
$pageTitle = 'Manage Pages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?> - <?php echo sanitize(APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .form-grid { display: grid; gap: 1rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-grid label { font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); display:block; margin-bottom:0.35rem; }
        .form-grid input, .form-grid textarea, .form-grid select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font: 0.875rem var(--font-family);
            background: #fff;
        }
        .form-grid textarea { min-height: 220px; resize: vertical; line-height: 1.55; }
        .help { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.35rem; }
        .table-wrap { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 0.9rem; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
        .table th { font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); }
        .badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; display:inline-block; }
        .badge-published { background: #f0fff4; color: #2f855a; }
        .badge-draft { background: #fffaf0; color: #c05621; }
        .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .btn-danger { background: #e53e3e; color: #fff; border: none; padding: 0.45rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; }
        .btn-danger:hover { filter: brightness(0.95); }
        .btn-link { color: var(--primary-light); text-decoration: none; font-weight: 600; }
        .btn-link:hover { text-decoration: underline; }
        .meta-grid { display:grid; grid-template-columns: 1fr; gap: 1rem; }
        .checkbox-row { display:flex; align-items:center; gap:0.5rem; }
        .checkbox-row input { width:auto; }
        @media (max-width: 900px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo sanitize(APP_NAME); ?></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="properties.php"><i class="fas fa-building"></i> Properties</a>
            <a href="agents.php"><i class="fas fa-users"></i> Agents</a>
            <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
            <a href="blogs.php"><i class="fas fa-blog"></i> Blogs</a>
            <a href="pages.php" class="active"><i class="fas fa-file-lines"></i> Pages</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <h1><?php echo sanitize($pageTitle); ?></h1>
            </div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name'] ?? 'Admin'); ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
            </div>
        </header>

        <main class="content">
            <?php foreach (get_flash_messages() as $msg): ?>
                <div class="alert alert-<?php echo sanitize($msg['type']); ?>" style="margin-bottom: 1rem;">
                    <?php echo sanitize($msg['message']); ?>
                </div>
            <?php endforeach; ?>

            <div class="card">
                <div class="card-header">
                    <h2><?php echo $editingPage ? 'Edit Page' : 'Create New Page'; ?></h2>
                    <?php if ($editingPage): ?>
                        <div class="actions">
                            <a class="btn btn-secondary" href="pages.php?status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">Cancel</a>
                            <?php if (!empty($editingPage['slug'])): ?>
                                <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?php echo base_url('page.php?slug=' . urlencode($editingPage['slug'])); ?>">View</a>
                                <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?php echo base_url('page.php?slug=' . urlencode($editingPage['slug']) . '&preview=1'); ?>">Preview</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="save_page">
                        <input type="hidden" name="page_id" value="<?php echo (int) ($editingPage['id'] ?? 0); ?>">

                        <div class="form-row">
                            <div>
                                <label for="title">Title</label>
                                <input id="title" type="text" name="title" value="<?php echo sanitize($editingPage['title'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="slug">Slug (optional)</label>
                                <input id="slug" type="text" name="slug" value="<?php echo sanitize($editingPage['slug'] ?? ''); ?>" placeholder="e.g., become-agent">
                                <div class="help">Leave blank to auto-generate from the title. Only letters, numbers, and hyphens are used.</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="draft" <?php echo (($editingPage['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo (($editingPage['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <div>
                                <label for="footer_order">Footer Order</label>
                                <input id="footer_order" type="number" name="footer_order" value="<?php echo (int) ($editingPage['footer_order'] ?? 0); ?>" min="0" step="1">
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="footer_section">Footer Section</label>
                                <select id="footer_section" name="footer_section">
                                    <?php $section = (string) ($editingPage['footer_section'] ?? ''); ?>
                                    <option value="" <?php echo $section === '' ? 'selected' : ''; ?>>None</option>
                                    <option value="join" <?php echo $section === 'join' ? 'selected' : ''; ?>>Join Us</option>
                                    <option value="about" <?php echo $section === 'about' ? 'selected' : ''; ?>>About</option>
                                    <option value="resources" <?php echo $section === 'resources' ? 'selected' : ''; ?>>Resources</option>
                                    <option value="legal" <?php echo $section === 'legal' ? 'selected' : ''; ?>>Legal</option>
                                </select>
                                <div class="help">Used to group links shown in the footer navigation.</div>
                            </div>
                            <div style="display:flex; align-items:flex-end;">
                                <label class="checkbox-row" style="margin:0;">
                                    <input type="checkbox" name="show_in_footer" value="1" <?php echo !empty($editingPage['show_in_footer']) ? 'checked' : ''; ?>>
                                    <span>Show in footer navigation</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="content">Content</label>
                            <textarea id="content" name="content" required><?php echo sanitize($editingPage['content'] ?? ''); ?></textarea>
                            <div class="help">
                                Formatting: use <code>H2: Heading</code> lines, and bullet lines starting with <code>-</code> or <code>*</code>.
                            </div>
                        </div>

                        <div class="meta-grid">
                            <div>
                                <label for="meta_title">Meta Title (optional)</label>
                                <input id="meta_title" type="text" name="meta_title" value="<?php echo sanitize($editingPage['meta_title'] ?? ''); ?>" placeholder="e.g., Become an Agent | iProply">
                            </div>
                            <div>
                                <label for="meta_description">Meta Description (optional)</label>
                                <textarea id="meta_description" name="meta_description" style="min-height: 120px;"><?php echo sanitize($editingPage['meta_description'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label for="meta_keywords">Meta Keywords (optional)</label>
                                <input id="meta_keywords" type="text" name="meta_keywords" value="<?php echo sanitize($editingPage['meta_keywords'] ?? ''); ?>" placeholder="real estate, homes, apartments">
                            </div>
                        </div>

                        <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Page</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>All Pages</h2>
                </div>
                <div class="card-body">
                    <form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
                        <input type="text" name="search" value="<?php echo sanitize($search); ?>" placeholder="Search title, slug, or content" style="flex:1; min-width:220px; padding:0.65rem;">
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
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Footer</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pages)): ?>
                                    <tr><td colspan="6">No pages found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pages as $p): ?>
                                        <tr>
                                            <td><?php echo sanitize($p['title']); ?></td>
                                            <td><code><?php echo sanitize($p['slug']); ?></code></td>
                                            <td>
                                                <span class="badge <?php echo $p['status'] === 'published' ? 'badge-published' : 'badge-draft'; ?>">
                                                    <?php echo ucfirst($p['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($p['show_in_footer'])): ?>
                                                    <span class="badge badge-success"><?php echo sanitize($p['footer_section'] ?? 'footer'); ?></span>
                                                    <div class="help" style="margin-top:0.35rem;">Order: <?php echo (int) ($p['footer_order'] ?? 0); ?></div>
                                                <?php else: ?>
                                                    <span class="help">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($p['updated_at']) ? format_date($p['updated_at'], 'M d, Y') : '-'; ?></td>
                                            <td class="actions">
                                                <a class="btn btn-primary" href="pages.php?edit=<?php echo (int) $p['id']; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">Edit</a>
                                                <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?php echo base_url('page.php?slug=' . urlencode($p['slug'])); ?>">View</a>
                                                <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?php echo base_url('page.php?slug=' . urlencode($p['slug']) . '&preview=1'); ?>">Preview</a>
                                                <form method="POST" onsubmit="return confirm('Delete this page?');">
                                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="delete_page">
                                                    <input type="hidden" name="page_id" value="<?php echo (int) $p['id']; ?>">
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

