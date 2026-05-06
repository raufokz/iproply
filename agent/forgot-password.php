<?php
/**
 * Agent portal — forgot password request.
 * Displays a privacy-safe confirmation; full token-based reset can be wired to email later.
 */
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

if (is_agent()) {
    redirect('agent/dashboard.php');
}

$errors = [];
$sent = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $errors[] = 'Please enter the email address on your agent account.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $sent = true;
    }
}

$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — <?php echo APP_NAME; ?></title>
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
                <h1>Reset your password</h1>
                <p>Enter the email tied to your agent account. If it matches an active profile, we will send reset instructions when automated delivery is enabled.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-auth alert-auth-error" role="alert">
                    <?php foreach ($errors as $err): ?>
                        <p style="margin:.25rem 0"><?php echo sanitize($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($sent): ?>
                <div class="alert-auth alert-auth-success">
                    Thanks. If that email is registered to an agent account here, you will receive reset instructions when outbound email is enabled for <?php echo APP_NAME; ?>.
                    If you still need access, contact your administrator.
                </div>
                <div class="auth-footer" style="border-top:none;padding-top:1rem;margin-top:.5rem">
                    <p><a href="login.php"><i class="fas fa-arrow-left"></i> Back to sign in</a></p>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus value="<?php echo sanitize($email); ?>">
                    </div>
                    <button type="submit" class="btn-auth-primary">
                        <i class="fas fa-paper-plane"></i>
                        Send reset link
                    </button>
                </form>

                <div class="auth-footer">
                    <p><a href="login.php">Back to sign in</a></p>
                    <p>No account yet? <a href="register.php">Register</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
