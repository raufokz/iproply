<?php
/**
 * iProply - Dynamic CMS Page Renderer
 * URL: /page.php?slug=your-page-slug
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Page.php';

$pageSlug = isset($pageSlug) ? $pageSlug : ($_GET['slug'] ?? '');
$pageSlug = strtolower(trim((string) $pageSlug));
$pageSlug = preg_replace('/[^a-z0-9-]/', '', $pageSlug);

$pageModel = new Page();

$isPreview = is_admin() && (($_GET['preview'] ?? '') === '1');
try {
    $page = $isPreview ? $pageModel->getBySlug($pageSlug) : $pageModel->getPublishedBySlug($pageSlug);
} catch (Exception $e) {
    http_response_code(500);
    require_once '500.php';
    exit;
}

if (!$page) {
    http_response_code(404);
    require_once '404.php';
    exit;
}

$pageTitle = $page['title'];
$metaTitle = !empty($page['meta_title']) ? $page['meta_title'] : null;
$metaDescription = !empty($page['meta_description']) ? $page['meta_description'] : null;
$metaKeywords = !empty($page['meta_keywords']) ? $page['meta_keywords'] : null;

include 'partials/header.php';
?>

<section class="cms-hero">
    <div class="container">
        <h1 class="cms-title"><?php echo sanitize($page['title']); ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
            <p class="cms-subtitle"><?php echo sanitize($page['meta_description']); ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="cms-section">
    <div class="container">
        <div class="cms-content">
            <?php echo Page::renderContent($page['content']); ?>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
