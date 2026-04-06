<?php
/**
 * Realty - Agent Registration
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Redirect if already logged in
if (is_agent()) {
    redirect('agent/dashboard.php');
}

$auth = new Auth();
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->agentRegister($_POST)) {
        $successMessage = 'Registration successful! Your account is pending approval. An admin will review your profile soon.';
        // Clear form data after success
        $_POST = [];
    } else {
        $errors = $auth->getErrors();
    }
}

$pageTitle = 'Agent Register';
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
            --error: #e53e3e;
            --success: #48bb78;
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius-lg: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-container { width: 100%; max-width: 650px; }
        .login-card { background: white; border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: var(--shadow-lg); }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
        .login-header p { color: var(--text-secondary); font-size: 0.875rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: var(--primary); }
        .btn { width: 100%; padding: 1rem; background-color: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn:hover { background-color: var(--primary-light); }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem; }
        .alert-error { background-color: #fff5f5; color: var(--error); border: 1px solid #fc8181; }
        .alert-success { background-color: #ecfdf5; color: #0f766e; border: 1px solid #6ee7b7; }
        .login-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); }
        .login-footer a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .login-footer a:hover { text-decoration: underline; }
        .logo { text-align: center; margin-bottom: 2rem; }
        .logo a { font-size: 2rem; font-weight: 700; color: white; text-decoration: none; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-row.full { grid-template-columns: 1fr; }
        @media (max-width: 768px) {
            .login-container { max-width: 100%; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>
        <div class="login-card">
            <div class="login-header">
                <h1>Join Our Agent Network</h1>
                <p>Complete your profile to get started as a real estate agent</p>
            </div>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo sanitize($successMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo sanitize($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- Personal Information -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required value="<?php echo sanitize($_POST['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required value="<?php echo sanitize($_POST['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Account Information</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required value="<?php echo sanitize($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Contact Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo sanitize($_POST['mobile'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Professional Information</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="license_number">License Number</label>
                            <input type="text" id="license_number" name="license_number" value="<?php echo sanitize($_POST['license_number'] ?? ''); ?>" placeholder="e.g., RE-12345">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="years_experience">Years of Experience</label>
                            <input type="number" id="years_experience" name="years_experience" min="0" value="<?php echo sanitize($_POST['years_experience'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="specialties">Specialties</label>
                            <input type="text" id="specialties" name="specialties" value="<?php echo sanitize($_POST['specialties'] ?? ''); ?>" placeholder="e.g., Residential, Luxury">
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Location Information</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo sanitize($_POST['address'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo sanitize($_POST['city'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="<?php echo sanitize($_POST['state'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="zip_code">ZIP Code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?php echo sanitize($_POST['zip_code'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" id="website" name="website" value="<?php echo sanitize($_POST['website'] ?? ''); ?>" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary);">Social Media (Optional)</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="facebook">Facebook</label>
                            <input type="text" id="facebook" name="facebook" value="<?php echo sanitize($_POST['facebook'] ?? ''); ?>" placeholder="Facebook URL">
                        </div>
                        <div class="form-group">
                            <label for="twitter">Twitter</label>
                            <input type="text" id="twitter" name="twitter" value="<?php echo sanitize($_POST['twitter'] ?? ''); ?>" placeholder="Twitter URL">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" id="instagram" name="instagram" value="<?php echo sanitize($_POST['instagram'] ?? ''); ?>" placeholder="Instagram URL">
                        </div>
                        <div class="form-group">
                            <label for="linkedin">LinkedIn</label>
                            <input type="text" id="linkedin" name="linkedin" value="<?php echo sanitize($_POST['linkedin'] ?? ''); ?>" placeholder="LinkedIn URL">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i>
                    Create Agent Account
                </button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
