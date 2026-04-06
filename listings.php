<?php
/**
 * iProply - Property Listings Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';

// Set current page
$currentPage = 'listings';
$pageTitle = 'Property Listings';

// Initialize Property model
$propertyModel = new Property();

// Get filter parameters
$filters = [
    'keyword' => $_GET['keyword'] ?? '',
    'city' => $_GET['city'] ?? '',
    'state' => $_GET['state'] ?? '',
    'property_type' => $_GET['type'] ?? '',
    'status_type' => $_GET['status'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'bathrooms' => $_GET['bathrooms'] ?? ''
];

// Remove empty filters (but keep zero values such as 0 if needed)
$filters = array_filter($filters, function($value) {
    return $value !== '' && $value !== null;
});

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Get properties
$properties = $propertyModel->getAll($filters, $page, $perPage);
$totalProperties = $propertyModel->getTotalCount($filters);
$totalPages = ceil($totalProperties / $perPage);

// Get property types and states for filters
$propertyTypes = $propertyModel->getPropertyTypes();
$states = $propertyModel->getStates();

// Include header
include 'partials/header.php';
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
        <form action="listings.php" method="GET" class="filter-form">
            <div class="filter-group">
                <label>Location</label>
                <input type="text" name="keyword" placeholder="City, state, or keyword" value="<?php echo sanitize($_GET['keyword'] ?? ''); ?>">
            </div>
            
            <div class="filter-group">
                <label>Property Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach ($propertyTypes as $type): ?>
                        <option value="<?php echo sanitize($type['id']); ?>" <?php echo ($_GET['type'] ?? '') == $type['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="sale" <?php echo ($_GET['status'] ?? '') == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                    <option value="rent" <?php echo ($_GET['status'] ?? '') == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Min Price</label>
                <input type="number" name="min_price" placeholder="$0" value="<?php echo sanitize($_GET['min_price'] ?? ''); ?>">
            </div>
            
            <div class="filter-group">
                <label>Max Price</label>
                <input type="number" name="max_price" placeholder="No max" value="<?php echo sanitize($_GET['max_price'] ?? ''); ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="listings.php" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>
</section>

<!-- Listings Section -->
<section class="section">
    <div class="container">
        <div class="listings-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <p style="color: var(--text-secondary);">
                Showing <?php echo is_array($properties) ? count($properties) : 0; ?> of <?php echo intval($totalProperties); ?> properties
            </p>
        </div>
        
        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <h3>No properties found</h3>
                <p>Try adjusting your search criteria to find more properties.</p>
            </div>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <a href="property.php?slug=<?php echo sanitize($property['slug']); ?>" style="text-decoration: none; color: inherit;">
                            <div class="property-image">
                                <?php if (!empty($property['primary_image'])): ?>
                                    <img src="<?php echo UPLOAD_URL . 'properties/' . sanitize($property['primary_image']); ?>" alt="<?php echo sanitize($property['title']); ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop" alt="Property">
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
                                            <span><?php echo intval($property['bathrooms']); ?> Baths</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($property['area_sqft'])): ?>
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span><?php echo number_format(intval($property['area_sqft'])); ?> sqft</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
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

<?php
// Include footer
include 'partials/footer.php';
?>