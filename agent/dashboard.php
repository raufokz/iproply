<?php
/**
 * Realty - Agent Dashboard
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';
require_once '../includes/Inquiry.php';

// Check authentication
$auth = new Auth();
$auth->requireAgent();

// Initialize models
$propertyModel = new Property();
$inquiryModel = new Inquiry();

// Get agent stats
$db = Database::getInstance();
$stats = $db->callProcedure('sp_get_agent_dashboard', [current_user_id()])[0][0];

// Get recent properties
$recentProperties = $propertyModel->getAgentProperties(current_user_id());
$recentProperties = array_slice($recentProperties, 0, 5);

// Get recent inquiries
$recentInquiries = $inquiryModel->getRecent(5, current_user_id());

$pageTitle = 'Agent Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --info: #4299e1;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius-md: 8px;
            --radius-lg: 12px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--secondary);
            color: var(--text-primary);
        }
        
        /* Sidebar */
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
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
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
        
        .sidebar-nav i {
            width: 20px;
            text-align: center;
        }
        
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
        
        /* Main Content */
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
        
        .user-info {
            text-align: right;
        }
        
        .user-info .name {
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .user-info .role {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
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
        
        .content {
            padding: 2rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-card-icon.blue {
            background-color: #ebf8ff;
            color: var(--info);
        }
        
        .stat-card-icon.green {
            background-color: #f0fff4;
            color: var(--success);
        }
        
        .stat-card-icon.orange {
            background-color: #fffaf0;
            color: var(--warning);
        }
        
        .stat-card-icon.purple {
            background-color: #faf5ff;
            color: #805ad5;
        }
        
        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .stat-card-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        /* Cards */
        .card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
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
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Tables */
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
        
        .table tr:hover {
            background-color: var(--secondary);
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #f0fff4;
            color: var(--success);
        }
        
        .badge-warning {
            background-color: #fffaf0;
            color: var(--warning);
        }
        
        .badge-info {
            background-color: #ebf8ff;
            color: var(--info);
        }
        
        .badge-error {
            background-color: #fff5f5;
            color: var(--error);
        }
        
        /* Buttons */
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-outline:hover {
            background-color: var(--secondary);
        }
        
        /* Alerts */
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
        
        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        /* Empty State */
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
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
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
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building"></i>
                My Properties
            </a>
            <a href="add-property.php">
                <i class="fas fa-plus-circle"></i>
                Add Property
            </a>
            <a href="inquiries.php">
                <i class="fas fa-envelope"></i>
                Inquiries
                <?php if ($stats['new_inquiries'] > 0): ?>
                    <span class="badge badge-error" style="margin-left: auto;"><?php echo $stats['new_inquiries']; ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php">
                <i class="fas fa-user"></i>
                Profile
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>">
                <i class="fas fa-arrow-left"></i>
                Back to Website
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-title">
                <h1>Dashboard</h1>
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
        
        <!-- Content -->
        <main class="content">
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo sanitize($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon blue">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['total_properties']; ?></div>
                    <div class="stat-card-label">Total Properties</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['active_properties']; ?></div>
                    <div class="stat-card-label">Active Listings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon orange">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['total_inquiries']; ?></div>
                    <div class="stat-card-label">Total Inquiries</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon purple">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['new_inquiries']; ?></div>
                    <div class="stat-card-label">New Inquiries</div>
                </div>
            </div>
            
            <div class="grid-2">
                <!-- Recent Properties -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Properties</h2>
                        <a href="properties.php" class="btn-sm" style="color: var(--primary);">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProperties)): ?>
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <p>No properties yet</p>
                                <a href="add-property.php" class="btn btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-plus"></i> Add Property
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProperties as $property): ?>
                                        <tr>
                                            <td><?php echo sanitize(truncate($property['title'], 30)); ?></td>
                                            <td><?php echo format_price($property['price'], $property['status']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $property['property_status'] === 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($property['property_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Inquiries -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Inquiries</h2>
                        <a href="inquiries.php" class="btn-sm" style="color: var(--primary);">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentInquiries)): ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope"></i>
                                <p>No inquiries yet</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Property</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentInquiries as $inquiry): ?>
                                        <tr>
                                            <td><?php echo sanitize($inquiry['name']); ?></td>
                                            <td><?php echo sanitize(truncate($inquiry['property_title'], 25)); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $inquiry['status'] === 'new' ? 'error' : ($inquiry['status'] === 'responded' ? 'success' : 'info'); ?>">
                                                    <?php echo ucfirst($inquiry['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="add-property.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Property
                        </a>
                        <a href="properties.php" class="btn btn-outline">
                            <i class="fas fa-list"></i> Manage Properties
                        </a>
                        <a href="inquiries.php" class="btn btn-outline">
                            <i class="fas fa-envelope"></i> View Inquiries
                        </a>
                        <a href="profile.php" class="btn btn-outline">
                            <i class="fas fa-user"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
