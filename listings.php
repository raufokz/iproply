<?php
/**
 * iProply - Property Listings Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';
require_once 'includes/listing-filters.php';

// Set current page
$currentPage = 'listings';
$pageTitle   = 'Property Listings';

$propertyModel = new Property();

$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;

// Canonical filter map (PDO-safe placeholders live in Property::compilePublicListingConditions).
$requestFilters = listing_filters_from_request($_GET);

$result = $propertyModel->searchListingsPaginated($requestFilters, $page, $perPage);

$properties               = $result['properties'];
$totalProperties          = (int) $result['total'];
$effectiveFilters         = $result['effective_filters'];
$fallbackBannerMessage    = $result['fallback_message'];

$paginationBaseQuery = listing_filters_to_query($effectiveFilters);

$totalPages = $totalProperties > 0 ? (int) ceil($totalProperties / $perPage) : 1;

$propertyTypes = $propertyModel->getPropertyTypes();
$states        = $propertyModel->getStates();

include 'partials/header.php';

$selectedState = isset($_GET['state']) ? strtoupper(trim((string) $_GET['state'])) : '';
$advancedFilterCount = 0;
$advancedFilterCount += trim((string) ($_GET['bedrooms'] ?? '')) !== '' ? 1 : 0;
$advancedFilterCount += trim((string) ($_GET['bathrooms'] ?? '')) !== '' ? 1 : 0;
?>

<!-- Page Header -->
<header class="page-header">
    <div class="container">
        <h1>Property Listings</h1>
        <p>Find your perfect property from our extensive collection</p>
    </div>
</header>

<!-- Filter Bar -->
<section class="filter-bar">
    <div class="container">
        <form action="listings.php" method="GET" class="filter-form filter-form--compact">
            <input type="hidden" name="lat" value="<?php echo sanitize($_GET['lat'] ?? ''); ?>">
            <input type="hidden" name="lng" value="<?php echo sanitize($_GET['lng'] ?? ''); ?>">
            <?php if (!empty($_GET['radius_mi']) && is_numeric($_GET['radius_mi'])): ?>
                <input type="hidden" name="radius_mi" value="<?php echo sanitize($_GET['radius_mi']); ?>">
            <?php endif; ?>
            <input type="hidden" name="city" value="<?php echo sanitize($_GET['city'] ?? ''); ?>">
            <?php if (!empty($_GET['featured'])): ?>
                <input type="hidden" name="featured" value="1">
            <?php endif; ?>
            <?php if (!empty($_GET['agent']) && ctype_digit((string) $_GET['agent'])): ?>
                <input type="hidden" name="agent" value="<?php echo sanitize($_GET['agent']); ?>">
            <?php endif; ?>

            <div class="filter-group filter-group--keyword">
                <label for="list-keyword">Keywords / address / ZIP</label>
                <input type="text" name="keyword" id="list-keyword" placeholder="City, address, ZIP, or keyword" value="<?php echo sanitize($_GET['keyword'] ?? ($_GET['search'] ?? '')); ?>">
            </div>

            <div class="filter-group">
                <label for="list-state">State</label>
                <select name="state" id="list-state">
                    <option value="">All states</option>
                    <?php foreach ((array) $states as $st): ?>
                        <option value="<?php echo sanitize($st); ?>" <?php echo $selectedState === $st ? 'selected' : ''; ?>>
                            <?php echo sanitize($st); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="list-type">Property Type</label>
                <select name="type" id="list-type">
                    <option value="">All Types</option>
                    <?php foreach ($propertyTypes as $type): ?>
                        <option value="<?php echo sanitize($type['id']); ?>" <?php echo (string) ($_GET['type'] ?? '') === (string) $type['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="list-status">Status</label>
                <select name="status" id="list-status">
                    <option value="">Any status</option>
                    <option value="sale" <?php echo (($_GET['status'] ?? '') === 'sale') ? 'selected' : ''; ?>>For Sale</option>
                    <option value="rent" <?php echo (($_GET['status'] ?? '') === 'rent') ? 'selected' : ''; ?>>For Rent</option>
                </select>
            </div>

            <div class="filter-group filter-group--price">
                <label for="list-minp">Price Range</label>
                <div class="filter-price-row">
                    <input type="number" name="min_price" id="list-minp" placeholder="$0" min="0" step="1000" aria-label="Minimum price" value="<?php echo sanitize($_GET['min_price'] ?? ''); ?>">
                    <input type="number" name="max_price" id="list-maxp" placeholder="No max" min="0" step="1000" aria-label="Maximum price" value="<?php echo sanitize($_GET['max_price'] ?? ''); ?>">
                </div>
            </div>

            <details class="filter-more">
                <summary class="filter-more-toggle">
                    <i class="fas fa-sliders" aria-hidden="true"></i>
                    <span>More</span>
                    <?php if ($advancedFilterCount > 0): ?>
                        <span class="filter-more-count" aria-label="<?php echo $advancedFilterCount; ?> advanced filters active"><?php echo $advancedFilterCount; ?></span>
                    <?php endif; ?>
                </summary>
                <div class="filter-more-menu">
                    <div class="filter-group">
                        <label for="list-beds">Beds min</label>
                        <input type="number" name="bedrooms" id="list-beds" placeholder="Any" min="0" step="1" value="<?php echo sanitize($_GET['bedrooms'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="list-baths">Baths min</label>
                        <input type="number" name="bathrooms" id="list-baths" placeholder="Any" min="0" step="0.5" value="<?php echo sanitize($_GET['bathrooms'] ?? ''); ?>">
                    </div>
                </div>
            </details>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="listings.php" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if (!empty($_GET['lat']) && !empty($_GET['lng'])): ?>
            <p class="listings-geo-note" style="margin:0 0 1rem; font-size:0.9rem; color: var(--text-secondary);">
                <i class="fas fa-location-crosshairs" aria-hidden="true"></i>
                Showing homes near your selected location<?php echo !empty($_GET['radius_mi']) ? ' (about ' . (int) $_GET['radius_mi'] . ' mi radius).' : '.'; ?>
            </p>
        <?php endif; ?>

        <?php if ($fallbackBannerMessage !== null): ?>
            <div class="alert alert-info" role="status" style="margin-bottom: 1.5rem;">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <?php echo sanitize($fallbackBannerMessage); ?>
                <span style="display:block;margin-top:0.5rem;font-size:0.9rem;">You can widen price or remove filters anytime using the toolbar above.</span>
            </div>
        <?php endif; ?>

        <div class="listings-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <p style="color: var(--text-secondary);">
                Showing <?php echo is_array($properties) ? count($properties) : 0; ?> of <?php echo $totalProperties; ?> properties
            </p>
        </div>

        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <h3>No properties found</h3>
                <p>Try clearing the map radius, widening your price band, or searching by state.</p>
            </div>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <a href="property.php?slug=<?php echo sanitize($property['slug']); ?>" style="text-decoration: none; color: inherit;">
                            <div class="property-image">
                                <?php if (!empty($property['primary_image'])): ?>
                                    <img src="<?php echo property_image_url($property['primary_image']); ?>" alt="<?php echo sanitize($property['title']); ?>">
                                <?php else: ?>
                                    <img src="<?php echo property_image_url(''); ?>" alt="Property image coming soon">
                                <?php endif; ?>

                                <span class="property-badge badge-<?php echo sanitize($property['status']); ?>">
                                    For <?php echo ucfirst(sanitize($property['status'])); ?>
                                </span>

                                <?php if (!empty($property['is_featured'])): ?>
                                    <span class="property-badge badge-featured" style="left: auto; right: 15px;">Featured</span>
                                <?php endif; ?>

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
                                <div class="property-features">
                                    <?php if (!empty($property['bedrooms'])): ?>
                                        <div class="property-feature">
                                            <i class="fas fa-bed"></i>
                                            <span><?php echo intval($property['bedrooms']); ?> Beds</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($property['bathrooms'])): ?>
                                        <div class="property-feature">
                                            <i class="fas fa-bath"></i>
                                            <span><?php echo htmlspecialchars((string) $property['bathrooms'], ENT_QUOTES, 'UTF-8'); ?> Baths</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($property['area_sqft'])): ?>
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span><?php echo number_format((int) $property['area_sqft']); ?> sqft</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $prevQ = array_merge($paginationBaseQuery, ['page' => $page - 1]);
                    $nextQ = array_merge($paginationBaseQuery, ['page' => $page + 1]);
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query($prevQ); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($paginationBaseQuery, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query($nextQ); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
