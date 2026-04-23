<?php
/**
 * Realty - Agent Properties Management
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';

// Check authentication
$auth = new Auth();
$auth->requireAgent();

// Initialize Property model
$propertyModel = new Property();

// Get agent properties
$properties = $propertyModel->getAgentProperties(current_user_id());

$pageTitle = 'My Properties';
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
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .card-body { padding: 1.5rem; }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
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
        
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
        
        .btn-success { background-color: var(--success); color: white; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-danger { background-color: var(--error); color: white; }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .table th {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-secondary);
        }
        
        .table tr:hover { background-color: var(--secondary); }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success { background-color: #f0fff4; color: var(--success); }
        .badge-warning { background-color: #fffaf0; color: var(--warning); }
        .badge-info { background-color: #ebf8ff; color: #3182ce; }
        .badge-error { background-color: #fff5f5; color: var(--error); }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #f0fff4;
            color: var(--success);
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background-color: #fff5f5;
            color: var(--error);
            border: 1px solid #fc8181;
        }
        
        .actions { display: flex; gap: 0.5rem; }
        
        .property-thumb {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: var(--radius-md);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.active { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="properties.php" class="active">
                <i class="fas fa-building"></i> My Properties
            </a>
            <a href="add-property.php">
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
    
    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <h1>My Properties</h1>
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
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo sanitize($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>All Properties</h2>
                    <a href="add-property.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($properties)): ?>
                        <div class="empty-state">
                            <i class="fas fa-building"></i>
                            <h3>No properties yet</h3>
                            <p>Start by adding your first property listing</p>
                            <a href="add-property.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Add Property
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Property</th>
                                        <th>Price</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Inquiries</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $images = $propertyModel->getImages($property['id']);
                                                $primaryImage = !empty($images) ? $images[0] : null;
                                                ?>
                                                <?php if ($primaryImage): ?>
                                                    <img src="<?php echo UPLOAD_URL . 'properties/' . $primaryImage['image_path']; ?>" alt="" class="property-thumb">
                                                <?php else: ?>
                                                    <div class="property-thumb" style="background: var(--secondary); display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image" style="color: var(--text-secondary);"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo sanitize(truncate($property['title'], 40)); ?></strong>
                                                <br>
                                                <small style="color: var(--text-secondary);">
                                                    <?php echo sanitize($property['city']) . ', ' . sanitize($property['state']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo format_price($property['price'], $property['status']); ?></td>
                                            <td><?php echo ucfirst($property['status']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $property['property_status'] === 'active' ? 'success' : 
                                                        ($property['property_status'] === 'pending' ? 'warning' : 
                                                        ($property['property_status'] === 'sold' ? 'info' : 'error')); 
                                                ?>">
                                                    <?php echo ucfirst($property['property_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($property['view_count']); ?></td>
                                            <td><?php echo number_format($property['inquiry_count']); ?></td>
                                            <td>
                                                <div class="actions">
                                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../property.php?slug=<?php echo $property['slug']; ?>" class="btn btn-sm btn-success" title="View" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="delete-property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this property?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
