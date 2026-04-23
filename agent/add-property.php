<?php
/**
 * Realty - Add Property
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';
require_once '../includes/Upload.php';

// Check authentication
$auth = new Auth();
$auth->requireAgent();

// Initialize models
$propertyModel = new Property();
$upload = new Upload();

// Get property types and categories
$propertyTypes = $propertyModel->getPropertyTypes();
$categories = $propertyModel->getCategories();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate required fields
        $required = ['title', 'description', 'price', 'address', 'city', 'state'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        if (empty($errors)) {
            // Prepare property data
            $propertyData = [
                'agent_id' => current_user_id(),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'price' => floatval($_POST['price']),
                'price_type' => $_POST['price_type'] ?? 'fixed',
                'status' => $_POST['status_type'] ?? 'sale',
                'property_status' => 'pending',
                'bedrooms' => intval($_POST['bedrooms'] ?? 0),
                'bathrooms' => floatval($_POST['bathrooms'] ?? 0),
                'area_sqft' => floatval($_POST['area_sqft'] ?? 0),
                'lot_size' => floatval($_POST['lot_size'] ?? 0),
                'year_built' => !empty($_POST['year_built']) ? intval($_POST['year_built']) : null,
                'parking_spaces' => intval($_POST['parking_spaces'] ?? 0),
                'floors' => intval($_POST['floors'] ?? 1),
                'address' => trim($_POST['address']),
                'city' => trim($_POST['city']),
                'state' => trim($_POST['state']),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'property_type_id' => !empty($_POST['property_type_id']) ? intval($_POST['property_type_id']) : null,
                'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
                'features' => !empty($_POST['features']) ? json_encode(array_map('trim', explode(',', $_POST['features']))) : null,
                'amenities' => !empty($_POST['amenities']) ? json_encode(array_map('trim', explode(',', $_POST['amenities']))) : null,
                'virtual_tour_url' => trim($_POST['virtual_tour_url'] ?? ''),
                'video_url' => trim($_POST['video_url'] ?? '')
            ];

            // Create property
            $propertyId = $propertyModel->create($propertyData);

            if ($propertyId) {
                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadedImages = $upload->uploadMultiple($_FILES['images'], 'properties', [
                        'resize' => true,
                        'thumbnail' => true
                    ]);

                    foreach ($uploadedImages as $index => $imageData) {
                        $propertyModel->addImage($propertyId, $imageData, $index === 0);
                    }
                }

                set_flash_message('success', 'Property added successfully! It will be reviewed by an admin before going live.');
                redirect('agent/properties.php');
            } else {
                $errors = $propertyModel->getErrors();
            }
        }
    }
}

$pageTitle = 'Add Property';
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
    <style>
        :root {
            --primary: #1e3b5a;
            --primary-light: #2c5282;
            --secondary: #f5f5f5;
            --text-primary: #1e3b5a;
            --text-secondary: #666666;
            --border: #e0e0e0;
            --success: #48bb78;
            --warning: #ed8936;
            --error: #e53e3e;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --radius-md: 8px;
            --radius-lg: 12px;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--secondary);
            color: var(--text-primary);
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background-color: var(--primary);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header a {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-nav { padding: 1rem 0; }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-nav i { width: 20px; text-align: center; }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all 0.2s;
        }
        
        .sidebar-footer a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
        }
        
        .topbar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info { text-align: right; }
        .user-info .name { font-weight: 600; font-size: 0.875rem; }
        .user-info .role { font-size: 0.75rem; color: var(--text-secondary); }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .content { padding: 2rem; }
        
        .card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .card-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .card-body { padding: 1.5rem; }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-group label .required {
            color: var(--error);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group small {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover { background-color: var(--primary-light); }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--border);
        }
        
        .btn-outline:hover { background-color: var(--secondary); }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background-color: #fff5f5;
            color: var(--error);
            border: 1px solid #fc8181;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .image-upload {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .image-upload:hover {
            border-color: var(--primary);
            background-color: var(--secondary);
        }
        
        .image-upload i {
            font-size: 2rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .image-upload input {
            display: none;
        }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.active { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/agent.css">
</head>
<body class="agent-portal">
    <!-- Sidebar -->
    <aside class="sidebar" id="agentSidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building"></i> My Properties
            </a>
            <a href="add-property.php" class="active">
                <i class="fas fa-plus-circle"></i> Add Property
            </a>
            <a href="inquiries.php">
                <i class="fas fa-envelope"></i> Inquiries
            </a>
            <a href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>

    <div class="sidebar-overlay" data-sidebar-overlay></div>
    
    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="agentSidebar" aria-expanded="false" aria-label="Open menu">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                <h1>Add New Property</h1>
            </div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name']); ?></div>
                    <div class="role">Real Estate Agent</div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
        </header>
        
        <main class="content">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Basic Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="title">Property Title <span class="required">*</span></label>
                                <input type="text" id="title" name="title" required value="<?php echo sanitize($_POST['title'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="5" required><?php echo sanitize($_POST['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="short_description">Short Description</label>
                                <input type="text" id="short_description" name="short_description" value="<?php echo sanitize($_POST['short_description'] ?? ''); ?>">
                                <small>Brief summary for listings (max 150 characters)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price <span class="required">*</span></label>
                                <input type="number" id="price" name="price" min="0" step="0.01" required value="<?php echo sanitize($_POST['price'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="price_type">Price Type</label>
                                <select id="price_type" name="price_type">
                                    <option value="fixed">Fixed</option>
                                    <option value="negotiable">Negotiable</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status_type">Status</label>
                                <select id="status_type" name="status_type">
                                    <option value="sale">For Sale</option>
                                    <option value="rent">For Rent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Property Details -->
                <div class="card">
                    <div class="card-header">
                        <h2>Property Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="property_type_id">Property Type</label>
                                <select id="property_type_id" name="property_type_id">
                                    <option value="">Select Type</option>
                                    <?php foreach ($propertyTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" <?php echo ($_POST['property_type_id'] ?? '') == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo sanitize($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo sanitize($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="bedrooms">Bedrooms</label>
                                <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo sanitize($_POST['bedrooms'] ?? '0'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="bathrooms">Bathrooms</label>
                                <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="<?php echo sanitize($_POST['bathrooms'] ?? '0'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="area_sqft">Area (sqft)</label>
                                <input type="number" id="area_sqft" name="area_sqft" min="0" value="<?php echo sanitize($_POST['area_sqft'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lot_size">Lot Size (sqft)</label>
                                <input type="number" id="lot_size" name="lot_size" min="0" value="<?php echo sanitize($_POST['lot_size'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="year_built">Year Built</label>
                                <input type="number" id="year_built" name="year_built" min="1800" max="<?php echo date('Y'); ?>" value="<?php echo sanitize($_POST['year_built'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="parking_spaces">Parking Spaces</label>
                                <input type="number" id="parking_spaces" name="parking_spaces" min="0" value="<?php echo sanitize($_POST['parking_spaces'] ?? '0'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="floors">Floors</label>
                                <input type="number" id="floors" name="floors" min="1" value="<?php echo sanitize($_POST['floors'] ?? '1'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="card">
                    <div class="card-header">
                        <h2>Location</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="address">Address <span class="required">*</span></label>
                                <input type="text" id="address" name="address" required value="<?php echo sanitize($_POST['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <input type="text" id="city" name="city" required value="<?php echo sanitize($_POST['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State <span class="required">*</span></label>
                                <input type="text" id="state" name="state" required value="<?php echo sanitize($_POST['state'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" value="<?php echo sanitize($_POST['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Features & Amenities -->
                <div class="card">
                    <div class="card-header">
                        <h2>Features & Amenities</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="features">Features</label>
                                <input type="text" id="features" name="features" value="<?php echo sanitize($_POST['features'] ?? ''); ?>">
                                <small>Comma-separated list (e.g., Pool, Garden, Fireplace)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="amenities">Amenities</label>
                                <input type="text" id="amenities" name="amenities" value="<?php echo sanitize($_POST['amenities'] ?? ''); ?>">
                                <small>Comma-separated list (e.g., Gym, Parking, Elevator)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Media -->
                <div class="card">
                    <div class="card-header">
                        <h2>Media</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>Property Images</label>
                                <div class="image-upload" onclick="document.getElementById('images').click()">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to upload images</p>
                                    <small>First image will be the featured image. Max 5MB each.</small>
                                </div>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" onchange="previewImages(this)">
                            </div>
                            
                            <div class="form-group">
                                <label for="virtual_tour_url">Virtual Tour URL</label>
                                <input type="url" id="virtual_tour_url" name="virtual_tour_url" value="<?php echo sanitize($_POST['virtual_tour_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="video_url">Video URL</label>
                                <input type="url" id="video_url" name="video_url" value="<?php echo sanitize($_POST['video_url'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="properties.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Property
                    </button>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        function previewImages(input) {
            if (input.files && input.files.length > 0) {
                const uploadDiv = input.previousElementSibling;
                uploadDiv.innerHTML = `<i class="fas fa-check-circle" style="color: var(--success);"></i><p>${input.files.length} image(s) selected</p>`;
            }
        }
    </script>
    <script src="../assets/js/agent-portal.js"></script>
</body>
</html>
