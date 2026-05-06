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

$workspacesForPicker = WorkspaceContext::activeList();
$selectedWorkspaceId = sanitize($_POST['workspace_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ws = WorkspaceContext::validateForForm($_POST['workspace_id'] ?? null);
    if (!$ws['ok']) {
        foreach ($ws['errors'] as $msg) {
            $errors[] = $msg;
        }
    }

    if (empty($errors) && $auth->agentRegister($_POST)) {
        if ($ws['resolved_id'] !== null) {
            WorkspaceContext::setPrefill($ws['resolved_id']);
        }
        $successMessage = 'Registration successful! Your account is pending approval. An admin will review your profile soon.';
        $_POST = [];
        $selectedWorkspaceId = '';
    } elseif (empty($errors)) {
        $errors = array_merge($errors, $auth->getErrors());
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/agent-auth.css'); ?>">
</head>
<body class="auth-body">
    <div class="auth-shell auth-shell--wide">
        <div class="auth-logo">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>

        <div class="auth-card">
            <div class="auth-card-head">
                <h1>Join our agent network</h1>
                <p><?php
                    $wsN = count($workspacesForPicker);
                    if ($wsN > 1) {
                        echo 'Choose your primary workspace, complete your profile, and submit for review.';
                    } elseif ($wsN === 1) {
                        echo 'Complete your profile for the workspace below and submit for review.';
                    } else {
                        echo 'Complete your profile and submit for review.';
                    }
                ?></p>
            </div>

            <?php if ($successMessage): ?>
                <div class="alert-auth alert-auth-success"><?php echo sanitize($successMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-auth alert-auth-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p style="margin:.25rem 0"><?php echo sanitize($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate>
                <?php require __DIR__ . '/partials/workspace-picker.php'; ?>

                <div class="form-section-wrap">
                    <div class="form-section-title">Personal information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First name *</label>
                            <input type="text" id="first_name" name="first_name" required value="<?php echo sanitize($_POST['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last name *</label>
                            <input type="text" id="last_name" name="last_name" required value="<?php echo sanitize($_POST['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-wrap">
                    <div class="form-section-title">Account</div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo sanitize($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="form-section-wrap">
                    <div class="form-section-title">Contact</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" autocomplete="tel" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo sanitize($_POST['mobile'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-wrap">
                    <div class="form-section-title">Professional profile</div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="license_number">License number</label>
                            <input type="text" id="license_number" name="license_number" value="<?php echo sanitize($_POST['license_number'] ?? ''); ?>" placeholder="e.g., RE-12345">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="years_experience">Years of experience</label>
                            <input type="number" id="years_experience" name="years_experience" min="0" value="<?php echo sanitize($_POST['years_experience'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="specialties">Specialties</label>
                            <input type="text" id="specialties" name="specialties" value="<?php echo sanitize($_POST['specialties'] ?? ''); ?>" placeholder="e.g., Residential, luxury">
                        </div>
                    </div>
                </div>

                <div class="form-section-wrap">
                    <div class="form-section-title">Location</div>
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
                            <label for="zip_code">ZIP code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?php echo sanitize($_POST['zip_code'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" id="website" name="website" placeholder="https://…" value="<?php echo sanitize($_POST['website'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-wrap" style="border-bottom:none;padding-bottom:0;margin-bottom:.5rem">
                    <div class="form-section-title">Social (optional)</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="facebook">Facebook</label>
                            <input type="text" id="facebook" name="facebook" placeholder="Profile URL" value="<?php echo sanitize($_POST['facebook'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="twitter">Twitter / X</label>
                            <input type="text" id="twitter" name="twitter" placeholder="Profile URL" value="<?php echo sanitize($_POST['twitter'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" id="instagram" name="instagram" placeholder="Profile URL" value="<?php echo sanitize($_POST['instagram'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="linkedin">LinkedIn</label>
                            <input type="text" id="linkedin" name="linkedin" placeholder="Profile URL" value="<?php echo sanitize($_POST['linkedin'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="fas fa-user-plus"></i>
                    Create agent account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already registered? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
