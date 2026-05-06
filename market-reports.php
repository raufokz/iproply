<?php
/**
 * iProply — Market Reports
 * Live inventory snapshot from active listings plus CMS intro and actionable tools.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Property.php';
require_once __DIR__ . '/includes/Page.php';
require_once __DIR__ . '/includes/static-cms-pages.php';

$propertyModel = new Property();
$pageModel     = new Page();

$page = null;
try {
    $page = $pageModel->getPublishedBySlug('market-reports');
} catch (Exception $e) {
    // fall through to static
}
if (!$page) {
    $page = get_static_cms_page('market-reports');
}

if (!$page) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$snapshot = [];
$byState  = [];
$byMetro  = [];
$spotlight = [];

try {
    $snapshot  = $propertyModel->getMarketSnapshotTotals();
    $byState   = $propertyModel->getMarketStatsByState(15);
    $byMetro   = $propertyModel->getMarketStatsByMetro(12);
    $spotlight = $propertyModel->getLatest(6);
} catch (Exception $e) {
    error_log('market-reports aggregates: ' . $e->getMessage());
}

$totalActive = (int) ($snapshot['total_active'] ?? 0);
$saleCount   = (int) ($snapshot['sale_count'] ?? 0);
$rentCount   = (int) ($snapshot['rent_count'] ?? 0);

$avgSale = isset($snapshot['avg_sale_price']) && $snapshot['avg_sale_price'] !== null
    ? (float) $snapshot['avg_sale_price'] : null;
$avgRent = isset($snapshot['avg_rent_price']) && $snapshot['avg_rent_price'] !== null
    ? (float) $snapshot['avg_rent_price'] : null;

$pageTitle       = $page['title'];
$metaTitle       = !empty($page['meta_title']) ? $page['meta_title'] : ('Market Reports | ' . APP_NAME);
$metaDescription = !empty($page['meta_description']) ? $page['meta_description'] : '';
$extraCss          = ['market-reports.css'];

$contactMarketHref = base_url('contact.php?subject=' . rawurlencode('Custom market snapshot request'));

include __DIR__ . '/partials/header.php';
?>

<section class="market-hero">
    <div class="container market-hero__inner">
        <p class="market-hero__kicker"><i class="fas fa-chart-line" aria-hidden="true"></i> Inventory intelligence</p>
        <h1 class="market-hero__title"><?php echo sanitize($page['title']); ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
            <p class="market-hero__lead"><?php echo sanitize($page['meta_description']); ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="market-disclaimer">
    <div class="container">
        <div class="market-disclaimer__box" role="note">
            <strong>How to read this page.</strong>
            Figures below are derived from <em>active iProply listings only</em> — not county recorder sales, not all MLS closed data, and not real-time for every market.
            Use them as a directional snapshot of what is on the platform right now. For a deeper study of your neighborhood, use the filters on Listings or request a custom brief from our team.
        </div>
    </div>
</section>

<section class="market-stats-section">
    <div class="container">
        <h2 class="market-section-title">At a glance</h2>
        <p class="market-section-sub">Based on active inventory on <?php echo sanitize(date('F j, Y')); ?>.</p>

        <div class="market-stat-grid">
            <div class="market-stat-card">
                <span class="market-stat-card__label">Active listings</span>
                <span class="market-stat-card__value"><?php echo number_format($totalActive); ?></span>
                <span class="market-stat-card__meta"><?php echo number_format($saleCount); ?> for sale · <?php echo number_format($rentCount); ?> for rent</span>
            </div>
            <div class="market-stat-card">
                <span class="market-stat-card__label">Avg. list — for sale</span>
                <span class="market-stat-card__value"><?php echo $avgSale !== null ? format_price($avgSale, 'sale') : '—'; ?></span>
                <?php if ($saleCount > 0 && $snapshot['min_sale_price'] !== null && $snapshot['max_sale_price'] !== null): ?>
                    <span class="market-stat-card__meta">Range <?php echo format_price((float) $snapshot['min_sale_price'], 'sale'); ?> – <?php echo format_price((float) $snapshot['max_sale_price'], 'sale'); ?></span>
                <?php else: ?>
                    <span class="market-stat-card__meta">Add sale listings to see range</span>
                <?php endif; ?>
            </div>
            <div class="market-stat-card">
                <span class="market-stat-card__label">Avg. ask — for rent</span>
                <span class="market-stat-card__value"><?php echo $avgRent !== null ? format_price($avgRent, 'rent') : '—'; ?></span>
                <?php if ($rentCount > 0 && $snapshot['min_rent_price'] !== null && $snapshot['max_rent_price'] !== null): ?>
                    <span class="market-stat-card__meta">Range <?php echo format_price((float) $snapshot['min_rent_price'], 'rent'); ?> – <?php echo format_price((float) $snapshot['max_rent_price'], 'rent'); ?></span>
                <?php else: ?>
                    <span class="market-stat-card__meta">Add rentals to see range</span>
                <?php endif; ?>
            </div>
            <div class="market-stat-card market-stat-card--accent">
                <span class="market-stat-card__label">Next steps</span>
                <span class="market-stat-card__value market-stat-card__value--sm">Drill into real inventory</span>
                <div class="market-stat-card__actions">
                    <a class="btn btn-primary btn-sm" href="<?php echo base_url('listings.php'); ?>">Open listings</a>
                    <a class="btn btn-outline btn-sm" href="<?php echo sanitize($contactMarketHref); ?>">Request brief</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="market-cms-section">
    <div class="container">
        <div class="market-cms">
            <?php echo Page::renderContent($page['content']); ?>
        </div>
    </div>
</section>

<section class="market-tables-section">
    <div class="container">
        <h2 class="market-section-title">Where inventory is concentrated</h2>
        <p class="market-section-sub">Click a row to open matching properties in Listings.</p>

        <div class="market-tables-grid">
            <div class="market-table-wrap">
                <h3 class="market-table-title">By state</h3>
                <?php if (empty($byState)): ?>
                    <p class="market-empty">No state-level data yet. Publish active listings with a state to populate this table.</p>
                <?php else: ?>
                    <div class="table-scroll">
                        <table class="market-table">
                            <thead>
                                <tr>
                                    <th>State</th>
                                    <th>Listings</th>
                                    <th>Sale / Rent</th>
                                    <th>Avg. sale</th>
                                    <th>Avg. rent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($byState as $row): ?>
                                    <?php
                                    $st = sanitize($row['state']);
                                    $listHref = base_url('listings.php?' . http_build_query(['state' => $row['state']]));
                                    $avgS = $row['avg_sale_price'] !== null ? format_price((float) $row['avg_sale_price'], 'sale') : '—';
                                    $avgR = $row['avg_rent_price'] !== null ? format_price((float) $row['avg_rent_price'], 'rent') : '—';
                                    ?>
                                    <tr>
                                        <td><a class="market-link" href="<?php echo sanitize($listHref); ?>"><?php echo $st; ?></a></td>
                                        <td><?php echo number_format((int) $row['listing_count']); ?></td>
                                        <td><?php echo number_format((int) $row['sale_count']); ?> / <?php echo number_format((int) $row['rent_count']); ?></td>
                                        <td><?php echo $avgS; ?></td>
                                        <td><?php echo $avgR; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="market-table-wrap">
                <h3 class="market-table-title">Top metros (city)</h3>
                <?php if (empty($byMetro)): ?>
                    <p class="market-empty">No metro rows yet. Active listings need city and state filled in.</p>
                <?php else: ?>
                    <div class="table-scroll">
                        <table class="market-table">
                            <thead>
                                <tr>
                                    <th>Metro</th>
                                    <th>Listings</th>
                                    <th>Sale / Rent</th>
                                    <th>Avg. sale</th>
                                    <th>Avg. rent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($byMetro as $row): ?>
                                    <?php
                                    $listHref = base_url('listings.php?' . http_build_query([
                                        'city'  => $row['city'],
                                        'state' => $row['state'],
                                    ]));
                                    $label = sanitize($row['city'] . ', ' . $row['state']);
                                    $avgS = $row['avg_sale_price'] !== null ? format_price((float) $row['avg_sale_price'], 'sale') : '—';
                                    $avgR = $row['avg_rent_price'] !== null ? format_price((float) $row['avg_rent_price'], 'rent') : '—';
                                    ?>
                                    <tr>
                                        <td><a class="market-link" href="<?php echo sanitize($listHref); ?>"><?php echo $label; ?></a></td>
                                        <td><?php echo number_format((int) $row['listing_count']); ?></td>
                                        <td><?php echo number_format((int) $row['sale_count']); ?> / <?php echo number_format((int) $row['rent_count']); ?></td>
                                        <td><?php echo $avgS; ?></td>
                                        <td><?php echo $avgR; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="market-spotlight-section">
    <div class="container">
        <div class="market-spotlight-head">
            <h2 class="market-section-title">Latest on the market</h2>
            <a class="market-text-link" href="<?php echo base_url('listings.php'); ?>">View all listings <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if (empty($spotlight)): ?>
            <p class="market-empty">No properties to showcase yet.</p>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($spotlight as $property): ?>
                    <div class="property-card">
                        <a href="<?php echo base_url('property.php?slug=' . urlencode($property['slug'])); ?>" style="text-decoration: none; color: inherit;">
                            <div class="property-image">
                                <?php if (!empty($property['primary_image'])): ?>
                                    <img src="<?php echo UPLOAD_URL . 'properties/' . sanitize($property['primary_image']); ?>" alt="<?php echo sanitize($property['title']); ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&amp;h=400&amp;fit=crop" alt="<?php echo sanitize($property['title']); ?>">
                                <?php endif; ?>
                                <span class="property-badge badge-<?php echo sanitize($property['status']); ?>">
                                    For <?php echo ucfirst(sanitize($property['status'])); ?>
                                </span>
                                <div class="property-price">
                                    <?php echo format_price($property['price'], $property['status']); ?>
                                </div>
                            </div>
                            <div class="property-content">
                                <h3 class="property-title"><?php echo sanitize($property['title']); ?></h3>
                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo sanitize($property['city']) . ', ' . sanitize($property['state']); ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="market-tools-section">
    <div class="container">
        <h2 class="market-section-title">Tools &amp; context</h2>
        <div class="market-tools">
            <a class="market-tool" href="<?php echo base_url('mortgage-calculator.php'); ?>">
                <span class="market-tool__icon"><i class="fas fa-calculator"></i></span>
                <span class="market-tool__title">Mortgage calculator</span>
                <span class="market-tool__desc">Stress-test payments against list prices.</span>
            </a>
            <a class="market-tool" href="<?php echo base_url('blog.php?q=market'); ?>">
                <span class="market-tool__icon"><i class="fas fa-newspaper"></i></span>
                <span class="market-tool__title">Blog &amp; insights</span>
                <span class="market-tool__desc">Guides and perspective from our editors.</span>
            </a>
            <a class="market-tool" href="<?php echo base_url('help-center.php'); ?>">
                <span class="market-tool__icon"><i class="fas fa-life-ring"></i></span>
                <span class="market-tool__title">Help center</span>
                <span class="market-tool__desc">Search tips, contacting agents, and account help.</span>
            </a>
            <a class="market-tool" href="<?php echo sanitize($contactMarketHref); ?>">
                <span class="market-tool__icon"><i class="fas fa-envelope"></i></span>
                <span class="market-tool__title">Custom snapshot</span>
                <span class="market-tool__desc">Tell us your city and goals — we will follow up.</span>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
