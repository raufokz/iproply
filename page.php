<?php
/**
 * iProply - Dynamic CMS Page Renderer
 * URL: /page.php?slug=your-page-slug
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Page.php';
require_once 'includes/static-cms-pages.php';
require_once 'includes/cms-page-extras.php';

$pageSlug = isset($pageSlug) ? $pageSlug : ($_GET['slug'] ?? '');
$pageSlug = strtolower(trim((string) $pageSlug));
$pageSlug = preg_replace('/[^a-z0-9-]/', '', $pageSlug);

$isPreview = is_admin() && (($_GET['preview'] ?? '') === '1');

if (!$isPreview && $pageSlug !== '') {
    if ($pageSlug === 'become-agent') {
        redirect('become-agent.php');
    }
    if ($pageSlug === 'mortgage-calculator') {
        redirect('mortgage-calculator.php');
    }
    if ($pageSlug === 'market-reports') {
        redirect('market-reports.php');
    }
    if ($pageSlug === 'referral-network') {
        redirect('referral-network.php');
    }
}

$pageModel = new Page();

try {
    $page = $isPreview ? $pageModel->getBySlug($pageSlug) : $pageModel->getPublishedBySlug($pageSlug);
} catch (Exception $e) {
    http_response_code(500);
    require_once '500.php';
    exit;
}

if (!$page) {
    $page = get_static_cms_page($pageSlug);
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

// Main nav: highlight "About" for company / story CMS routes (unless caller preset $currentPage).
if (!isset($currentPage)) {
    $cmsMainNavBySlug = [
        'about' => 'about',
        'why-iproply' => 'about',
        'our-story' => 'about',
        'community-impact' => 'about',
        'inclusion' => 'about',
        'press' => 'about',
        'careers' => 'about',
    ];
    $currentPage = $cmsMainNavBySlug[$pageSlug] ?? '';
}

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
            <?php echo cms_page_after_content_html($page['slug'] ?? $pageSlug); ?>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
