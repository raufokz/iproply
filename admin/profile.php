<?php
/**
 * Realty - Admin Profile
 * Handles: profile info update, password change, avatar upload
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireAdmin();

$db     = Database::getInstance();
$errors = [];
$csrfToken = generate_csrf_token();

// ------------------------------------------------------------------
// Load current admin data
// ------------------------------------------------------------------
$admin = $db->query(
    "SELECT * FROM admins WHERE id = :id LIMIT 1",
    ['id' => current_user_id()]
)->fetch();

if (!$admin) {
    set_flash_message('error', 'Admin profile not found.');
    redirect('admin/dashboard.php');
}

// ------------------------------------------------------------------
// Handle POST
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('admin/profile.php');
    }

    $action = $_POST['action'] ?? '';

    // ---- Update profile info ----
    if ($action === 'update_profile') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $phone     = trim($_POST['phone']      ?? '');

        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName))  $errors[] = 'Last name is required.';

        if (empty($errors)) {
            $db->update('admins', [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => current_user_id()]);

            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            set_flash_message('success', 'Profile updated successfully.');
            redirect('admin/profile.php');
        }
    }

    // ---- Change password ----
    if ($action === 'change_password') {
        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        if (empty($currentPw))  $errors[] = 'Current password is required.';
        if (strlen($newPw) < PASSWORD_MIN_LENGTH)
            $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        if ($newPw !== $confirmPw) $errors[] = 'New passwords do not match.';

        if (empty($errors)) {
            if (!password_verify($currentPw, $admin['password'])) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $db->update('admins',
                    ['password' => password_hash($newPw, PASSWORD_DEFAULT), 'updated_at' => date('Y-m-d H:i:s')],
                    'id = :id', ['id' => current_user_id()]
                );
                set_flash_message('success', 'Password changed successfully.');
                redirect('admin/profile.php');
            }
        }
    }

    // ---- Upload avatar ----
    if ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Please select a valid image file.';
        } else {
            $file     = $_FILES['avatar'];
            $mimeType = mime_content_type($file['tmp_name']);

            if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES)) {
                $errors[] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
            } elseif ($file['size'] > UPLOAD_MAX_SIZE) {
                $errors[] = 'Image must be smaller than 5MB.';
            } else {
                $uploadDir = UPLOAD_PATH . 'avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'admin_' . current_user_id() . '_' . time() . '.' . $ext;
                $dest     = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    if (!empty($admin['avatar'])) {
                        $old = UPLOAD_PATH . 'avatars/' . $admin['avatar'];
                        if (file_exists($old)) unlink($old);
                    }
                    $db->update('admins',
                        ['avatar' => $filename, 'updated_at' => date('Y-m-d H:i:s')],
                        'id = :id', ['id' => current_user_id()]
                    );
                    set_flash_message('success', 'Profile photo updated.');
                    redirect('admin/profile.php');
                } else {
                    $errors[] = 'Failed to upload image. Please try again.';
                }
            }
        }
    }

    // Reload after failed POST
    $admin = $db->query("SELECT * FROM admins WHERE id = :id LIMIT 1", ['id' => current_user_id()])->fetch();
}

$avatarUrl = !empty($admin['avatar'])
    ? UPLOAD_URL . 'avatars/' . $admin['avatar']
    : base_url('assets/images/default-avatar.png');

$pageTitle     = 'Admin Profile';
$flashMessages = get_flash_messages();
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
        :root {
            --primary:       #1e3b5a;
            --primary-light: #2c5282;
            --accent:        #c9a84c;
            --secondary:     #f4f6f9;
            --surface:       #ffffff;
            --border:        #e2e8f0;
            --text-primary:  #1a2332;
            --text-secondary:#64748b;
            --success:       #10b981;
            --error:         #ef4444;
            --warning:       #f59e0b;
            --shadow-sm:     0 1px 3px rgba(0,0,0,.08);
            --shadow-md:     0 4px 16px rgba(0,0,0,.1);
            --radius:        10px;
            --radius-lg:     16px;
        }

        body { font-family: var(--font-family); }

        .content { padding: 2rem; max-width: 1000px; }

        /* ---- Alerts ---- */
        .alert {
            padding: .875rem 1.25rem; border-radius: var(--radius);
            margin-bottom: 1.5rem; font-size: .9rem;
            display: flex; align-items: flex-start; gap: .75rem;
        }
        .alert i { margin-top: .1rem; flex-shrink: 0; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert ul { margin: .25rem 0 0 1rem; }

        /* ---- Profile hero ---- */
        .profile-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #162d46 100%);
            border-radius: var(--radius-lg);
            padding: 2rem;
            display: flex; align-items: center; gap: 1.75rem;
            margin-bottom: 2rem;
            position: relative; overflow: hidden;
        }
        .profile-hero::before {
            content: '';
            position: absolute; right: 2rem; top: 50%; transform: translateY(-50%);
            font-family: 'Font Awesome 6 Free'; font-weight: 900;
            content: '\f505'; /* fa-user-shield */
            font-size: 7rem; color: rgba(255,255,255,.04);
            pointer-events: none;
        }
        .hero-avatar-wrap { position: relative; flex-shrink: 0; }
        .hero-avatar {
            width: 100px; height: 100px; border-radius: 12px;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,.3);
            box-shadow: var(--shadow-md);
        }
        .hero-avatar-edit {
            position: absolute; bottom: -6px; right: -6px;
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--accent); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; cursor: pointer; border: 2px solid white;
            transition: transform .2s;
        }
        .hero-avatar-edit:hover { transform: scale(1.1); }
        .hero-info { color: white; }
        .hero-info h2 {
            font-family: var(--font-family);
            font-size: 1.6rem; font-weight: 400; margin-bottom: .2rem;
        }
        .hero-email { font-size: .875rem; opacity: .7; margin-bottom: .75rem; }
        .hero-badges { display: flex; gap: .5rem; flex-wrap: wrap; }
        .hero-badge {
            padding: .25rem .75rem; border-radius: 9999px;
            font-size: .75rem; font-weight: 500;
        }
        .badge-role { background: rgba(201,168,76,.3); color: #fcd97e; }
        .badge-status { background: rgba(16,185,129,.2); color: #6ee7b7; }

        /* ---- Tabs ---- */
        .tabs {
            display: flex; gap: 0; margin-bottom: 1.5rem;
            background: var(--surface); border-radius: var(--radius);
            box-shadow: var(--shadow-sm); overflow: hidden;
        }
        .tab-btn {
            flex: 1; padding: .875rem 1rem;
            background: none; border: none; cursor: pointer;
            font: .875rem/1 var(--font-family); font-weight: 500;
            color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            border-bottom: 3px solid transparent; transition: all .2s;
        }
        .tab-btn:hover  { color: var(--primary); background: var(--secondary); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--accent); background: #fafbfc; }

        /* ---- Card ---- */
        .card {
            background: var(--surface); border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: .75rem;
        }
        .card-header h3 { font-size: 1rem; font-weight: 600; color: var(--primary); }
        .card-header i  { color: var(--accent); }
        .card-body { padding: 1.5rem; }

        /* ---- Form ---- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .form-grid .span-2 { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; gap: .4rem; }
        .form-label {
            font-size: .8125rem; font-weight: 600;
            color: var(--text-secondary); text-transform: uppercase; letter-spacing: .03em;
        }
        .form-label span { color: var(--error); }
        .form-control {
            padding: .625rem .875rem;
            border: 1.5px solid var(--border); border-radius: var(--radius);
            font: .9375rem var(--font-family); color: var(--text-primary);
            background: var(--surface); transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,59,90,.1); }
        .form-control[readonly] { background: var(--secondary); color: var(--text-secondary); cursor: not-allowed; }
        .form-hint { font-size: .78rem; color: var(--text-secondary); }

        /* ---- Info row (read-only display) ---- */
        .info-row {
            display: flex; align-items: center;
            padding: .875rem 0; border-bottom: 1px solid var(--border);
            gap: 1rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .info-label {
            width: 160px; font-size: .8125rem; font-weight: 600;
            color: var(--text-secondary); text-transform: uppercase; letter-spacing: .03em;
            flex-shrink: 0;
        }
        .info-row .info-value { font-size: .9375rem; color: var(--text-primary); }

        /* ---- Upload zone ---- */
        .avatar-upload-zone {
            border: 2px dashed var(--border); border-radius: var(--radius);
            padding: 2rem; text-align: center; cursor: pointer; transition: all .2s;
        }
        .avatar-upload-zone:hover { border-color: var(--primary); background: #f8faff; }
        .avatar-upload-zone i { font-size: 2.5rem; color: var(--text-secondary); margin-bottom: .75rem; }
        .avatar-upload-zone p { font-size: .875rem; color: var(--text-secondary); margin-bottom: .5rem; }
        .avatar-upload-zone small { font-size: .78rem; color: #94a3b8; }

        /* ---- Password strength ---- */
        .strength-bar { height: 4px; border-radius: 2px; background: var(--border); margin-top: .4rem; overflow: hidden; }
        .strength-fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }
        .strength-label { font-size: .78rem; color: var(--text-secondary); margin-top: .25rem; }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .65rem 1.5rem; border-radius: var(--radius);
            font: 500 .9rem var(--font-family);
            border: none; cursor: pointer; transition: all .2s; text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-light); box-shadow: var(--shadow-md); }
        .btn-accent { background: var(--accent); color: white; }
        .btn-accent:hover { opacity: .9; }
        .btn-outline { background: none; color: var(--primary); border: 1.5px solid var(--border); }
        .btn-outline:hover { border-color: var(--primary); background: var(--secondary); }
        .form-actions {
            display: flex; gap: .75rem; justify-content: flex-end;
            padding-top: 1.25rem; border-top: 1px solid var(--border); margin-top: 1rem;
        }

        /* ---- Tab panels ---- */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .span-2 { grid-column: 1; }
            .profile-hero { flex-direction: column; text-align: center; }
            .tabs { overflow-x: auto; }
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-row .info-label { width: auto; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="properties.php"><i class="fas fa-building"></i> Properties</a>
        <a href="agents.php"><i class="fas fa-users"></i> Agents</a>
        <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
        <a href="pages.php"><i class="fas fa-file-lines"></i> Pages</a>
        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="profile.php" class="active"><i class="fas fa-user-shield"></i> My Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-title"><h1>My Profile</h1></div>
        <div class="topbar-user">
            <div class="user-info">
                <div class="name"><?php echo sanitize(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')); ?></div>
                <div class="role">Administrator</div>
            </div>
            <?php if (!empty($admin['avatar'])): ?>
                <img src="<?php echo $avatarUrl; ?>" alt="Avatar"
                    style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:2px solid var(--border);">
            <?php else: ?>
                <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            <?php endif; ?>
        </div>
    </header>

    <main class="content">

        <!-- Flash messages -->
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $msg): ?>
                <div class="alert alert-<?php echo sanitize($msg['type']); ?>">
                    <i class="fas fa-<?php echo $msg['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <div><?php echo sanitize($msg['message']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul><?php foreach ($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <!-- Profile hero -->
        <div class="profile-hero">
            <div class="hero-avatar-wrap">
                <?php if (!empty($admin['avatar'])): ?>
                    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="hero-avatar">
                <?php else: ?>
                    <div style="width:100px;height:100px;border-radius:12px;background:rgba(255,255,255,.15);
                         display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:rgba(255,255,255,.6);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                <?php endif; ?>
                <label for="quickAvatarInput" class="hero-avatar-edit" title="Change photo">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <div class="hero-info">
                <h2><?php echo sanitize(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')); ?></h2>
                <div class="hero-email"><?php echo sanitize($admin['email']); ?></div>
                <div class="hero-badges">
                    <span class="hero-badge badge-role">
                        <i class="fas fa-shield-alt"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                    </span>
                    <span class="hero-badge badge-status">
                        <?php echo ucfirst($admin['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('info', event)">
                <i class="fas fa-id-card"></i> Profile Info
            </button>
            <button class="tab-btn" onclick="switchTab('account', event)">
                <i class="fas fa-info-circle"></i> Account Details
            </button>
            <button class="tab-btn" onclick="switchTab('photo', event)">
                <i class="fas fa-camera"></i> Profile Photo
            </button>
            <button class="tab-btn" onclick="switchTab('password', event)">
                <i class="fas fa-lock"></i> Password
            </button>
        </div>

        <!-- Tab: Profile Info -->
        <div class="tab-panel active" id="tab-info">
            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="update_profile">

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i>
                        <h3>Personal Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name <span>*</span></label>
                                <input type="text" name="first_name" class="form-control"
                                    value="<?php echo sanitize($admin['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span>*</span></label>
                                <input type="text" name="last_name" class="form-control"
                                    value="<?php echo sanitize($admin['last_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="<?php echo sanitize($admin['phone'] ?? ''); ?>"
                                    placeholder="(555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control"
                                    value="<?php echo sanitize($admin['email']); ?>" readonly>
                                <span class="form-hint">Email address cannot be changed here. Contact a super admin.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-outline">Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Tab: Account Details (read-only) -->
        <div class="tab-panel" id="tab-account">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Account Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo sanitize($admin['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo sanitize($admin['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            <span style="background:rgba(201,168,76,.15);color:var(--accent);padding:.2rem .75rem;border-radius:9999px;font-size:.8rem;font-weight:600;">
                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span style="background:rgba(16,185,129,.1);color:var(--success);padding:.2rem .75rem;border-radius:9999px;font-size:.8rem;font-weight:600;">
                                <?php echo ucfirst($admin['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $admin['last_login'] ? format_date($admin['last_login'], 'M d, Y g:i A') : 'N/A'; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo format_date($admin['created_at']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Photo Upload -->
        <div class="tab-panel" id="tab-photo">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-camera"></i>
                    <h3>Profile Photo</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="upload_avatar">

                        <div style="text-align:center;margin-bottom:1.5rem;">
                            <img src="<?php echo $avatarUrl; ?>" alt="Current Avatar" id="bigAvatarPreview"
                                style="width:120px;height:120px;border-radius:12px;object-fit:cover;border:3px solid var(--border);margin-bottom:.75rem;">
                            <p style="font-size:.875rem;color:var(--text-secondary);">Current profile photo</p>
                        </div>

                        <label class="avatar-upload-zone" for="avatarFileInput" id="dropZone">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p><strong>Click to upload</strong> or drag and drop</p>
                            <small>JPEG, PNG, WebP or GIF · Max 5MB</small>
                        </label>
                        <input type="file" name="avatar" id="avatarFileInput"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            style="display:none;"
                            onchange="previewAvatar(this)">

                        <div class="form-actions">
                            <button type="submit" class="btn btn-accent" id="uploadBtn" style="display:none;">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Password -->
        <div class="tab-panel" id="tab-password">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock"></i>
                    <h3>Change Password</h3>
                </div>
                <div class="card-body">
                    <form method="POST" style="max-width:480px;">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group" style="margin-bottom:1.25rem;">
                            <label class="form-label">Current Password <span>*</span></label>
                            <input type="password" name="current_password" class="form-control"
                                autocomplete="current-password" required>
                        </div>
                        <div class="form-group" style="margin-bottom:1.25rem;">
                            <label class="form-label">New Password <span>*</span></label>
                            <input type="password" name="new_password" id="newPw" class="form-control"
                                autocomplete="new-password" required oninput="checkStrength(this.value)">
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-label" id="strengthLabel">Enter a password</div>
                        </div>
                        <div class="form-group" style="margin-bottom:1.5rem;">
                            <label class="form-label">Confirm New Password <span>*</span></label>
                            <input type="password" name="confirm_password" id="confirmPw" class="form-control"
                                autocomplete="new-password" required oninput="checkMatch()">
                            <div class="form-hint" id="matchHint"></div>
                        </div>

                        <div class="form-actions" style="border-top:none;padding-top:0;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Quick avatar upload triggered from hero camera icon -->
<form method="POST" enctype="multipart/form-data" id="quickAvatarForm" style="display:none;">
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="action" value="upload_avatar">
    <input type="file" name="avatar" id="quickAvatarInput"
        accept="image/jpeg,image/png,image/gif,image/webp"
        onchange="document.getElementById('quickAvatarForm').submit();">
</form>

<script>
    function switchTab(name, e) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        if (e) e.currentTarget.classList.add('active');
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('bigAvatarPreview').src = e.target.result;
                document.getElementById('uploadBtn').style.display = 'inline-flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function checkStrength(pw) {
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        let score = 0;
        if (pw.length >= 8)           score++;
        if (/[A-Z]/.test(pw))         score++;
        if (/[0-9]/.test(pw))         score++;
        if (/[^A-Za-z0-9]/.test(pw))  score++;
        const levels = [
            { pct: '0%',   color: '#e2e8f0', text: 'Enter a password' },
            { pct: '25%',  color: '#ef4444', text: 'Weak' },
            { pct: '50%',  color: '#f59e0b', text: 'Fair' },
            { pct: '75%',  color: '#3b82f6', text: 'Good' },
            { pct: '100%', color: '#10b981', text: 'Strong' },
        ];
        fill.style.width      = levels[score].pct;
        fill.style.background = levels[score].color;
        label.textContent     = levels[score].text;
        label.style.color     = levels[score].color;
    }

    function checkMatch() {
        const pw   = document.getElementById('newPw').value;
        const cpw  = document.getElementById('confirmPw').value;
        const hint = document.getElementById('matchHint');
        if (!cpw) { hint.textContent = ''; return; }
        hint.textContent = pw === cpw ? '✓ Passwords match' : '✗ Passwords do not match';
        hint.style.color = pw === cpw ? '#10b981' : '#ef4444';
    }

    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.borderColor = 'var(--primary)'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = 'var(--border)'; });
        dropZone.addEventListener('drop', e => {
            e.preventDefault(); dropZone.style.borderColor = 'var(--border)';
            if (e.dataTransfer.files.length) {
                const fi = document.getElementById('avatarFileInput');
                fi.files = e.dataTransfer.files;
                previewAvatar(fi);
            }
        });
    }
</script>
</body>
</html>
