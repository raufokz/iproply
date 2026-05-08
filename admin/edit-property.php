<?php
/**
 * iProply - Admin property editor.
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';
require_once '../includes/Upload.php';

$auth = new Auth();
$auth->requireAdmin();

$propertyModel = new Property();
$upload = new Upload();

$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($propertyId <= 0) {
    set_flash_message('error', 'Invalid property.');
    redirect('admin/properties.php');
}

$property = $propertyModel->getById($propertyId);
if (!$property) {
    set_flash_message('error', 'Property not found.');
    redirect('admin/properties.php');
}

$propertyTypes = $propertyModel->getPropertyTypes();
$categories = $propertyModel->getCategories();
$images = $propertyModel->getImages($propertyId);
$errors = [];

$csvFromJsonField = static function (?string $raw): string {
    if ($raw === null || $raw === '') {
        return '';
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? implode(', ', $decoded) : $raw;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_POST = [
        'title' => $property['title'],
        'description' => $property['description'],
        'short_description' => $property['short_description'] ?? '',
        'price' => $property['price'],
        'price_type' => $property['price_type'] ?? 'fixed',
        'status_type' => $property['status'] ?? 'sale',
        'property_status' => $property['property_status'] ?? Property::STATUS_PENDING,
        'is_featured' => $property['is_featured'] ?? 0,
        'bedrooms' => $property['bedrooms'],
        'bathrooms' => $property['bathrooms'],
        'area_sqft' => $property['area_sqft'],
        'lot_size' => $property['lot_size'],
        'year_built' => $property['year_built'],
        'parking_spaces' => $property['parking_spaces'],
        'floors' => $property['floors'],
        'address' => $property['address'],
        'city' => $property['city'],
        'state' => $property['state'],
        'zip_code' => $property['zip_code'] ?? '',
        'property_type_id' => $property['property_type_id'] ?? '',
        'category_id' => $property['category_id'] ?? '',
        'features' => $csvFromJsonField($property['features'] ?? ''),
        'amenities' => $csvFromJsonField($property['amenities'] ?? ''),
        'virtual_tour_url' => $property['virtual_tour_url'] ?? '',
        'video_url' => $property['video_url'] ?? '',
        'admin_notes' => $property['admin_notes'] ?? '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        $errors[] = 'Invalid request. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'delete_property') {
        if ($propertyModel->delete($propertyId)) {
            set_flash_message('success', 'Property deleted and images cleaned up.');
            redirect('admin/properties.php');
        }

        set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to delete property.');
        redirect('admin/edit-property.php?id=' . $propertyId);
    } elseif (isset($_POST['delete_image_id'])) {
        if ($propertyModel->deleteImage((int) $_POST['delete_image_id'], $propertyId)) {
            set_flash_message('success', 'Image deleted.');
        } else {
            set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to delete image.');
        }
        redirect('admin/edit-property.php?id=' . $propertyId);
    } elseif (isset($_POST['set_primary_image_id'])) {
        if ($propertyModel->setPrimaryImage($propertyId, (int) $_POST['set_primary_image_id'])) {
            set_flash_message('success', 'Featured image updated.');
        } else {
            set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to update featured image.');
        }
        redirect('admin/edit-property.php?id=' . $propertyId);
    } elseif (($_POST['action'] ?? '') === 'reorder_images') {
        $orders = is_array($_POST['image_order'] ?? null) ? $_POST['image_order'] : [];
        if ($propertyModel->reorderImages($propertyId, $orders)) {
            set_flash_message('success', 'Image order saved.');
        } else {
            set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to reorder images.');
        }
        redirect('admin/edit-property.php?id=' . $propertyId);
    } else {
        foreach (['title', 'description', 'price', 'address', 'city', 'state'] as $field) {
            if (trim((string) ($_POST[$field] ?? '')) === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        $workflowStatus = Property::normalizeWorkflowStatus($_POST['property_status'] ?? Property::STATUS_PENDING);

        if (empty($errors)) {
            $propertyData = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'price' => (float) $_POST['price'],
                'price_type' => $_POST['price_type'] ?? 'fixed',
                'status' => $_POST['status_type'] ?? 'sale',
                'property_status' => $workflowStatus,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'bedrooms' => (int) ($_POST['bedrooms'] ?? 0),
                'bathrooms' => (float) ($_POST['bathrooms'] ?? 0),
                'area_sqft' => (float) ($_POST['area_sqft'] ?? 0),
                'lot_size' => (float) ($_POST['lot_size'] ?? 0),
                'year_built' => trim((string) ($_POST['year_built'] ?? '')) !== '' ? (int) $_POST['year_built'] : null,
                'parking_spaces' => (int) ($_POST['parking_spaces'] ?? 0),
                'floors' => (int) ($_POST['floors'] ?? 1),
                'address' => trim($_POST['address']),
                'city' => trim($_POST['city']),
                'state' => trim($_POST['state']),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'property_type_id' => !empty($_POST['property_type_id']) ? (int) $_POST['property_type_id'] : null,
                'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
                'features' => !empty($_POST['features']) ? json_encode(array_map('trim', explode(',', $_POST['features']))) : null,
                'amenities' => !empty($_POST['amenities']) ? json_encode(array_map('trim', explode(',', $_POST['amenities']))) : null,
                'virtual_tour_url' => trim($_POST['virtual_tour_url'] ?? ''),
                'video_url' => trim($_POST['video_url'] ?? ''),
                'admin_notes' => trim($_POST['admin_notes'] ?? ''),
            ];

            if ($propertyModel->update($propertyId, $propertyData)) {
                $propertyModel->updateStatus($propertyId, $workflowStatus, current_user_id());

                if (!empty($_FILES['images']['name'][0])) {
                    $uploadedImages = $upload->uploadMultiple($_FILES['images'], 'properties', [
                        'resize' => true,
                        'thumbnail' => true,
                    ]);

                    foreach ($uploadedImages as $imageData) {
                        $propertyModel->addImage($propertyId, $imageData, false);
                    }

                    if (!empty($upload->getErrors())) {
                        set_flash_message('error', 'Property saved, but some images were skipped: ' . implode(' ', $upload->getErrors()));
                    }

                    if (!empty($upload->getWarnings())) {
                        set_flash_message('warning', implode(' ', $upload->getWarnings()));
                    }
                }

                set_flash_message('success', 'Property updated.');
                redirect('admin/edit-property.php?id=' . $propertyId);
            }

            $errors = array_merge($errors, $propertyModel->getErrors());
        }
    }
}

$pageTitle = 'Edit Property';
$csrfToken = generate_csrf_token();
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
        .editor-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group input, .form-group select, .form-group textarea {
            padding: 0.75rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font: inherit;
        }
        .form-group textarea { min-height: 140px; resize: vertical; }
        .image-manager { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
        .image-item { border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.75rem; }
        .image-item img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 0.75rem; }
        .image-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .image-order { width: 70px; }
        .btn-row { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem; }
        .btn-danger { background: var(--error); color: #fff; }
        @media (max-width: 960px) { .editor-grid, .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="properties.php" class="active"><i class="fas fa-building"></i> Properties</a>
            <a href="agents.php"><i class="fas fa-users"></i> Agents</a>
            <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
            <a href="blogs.php"><i class="fas fa-blog"></i> Blogs</a>
            <a href="pages.php"><i class="fas fa-file-lines"></i> Pages</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="sidebar-footer">
            <a href="properties.php"><i class="fas fa-arrow-left"></i> Back to Properties</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title"><h1>Edit Property</h1></div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name']); ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            </div>
        </header>

        <main class="content">
            <?php foreach (get_flash_messages() as $message): ?>
                <div class="alert alert-<?php echo sanitize($message['type']); ?>"><?php echo sanitize($message['message']); ?></div>
            <?php endforeach; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo sanitize($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">

                <div class="editor-grid">
                    <div>
                        <div class="card">
                            <div class="card-header"><h2>Listing Details</h2></div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="title">Title *</label>
                                        <input id="title" name="title" required value="<?php echo sanitize($_POST['title'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group full-width">
                                        <label for="description">Description *</label>
                                        <textarea id="description" name="description" required><?php echo sanitize($_POST['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group full-width">
                                        <label for="short_description">Short Description</label>
                                        <input id="short_description" name="short_description" value="<?php echo sanitize($_POST['short_description'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="price">Price *</label>
                                        <input id="price" name="price" type="number" min="0" step="0.01" required value="<?php echo sanitize($_POST['price'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="status_type">Listing Type</label>
                                        <select id="status_type" name="status_type">
                                            <option value="sale" <?php echo ($_POST['status_type'] ?? '') === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                            <option value="rent" <?php echo ($_POST['status_type'] ?? '') === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="price_type">Price Type</label>
                                        <select id="price_type" name="price_type">
                                            <option value="fixed" <?php echo ($_POST['price_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
                                            <option value="negotiable" <?php echo ($_POST['price_type'] ?? '') === 'negotiable' ? 'selected' : ''; ?>>Negotiable</option>
                                            <option value="auction" <?php echo ($_POST['price_type'] ?? '') === 'auction' ? 'selected' : ''; ?>>Auction</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="property_status">Workflow Status</label>
                                        <select id="property_status" name="property_status">
                                            <?php foreach (Property::ADMIN_STATUSES as $workflowStatus): ?>
                                                <option value="<?php echo $workflowStatus; ?>" <?php echo ($_POST['property_status'] ?? '') === $workflowStatus ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($workflowStatus); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="property_type_id">Property Type</label>
                                        <select id="property_type_id" name="property_type_id">
                                            <option value="">Select Type</option>
                                            <?php foreach ($propertyTypes as $type): ?>
                                                <option value="<?php echo (int) $type['id']; ?>" <?php echo (string) ($_POST['property_type_id'] ?? '') === (string) $type['id'] ? 'selected' : ''; ?>>
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
                                                <option value="<?php echo (int) $category['id']; ?>" <?php echo (string) ($_POST['category_id'] ?? '') === (string) $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo sanitize($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h2>Location and Specs</h2></div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="address">Address *</label>
                                        <input id="address" name="address" required value="<?php echo sanitize($_POST['address'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group"><label for="city">City *</label><input id="city" name="city" required value="<?php echo sanitize($_POST['city'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="state">State *</label><input id="state" name="state" required value="<?php echo sanitize($_POST['state'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="zip_code">ZIP Code</label><input id="zip_code" name="zip_code" value="<?php echo sanitize($_POST['zip_code'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="bedrooms">Bedrooms</label><input id="bedrooms" name="bedrooms" type="number" min="0" value="<?php echo sanitize($_POST['bedrooms'] ?? '0'); ?>"></div>
                                    <div class="form-group"><label for="bathrooms">Bathrooms</label><input id="bathrooms" name="bathrooms" type="number" min="0" step="0.5" value="<?php echo sanitize($_POST['bathrooms'] ?? '0'); ?>"></div>
                                    <div class="form-group"><label for="area_sqft">Area Sqft</label><input id="area_sqft" name="area_sqft" type="number" min="0" value="<?php echo sanitize($_POST['area_sqft'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="lot_size">Lot Size</label><input id="lot_size" name="lot_size" type="number" min="0" step="0.01" value="<?php echo sanitize($_POST['lot_size'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="year_built">Year Built</label><input id="year_built" name="year_built" type="number" min="1800" max="<?php echo date('Y') + 2; ?>" value="<?php echo sanitize($_POST['year_built'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="parking_spaces">Parking Spaces</label><input id="parking_spaces" name="parking_spaces" type="number" min="0" value="<?php echo sanitize($_POST['parking_spaces'] ?? '0'); ?>"></div>
                                    <div class="form-group"><label for="floors">Floors</label><input id="floors" name="floors" type="number" min="1" value="<?php echo sanitize($_POST['floors'] ?? '1'); ?>"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h2>Features and Media URLs</h2></div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <div class="form-group"><label for="features">Features</label><input id="features" name="features" value="<?php echo sanitize($_POST['features'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="amenities">Amenities</label><input id="amenities" name="amenities" value="<?php echo sanitize($_POST['amenities'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="virtual_tour_url">Virtual Tour URL</label><input id="virtual_tour_url" name="virtual_tour_url" type="url" value="<?php echo sanitize($_POST['virtual_tour_url'] ?? ''); ?>"></div>
                                    <div class="form-group"><label for="video_url">Video URL</label><input id="video_url" name="video_url" type="url" value="<?php echo sanitize($_POST['video_url'] ?? ''); ?>"></div>
                                    <div class="form-group full-width"><label for="admin_notes">Admin Notes</label><textarea id="admin_notes" name="admin_notes"><?php echo sanitize($_POST['admin_notes'] ?? ''); ?></textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card">
                            <div class="card-header"><h2>Publishing</h2></div>
                            <div class="card-body">
                                <label style="display:flex;gap:0.5rem;align-items:center;margin-bottom:1rem;">
                                    <input type="checkbox" name="is_featured" value="1" <?php echo !empty($_POST['is_featured']) ? 'checked' : ''; ?>>
                                    Featured listing
                                </label>
                                <div class="btn-row">
                                    <a href="properties.php" class="btn btn-outline">Cancel</a>
                                    <button type="submit" name="action" value="delete_property" class="btn btn-danger" formnovalidate onclick="return confirm('Permanently delete this property and all of its images?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Property</button>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h2>Images</h2></div>
                            <div class="card-body">
                                <?php if (!empty($images)): ?>
                                    <div class="image-manager">
                                        <?php foreach ($images as $image): ?>
                                            <div class="image-item">
                                                <img src="<?php echo property_image_url($image['image_path']); ?>" alt="Property image">
                                                <?php if (!empty($image['is_primary'])): ?>
                                                    <span class="badge badge-success">Featured</span>
                                                <?php endif; ?>
                                                <div class="image-actions" style="margin-top:0.5rem;">
                                                    <label>Order <input class="image-order" type="number" name="image_order[<?php echo (int) $image['id']; ?>]" min="0" value="<?php echo (int) $image['display_order']; ?>"></label>
                                                    <?php if (empty($image['is_primary'])): ?>
                                                        <button type="submit" name="set_primary_image_id" value="<?php echo (int) $image['id']; ?>" class="btn btn-outline" formnovalidate><i class="fas fa-star"></i></button>
                                                    <?php endif; ?>
                                                    <button type="submit" name="delete_image_id" value="<?php echo (int) $image['id']; ?>" class="btn btn-outline" formnovalidate onclick="return confirm('Delete this image?');"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="submit" name="action" value="reorder_images" class="btn btn-outline" formnovalidate style="margin-top:1rem;">
                                        <i class="fas fa-sort"></i> Save Image Order
                                    </button>
                                <?php else: ?>
                                    <div class="empty-state" style="padding:1rem 0;">No images yet.</div>
                                <?php endif; ?>

                                <div class="form-group" style="margin-top:1rem;">
                                    <label for="images">Upload Images</label>
                                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
