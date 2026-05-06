<?php
/**
 * Realty - Agent Login
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

$workspacesForPicker = WorkspaceContext::activeList();
$selectedWorkspaceId = sanitize($_POST['workspace_id'] ?? '');
if ($selectedWorkspaceId === '' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $prefill = WorkspaceContext::prefillId();
    $selectedWorkspaceId = $prefill ?: '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $ws = WorkspaceContext::validateForForm($_POST['workspace_id'] ?? null);
    if (!$ws['ok']) {
        foreach ($ws['errors'] as $msg) {
            $errors[] = $msg;
        }
    }

    if (empty($errors)) {
        if ($auth->agentLogin($username, $password)) {
            WorkspaceContext::persistToSession($ws['resolved_id'], $ws['resolved_name']);
            WorkspaceContext::clearPrefill();
            set_flash_message('success', 'Welcome back, ' . $_SESSION['user_name'] . '!');
            redirect('agent/dashboard.php');
        }
        $errors = array_merge($errors, $auth->getErrors());
    }
}

$pageTitle = 'Agent Login';
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
    <div class="auth-shell">
        <div class="auth-logo">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>

        <div class="auth-card">
            <div class="auth-card-head">
                <h1>Agent sign in</h1>
                <p><?php
                    $wsN = count($workspacesForPicker);
                    if ($wsN > 1) {
                        echo 'Choose the workspace you want to use, then enter your credentials.';
                    } elseif ($wsN === 1) {
                        echo 'You are signing in to the workspace below. Enter your account details to continue.';
                    } else {
                        echo 'Enter your credentials to open your dashboard.';
                    }
                ?></p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-auth alert-auth-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p style="margin:.25rem 0"><?php echo sanitize($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate>
                <?php
                require __DIR__ . '/partials/workspace-picker.php';
                ?>

                <div class="form-group">
                    <label for="username">Username or email</label>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username"
                           value="<?php echo sanitize($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="forgot-password.php">Forgot password?</a>
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign in
                </button>
            </form>

            <div class="auth-footer">
                <p>New to <?php echo APP_NAME; ?>? <a href="register.php">Create an agent account</a></p>
                <p><a href="<?php echo base_url('admin/login.php'); ?>">Admin sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
