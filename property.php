<?php
/**
 * iProply - Property Detail Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';
require_once 'includes/Inquiry.php';
require_once 'includes/Mail.php';

// Initialize models
$propertyModel = new Property();
$inquiryModel = new Inquiry();

// Get property slug
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    set_flash_message('error', 'Property not found');
    redirect('listings.php');
}

// Get property details
$property = $propertyModel->getBySlug($slug);

if (!$property) {
    set_flash_message('error', 'Property not found');
    redirect('listings.php');
}

// Increment view count
$propertyModel->incrementViews($property['id']);

// Get property images
$images = $propertyModel->getImages($property['id']);

// Get similar properties
$similarProperties = $propertyModel->getSimilar($property, 3);

// Set page title
$pageTitle = $property['title'];
$currentPage = 'listings';

// Handle inquiry form submission
$inquiryErrors = [];
$inquirySuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $inquiryErrors[] = 'Invalid request. Please try again.';
    } else {
        $inquiryData = [
            'property_id' => $property['id'],
            'agent_id' => $property['agent_id'],
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];

        $inquiryId = $inquiryModel->create($inquiryData);

        if ($inquiryId) {
            // Send email notification to agent
            $mail = new Mail();
            $inquiry = $inquiryModel->getById($inquiryId);
            $mail->sendInquiryToAgent($property, $inquiry, $property);

            // Send confirmation to user
            $mail->sendInquiryConfirmation($inquiry, $property);

            $inquirySuccess = true;
        } else {
            $inquiryErrors = $inquiryModel->getErrors();
        }
    }
}

// Include header
include 'partials/header.php';
?>

<!-- Property Detail -->
<section class="property-detail">
    <div class="container">
        <!-- Gallery -->
        <div class="property-gallery">
            <div class="gallery-main">
                <?php if (!empty($images)): ?>
                    <img id="mainImage" src="<?php echo UPLOAD_URL . 'properties/' . $images[0]['image_path']; ?>" alt="<?php echo sanitize($property['title']); ?>">
                <?php else: ?>
                    <img id="mainImage" src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&h=600&fit=crop" alt="Property">
                <?php endif; ?>
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $index => $image): ?>
                        <img src="<?php echo UPLOAD_URL . 'properties/' . $image['image_path']; ?>" 
                             data-full="<?php echo UPLOAD_URL . 'properties/' . $image['image_path']; ?>"
                             alt="Property Image <?php echo $index + 1; ?>"
                             class="<?php echo $index === 0 ? 'active' : ''; ?>"
                             onclick="changeMainImage(this)">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Header -->
        <div class="property-header">
            <h1><?php echo sanitize($property['title']); ?></h1>
            <div class="property-meta">
                <div class="property-meta-price">
                    <?php echo format_price($property['price'], $property['status']); ?>
                </div>
                <div class="property-meta-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo sanitize($property['address']); ?>, 
                    <?php echo sanitize($property['city']); ?>, 
                    <?php echo sanitize($property['state']); ?> 
                    <?php echo sanitize($property['zip_code']); ?>
                </div>
                <div class="property-meta-features">
                    <?php if ($property['bedrooms'] > 0): ?>
                        <div class="property-meta-feature">
                            <i class="fas fa-bed"></i>
                            <span><?php echo $property['bedrooms']; ?> Beds</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($property['bathrooms'] > 0): ?>
                        <div class="property-meta-feature">
                            <i class="fas fa-bath"></i>
                            <span><?php echo $property['bathrooms']; ?> Baths</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($property['area_sqft'] > 0): ?>
                        <div class="property-meta-feature">
                            <i class="fas fa-ruler-combined"></i>
                            <span><?php echo number_format($property['area_sqft']); ?> sqft</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="property-content-grid">
            <div class="property-main-content">
                <!-- Description -->
                <div class="property-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(sanitize($property['description'])); ?></p>
                </div>
                
                <!-- Features -->
                <?php if (!empty($property['features'])): ?>
                    <?php $features = json_decode($property['features'], true); ?>
                    <?php if (!empty($features)): ?>
                        <div class="property-features">
                            <h2>Features</h2>
                            <ul class="features-list">
                                <?php foreach ($features as $feature): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo sanitize($feature); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Amenities -->
                <?php if (!empty($property['amenities'])): ?>
                    <?php $amenities = json_decode($property['amenities'], true); ?>
                    <?php if (!empty($amenities)): ?>
                        <div class="property-features">
                            <h2>Amenities</h2>
                            <ul class="features-list">
                                <?php foreach ($amenities as $amenity): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo sanitize($amenity); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Property Details -->
                <div class="property-features">
                    <h2>Property Details</h2>
                    <ul class="features-list">
                        <?php if ($property['property_type_name']): ?>
                            <li>
                                <i class="fas fa-home"></i>
                                Property Type: <?php echo sanitize($property['property_type_name']); ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($property['year_built']): ?>
                            <li>
                                <i class="fas fa-calendar"></i>
                                Year Built: <?php echo $property['year_built']; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($property['lot_size'] > 0): ?>
                            <li>
                                <i class="fas fa-expand"></i>
                                Lot Size: <?php echo number_format($property['lot_size']); ?> sqft
                            </li>
                        <?php endif; ?>
                        <?php if ($property['parking_spaces'] > 0): ?>
                            <li>
                                <i class="fas fa-car"></i>
                                Parking Spaces: <?php echo $property['parking_spaces']; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($property['floors'] > 1): ?>
                            <li>
                                <i class="fas fa-building"></i>
                                Floors: <?php echo $property['floors']; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="property-sidebar">
                <!-- Agent Card -->
                <div class="agent-card-detail">
                    <h2>Contact Agent</h2>
                    <div class="agent-card-header">
                        <?php if ($property['agent_avatar']): ?>
                            <img src="<?php echo UPLOAD_URL . 'agents/' . $property['agent_avatar']; ?>" alt="<?php echo sanitize($property['agent_name']); ?>">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($property['agent_name']); ?>&background=1e3b5a&color=fff" alt="<?php echo sanitize($property['agent_name']); ?>">
                        <?php endif; ?>
                        <div class="agent-card-info">
                            <h3><?php echo sanitize($property['agent_name']); ?></h3>
                            <p>Real Estate Agent</p>
                            <?php if ($property['agent_license']): ?>
                                <p style="font-size: 12px; color: var(--text-muted);">License: <?php echo sanitize($property['agent_license']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <ul class="agent-contact-list">
                        <?php if ($property['agent_phone']): ?>
                            <li>
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $property['agent_phone']); ?>"><?php echo sanitize($property['agent_phone']); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($property['agent_email']): ?>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo sanitize($property['agent_email']); ?>"><?php echo sanitize($property['agent_email']); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $property['agent_phone'] ?? ''); ?>" class="btn btn-primary agent-contact-btn">
                        <i class="fas fa-phone"></i> Call Agent
                    </a>
                </div>
                
                <!-- Inquiry Form -->
                <div class="inquiry-form">
                    <h3>Send Inquiry</h3>
                    
                    <?php if ($inquirySuccess): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Thank you for your inquiry! We will get back to you soon.
                        </div>
                    <?php else: ?>
                        <?php if (!empty($inquiryErrors)): ?>
                            <div class="alert alert-error">
                                <?php foreach ($inquiryErrors as $error): ?>
                                    <p><?php echo sanitize($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" data-validate>
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo sanitize($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="4" required><?php echo sanitize($_POST['message'] ?? 'I am interested in this property. Please contact me with more information.'); ?></textarea>
                            </div>
                            
                            <button type="submit" name="submit_inquiry" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-paper-plane"></i> Send Inquiry
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Similar Properties -->
        <?php if (!empty($similarProperties)): ?>
            <div class="similar-properties" style="margin-top: 4rem;">
                <h2 style="font-size: var(--font-size-2xl); font-weight: 700; margin-bottom: 2rem;">Similar Properties</h2>
                
                <div class="property-grid">
                    <?php foreach ($similarProperties as $similar): ?>
                        <div class="property-card">
                            <a href="property.php?slug=<?php echo $similar['slug']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="property-image">
                                    <?php if ($similar['primary_image']): ?>
                                        <img src="<?php echo UPLOAD_URL . 'properties/' . $similar['primary_image']; ?>" alt="<?php echo sanitize($similar['title']); ?>">
                                    <?php else: ?>
                                        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop" alt="Property">
                                    <?php endif; ?>
                                    
                                    <span class="property-badge badge-<?php echo $similar['status']; ?>">
                                        For <?php echo ucfirst($similar['status']); ?>
                                    </span>
                                    
                                    <div class="property-price">
                                        <?php echo format_price($similar['price'], $similar['status']); ?>
                                    </div>
                                </div>
                                
                                <div class="property-content">
                                    <h3 class="property-title"><?php echo sanitize($similar['title']); ?></h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo sanitize($similar['city']) . ', ' . sanitize($similar['state']); ?>
                                    </div>
                                    <div class="property-features">
                                        <?php if ($similar['bedrooms'] > 0): ?>
                                            <div class="property-feature">
                                                <i class="fas fa-bed"></i>
                                                <span><?php echo $similar['bedrooms']; ?> Beds</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($similar['bathrooms'] > 0): ?>
                                            <div class="property-feature">
                                                <i class="fas fa-bath"></i>
                                                <span><?php echo $similar['bathrooms']; ?> Baths</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeMainImage(thumb) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = thumb.dataset.full;
    
    // Update active state
    document.querySelectorAll('.gallery-thumbs img').forEach(img => {
        img.classList.remove('active');
    });
    thumb.classList.add('active');
}
</script>

<?php
// Include footer
include 'partials/footer.php';
?>
