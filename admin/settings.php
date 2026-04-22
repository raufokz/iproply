<?php
/**
 * Realty - Admin Settings
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Initialize database
$db = Database::getInstance();

// Get current settings
$settingsResult = $db->select('site_settings', '*');
$settings = !empty($settingsResult) ? $settingsResult[0] : [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    // Prepare update data
    $updateData = [];
    $allowedFields = ['site_name', 'site_tagline', 'site_email', 'site_phone', 'site_address', 'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'meta_title', 'meta_description', 'meta_keywords', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption'];
    
    foreach ($allowedFields as $field) {
        if (isset($_POST[$field])) {
            $updateData[$field] = sanitize($_POST[$field]);
        }
    }
    
    if (!empty($updateData)) {
        $db->update('site_settings', $updateData, '1 = 1');
        set_flash_message('Settings updated successfully', 'success');
        redirect('settings.php');
    }
}

$pageTitle = 'Settings';
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
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font: 0.875rem 'Inter', sans-serif;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 59, 90, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h3 {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
            font-size: 1.125rem;
        }
        
        .button-group {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 2rem;
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
            <a href="inquiries.php">
                <i class="fas fa-envelope"></i> Inquiries
            </a>
            <a href="blogs.php">
                <i class="fas fa-blog"></i> Blogs
            </a>
            <a href="settings.php" class="active">
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
                <h1>Settings</h1>
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
            
            <form method="POST" class="card">
                <div class="card-header">
                    <h2>General Settings</h2>
                </div>
                <div class="card-body">
                    <!-- General Settings Section -->
                    <div class="section">
                        <h3><i class="fas fa-cog"></i> Site Configuration</h3>
                        
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo sanitize($settings['site_name'] ?? APP_NAME); ?>">
                            <small>The name of your real estate platform</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_tagline">Site Tagline</label>
                            <input type="text" id="site_tagline" name="site_tagline" value="<?php echo sanitize($settings['site_tagline'] ?? ''); ?>">
                            <small>Your site's tagline or motto</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_email">Site Email</label>
                            <input type="email" id="site_email" name="site_email" value="<?php echo sanitize($settings['site_email'] ?? ''); ?>">
                            <small>Primary contact email for inquiries</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_phone">Contact Phone</label>
                            <input type="tel" id="site_phone" name="site_phone" value="<?php echo sanitize($settings['site_phone'] ?? ''); ?>">
                            <small>Main contact phone number</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_address">Business Address</label>
                            <textarea id="site_address" name="site_address"><?php echo sanitize($settings['site_address'] ?? ''); ?></textarea>
                            <small>Your physical business address</small>
                        </div>
                    </div>
                    
                    <!-- Email Settings Section -->
                    <div class="section">
                        <h3><i class="fas fa-envelope"></i> Email (SMTP) Configuration</h3>
                        
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" placeholder="mail.yoursite.com" value="<?php echo sanitize($settings['smtp_host'] ?? ''); ?>">
                            <small>Your SMTP server address</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" placeholder="587" value="<?php echo sanitize($settings['smtp_port'] ?? '587'); ?>">
                            <small>Typically 587 for TLS or 465 for SSL</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" value="<?php echo sanitize($settings['smtp_username'] ?? ''); ?>">
                            <small>Your SMTP account username</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" value="<?php echo sanitize($settings['smtp_password'] ?? ''); ?>">
                            <small>Your SMTP account password (shown for reference only)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_encryption">SMTP Encryption</label>
                            <select id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                            <small>Encryption method for SMTP</small>
                        </div>
                    </div>
                    
                    <!-- Social Media Section -->
                    <div class="section">
                        <h3><i class="fas fa-share-alt"></i> Social Media</h3>
                        
                        <div class="form-group">
                            <label for="facebook_url">Facebook URL</label>
                            <input type="url" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/yourpage" value="<?php echo sanitize($settings['facebook_url'] ?? ''); ?>">
                            <small>Your Facebook business page URL</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="twitter_url">Twitter URL</label>
                            <input type="url" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/yourhandle" value="<?php echo sanitize($settings['twitter_url'] ?? ''); ?>">
                            <small>Your Twitter profile URL</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="instagram_url">Instagram URL</label>
                            <input type="url" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/yourprofile" value="<?php echo sanitize($settings['instagram_url'] ?? ''); ?>">
                            <small>Your Instagram profile URL</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="linkedin_url">LinkedIn URL</label>
                            <input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/company/yourcompany" value="<?php echo sanitize($settings['linkedin_url'] ?? ''); ?>">
                            <small>Your LinkedIn company page URL</small>
                        </div>
                    </div>
                    
                    <!-- SEO & Meta Section -->
                    <div class="section">
                        <h3><i class="fas fa-search"></i> SEO & Meta Information</h3>
                        
                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" value="<?php echo sanitize($settings['meta_title'] ?? ''); ?>">
                            <small>Page title for search engines (should be 50-60 characters)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description"><?php echo sanitize($settings['meta_description'] ?? ''); ?></textarea>
                            <small>Page description for search engines (should be 120-160 characters)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <textarea id="meta_keywords" name="meta_keywords"><?php echo sanitize($settings['meta_keywords'] ?? ''); ?></textarea>
                            <small>Comma-separated keywords relevant to your business</small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <input type="hidden" name="action" value="update_settings">
                    <div class="button-group">
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
