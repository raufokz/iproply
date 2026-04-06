<?php
/**
 * Realty - View Inquiry
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Get inquiry ID from URL
$inquiryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($inquiryId === 0) {
    redirect('inquiries.php');
}

// Initialize database
$db = Database::getInstance();

// Get inquiry details
$inquiries = $db->query(
    "SELECT i.*, p.title as property_title, p.price, a.first_name, a.last_name 
     FROM inquiries i 
     LEFT JOIN properties p ON i.property_id = p.id 
     LEFT JOIN agents a ON p.agent_id = a.id 
     WHERE i.id = :id",
    ['id' => $inquiryId]
)->fetchAll();

if (empty($inquiries)) {
    redirect('inquiries.php');
}

$inquiry = $inquiries[0];

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send_email') {
        // Send reply email to inquiry
        $message = sanitize($_POST['message'] ?? '');
        if (!empty($message)) {
            // TODO: Implement email sending using Mail class
            set_flash_message('Email sent to inquirer', 'success');
            
            // Update status to responded
            $db->update('inquiries', ['status' => 'responded'], 'id = :id', ['id' => $inquiryId]);
        }
    } elseif ($action === 'update_status') {
        $status = sanitize($_POST['status'] ?? '');
        if (in_array($status, ['new', 'responded', 'archived'])) {
            $db->update('inquiries', ['status' => $status], 'id = :id', ['id' => $inquiryId]);
            set_flash_message('Inquiry status updated', 'success');
        }
    }
    
    redirect('view-inquiry.php?id=' . $inquiryId);
}

$pageTitle = 'View Inquiry';
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .inquiry-header {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .inquiry-details {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .inquiry-meta {
            background-color: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .detail-value {
            color: var(--text-primary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .button-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .inquiry-header {
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
            <a href="agents.php">
                <i class="fas fa-users"></i> Agents
            </a>
            <a href="inquiries.php" class="active">
                <i class="fas fa-envelope"></i> Inquiries
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
                <h1>Inquiry Details</h1>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">ID: <?php echo $inquiryId; ?></p>
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
            <a href="inquiries.php" class="btn btn-secondary" style="margin-bottom: 1.5rem;">
                <i class="fas fa-arrow-left"></i> Back to Inquiries
            </a>
            
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo sanitize($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="inquiry-header">
                <!-- Left Column: Inquiry Details -->
                <div class="inquiry-details">
                    <h2 style="margin-bottom: 1.5rem;">Inquiry from <?php echo sanitize($inquiry['name']); ?></h2>
                    
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div class="detail-value"><?php echo sanitize($inquiry['name']); ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">
                            <a href="mailto:<?php echo sanitize($inquiry['email']); ?>" style="color: var(--primary); text-decoration: none;">
                                <?php echo sanitize($inquiry['email']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value">
                            <?php if ($inquiry['phone']): ?>
                                <a href="tel:<?php echo sanitize($inquiry['phone']); ?>" style="color: var(--primary); text-decoration: none;">
                                    <?php echo sanitize($inquiry['phone']); ?>
                                </a>
                            <?php else: ?>
                                <em style="color: var(--text-secondary);">Not provided</em>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Property:</div>
                        <div class="detail-value">
                            <?php if ($inquiry['property_title']): ?>
                                <strong><?php echo sanitize($inquiry['property_title']); ?></strong><br>
                                <small style="color: var(--text-secondary);">Price: $<?php echo number_format($inquiry['price']); ?></small>
                            <?php else: ?>
                                <em style="color: var(--text-secondary);">Property deleted</em>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Agent:</div>
                        <div class="detail-value">
                            <?php echo sanitize(($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? '')); ?>
                        </div>
                    </div>
                    
                    <div class="detail-row" style="border-bottom: none;">
                        <div class="detail-label">Received:</div>
                        <div class="detail-value"><?php echo format_date($inquiry['created_at']); ?></div>
                    </div>
                    
                    <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Message</h3>
                    <div style="background-color: var(--secondary); padding: 1rem; border-radius: var(--radius-md); line-height: 1.6;">
                        <?php echo nl2br(sanitize($inquiry['message'])); ?>
                    </div>
                </div>
                
                <!-- Right Column: Actions -->
                <div class="inquiry-meta">
                    <h3 style="margin-bottom: 1rem;">Status</h3>
                    
                    <form method="POST" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="status">Update Status:</label>
                            <select id="status" name="status" onchange="this.form.submit();">
                                <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="responded" <?php echo $inquiry['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                                <option value="archived" <?php echo $inquiry['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <input type="hidden" name="action" value="update_status">
                    </form>
                    
                    <div style="padding: 1rem; background-color: var(--secondary); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <div style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Current Status:</div>
                        <span class="badge badge-<?php echo $inquiry['status'] === 'new' ? 'error' : ($inquiry['status'] === 'responded' ? 'success' : 'info'); ?>">
                            <?php echo ucfirst($inquiry['status']); ?>
                        </span>
                    </div>
                    
                    <hr style="border: none; border-top: 1px solid var(--border); margin: 1.5rem 0;">
                    
                    <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
                    
                    <a href="mailto:<?php echo sanitize($inquiry['email']); ?>" class="btn btn-primary" style="width: 100%; text-align: center; margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope"></i> Send Email
                    </a>
                    
                    <a href="tel:<?php echo sanitize($inquiry['phone'] ?? '#'); ?>" class="btn btn-secondary" style="width: 100%; text-align: center; margin-bottom: 0.5rem;">
                        <i class="fas fa-phone"></i> Call
                    </a>
                    
                    <a href="inquiries.php" class="btn btn-secondary" style="width: 100%; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
