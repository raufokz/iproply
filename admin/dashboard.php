<?php
/**
 * iProply - Admin Dashboard
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';
require_once '../includes/Inquiry.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Initialize models
$propertyModel = new Property();
$inquiryModel = new Inquiry();

// Get admin stats
$db = Database::getInstance();
$stats = $db->callProcedure('sp_get_admin_dashboard')[0][0];

// Get recent properties
$recentProperties = $propertyModel->getAll(['status' => 'pending'], 1, 5);

// Get recent inquiries
$recentInquiries = $inquiryModel->getRecent(5);

// Get pending agents
$pendingAgents = $db->select('agents', '*', 'status = :status', ['status' => 'pending'], 'created_at DESC', 5);

$pageTitle = 'Admin Dashboard';
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
            --info: #4299e1;
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
        
        .stat-card-icon.blue { background-color: #ebf8ff; color: var(--info); }
        .stat-card-icon.green { background-color: #f0fff4; color: var(--success); }
        .stat-card-icon.orange { background-color: #fffaf0; color: var(--warning); }
        .stat-card-icon.purple { background-color: #faf5ff; color: #805ad5; }
        
        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .stat-card-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
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
        
        .card-body { padding: 1.5rem; }
        
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
        .badge-info { background-color: #ebf8ff; color: var(--info); }
        .badge-error { background-color: #fff5f5; color: var(--error); }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); }
        
        .btn-success { background-color: var(--success); color: white; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-danger { background-color: var(--error); color: white; }
        
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
        
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.active { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr; }
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
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building"></i> Properties
            </a>
            <a href="agents.php">
                <i class="fas fa-users"></i> Agents
            </a>
            <a href="inquiries.php">
                <i class="fas fa-envelope"></i> Inquiries
            </a>
            <a href="blogs.php">
                <i class="fas fa-blog"></i> Blogs
            </a>
            <a href="pages.php">
                <i class="fas fa-file-lines"></i> Pages
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
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
                <h1>Admin Dashboard</h1>
            </div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name']); ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
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
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['total_agents']; ?></div>
                    <div class="stat-card-label">Total Agents</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon purple">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $stats['new_inquiries']; ?></div>
                    <div class="stat-card-label">New Inquiries</div>
                </div>
            </div>
            
            <div class="grid-2">
                <!-- Pending Properties -->
                <div class="card">
                    <div class="card-header">
                        <h2>Pending Properties</h2>
                        <a href="properties.php?status=pending" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProperties)): ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>No pending properties</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Agent</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProperties as $property): ?>
                                        <tr>
                                            <td><?php echo sanitize(truncate($property['title'], 30)); ?></td>
                                            <td><?php echo sanitize($property['agent_name']); ?></td>
                                            <td>
                                                <a href="approve-property.php?id=<?php echo $property['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pending Agents -->
                <div class="card">
                    <div class="card-header">
                        <h2>Pending Agents</h2>
                        <a href="agents.php?status=pending" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingAgents)): ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>No pending agents</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingAgents as $agent): ?>
                                        <tr>
                                            <td><?php echo sanitize($agent['first_name'] . ' ' . $agent['last_name']); ?></td>
                                            <td><?php echo sanitize($agent['email']); ?></td>
                                            <td>
                                                <a href="approve-agent.php?id=<?php echo $agent['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Inquiries -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Inquiries</h2>
                    <a href="inquiries.php" class="btn btn-primary">View All</a>
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
                                    <th>Agent</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInquiries as $inquiry): ?>
                                    <tr>
                                        <td><?php echo sanitize($inquiry['name']); ?></td>
                                        <td><?php echo sanitize(truncate($inquiry['property_title'], 25)); ?></td>
                                        <td><?php echo sanitize($inquiry['agent_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $inquiry['status'] === 'new' ? 'error' : ($inquiry['status'] === 'responded' ? 'success' : 'info'); ?>">
                                                <?php echo ucfirst($inquiry['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($inquiry['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
