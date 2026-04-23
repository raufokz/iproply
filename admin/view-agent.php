<?php
/**
 * Realty - View Agent Profile
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Get agent ID from URL
$agentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($agentId === 0) {
    redirect('agents.php');
}

// Initialize database
$db = Database::getInstance();

// Get agent details
$agents = $db->select('agents', '*', 'id = :id', ['id' => $agentId]);

if (empty($agents)) {
    redirect('agents.php');
}

$agent = $agents[0];

// Get agent's properties count
$properties = $db->select('properties', 'COUNT(*) as count', 'agent_id = :id', ['id' => $agentId]);
$propertiesCount = $properties[0]['count'] ?? 0;

$pageTitle = 'View Agent Profile';
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
        .profile-header {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar-section {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .profile-info {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .info-item {
            padding: 1rem;
            background-color: var(--secondary);
            border-radius: var(--radius-md);
        }
        
        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 0.95rem;
            color: var(--text-primary);
            word-break: break-word;
        }
        
        .section {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .section h3 {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }
        
        .button-group {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-start;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
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
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building"></i> Properties
            </a>
            <a href="agents.php" class="active">
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
                <h1>Agent Profile</h1>
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
            <a href="agents.php" class="btn btn-secondary" style="margin-bottom: 1.5rem;">
                <i class="fas fa-arrow-left"></i> Back to Agents
            </a>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <!-- Avatar Section -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        <?php if (!empty($agent['avatar'])): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . sanitize($agent['avatar']); ?>" alt="<?php echo sanitize($agent['first_name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <span class="badge badge-<?php echo $agent['status'] === 'active' ? 'success' : ($agent['status'] === 'pending' ? 'warning' : 'info'); ?>">
                        <?php echo ucfirst($agent['status']); ?>
                    </span>
                </div>
                
                <!-- Profile Info -->
                <div class="profile-info">
                    <div class="profile-name"><?php echo sanitize($agent['first_name'] . ' ' . $agent['last_name']); ?></div>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        <i class="fas fa-license"></i> License: <?php echo sanitize($agent['license_number']); ?>
                    </p>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><a href="mailto:<?php echo sanitize($agent['email']); ?>" style="color: var(--primary);"><?php echo sanitize($agent['email']); ?></a></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><a href="tel:<?php echo sanitize($agent['phone']); ?>" style="color: var(--primary);"><?php echo sanitize($agent['phone']); ?></a></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Mobile</div>
                            <div class="info-value"><?php echo sanitize($agent['mobile']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Experience</div>
                            <div class="info-value"><?php echo sanitize($agent['years_experience']); ?> years</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Information -->
            <div class="section">
                <h3><i class="fas fa-briefcase"></i> Professional Information</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Specialties</div>
                        <div class="info-value"><?php echo sanitize($agent['specialties']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Total Properties</div>
                        <div class="info-value" style="font-size: 1.5rem; font-weight: 700;"><?php echo $propertiesCount; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Location Information -->
            <div class="section">
                <h3><i class="fas fa-map-marker-alt"></i> Location Information</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo sanitize($agent['address']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">City</div>
                        <div class="info-value"><?php echo sanitize($agent['city']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">State</div>
                        <div class="info-value"><?php echo sanitize($agent['state']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Zip Code</div>
                        <div class="info-value"><?php echo sanitize($agent['zip_code']); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Social Information -->
            <?php if (!empty($agent['website']) || !empty($agent['facebook']) || !empty($agent['twitter']) || !empty($agent['instagram']) || !empty($agent['linkedin'])): ?>
                <div class="section">
                    <h3><i class="fas fa-share-alt"></i> Social & Web</h3>
                    
                    <div class="info-grid">
                        <?php if (!empty($agent['website'])): ?>
                            <div class="info-item">
                                <div class="info-label">Website</div>
                                <div class="info-value"><a href="<?php echo sanitize($agent['website']); ?>" target="_blank" style="color: var(--primary);"><?php echo sanitize($agent['website']); ?></a></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['facebook'])): ?>
                            <div class="info-item">
                                <div class="info-label">Facebook</div>
                                <div class="info-value"><a href="<?php echo sanitize($agent['facebook']); ?>" target="_blank" style="color: var(--primary);"><i class="fab fa-facebook"></i> Facebook</a></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['twitter'])): ?>
                            <div class="info-item">
                                <div class="info-label">Twitter</div>
                                <div class="info-value"><a href="<?php echo sanitize($agent['twitter']); ?>" target="_blank" style="color: var(--primary);"><i class="fab fa-twitter"></i> Twitter</a></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['instagram'])): ?>
                            <div class="info-item">
                                <div class="info-label">Instagram</div>
                                <div class="info-value"><a href="<?php echo sanitize($agent['instagram']); ?>" target="_blank" style="color: var(--primary);"><i class="fab fa-instagram"></i> Instagram</a></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['linkedin'])): ?>
                            <div class="info-item">
                                <div class="info-label">LinkedIn</div>
                                <div class="info-value"><a href="<?php echo sanitize($agent['linkedin']); ?>" target="_blank" style="color: var(--primary);"><i class="fab fa-linkedin"></i> LinkedIn</a></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
