<?php
/**
 * Realty - Agent Profile
 * Handles: profile info update, password change, avatar upload
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireAgent();

$db     = Database::getInstance();
$errors = [];
$csrfToken = generate_csrf_token();

// ------------------------------------------------------------------
// Load current agent data
// ------------------------------------------------------------------
$agent = $db->query(
    "SELECT * FROM agents WHERE id = :id LIMIT 1",
    ['id' => current_user_id()]
)->fetch();

if (!$agent) {
    set_flash_message('error', 'Agent profile not found.');
    redirect('agent/dashboard.php');
}

// ------------------------------------------------------------------
// Handle POST
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('agent/profile.php');
    }

    $action = $_POST['action'] ?? '';

    // ---- Update profile info ----
    if ($action === 'update_profile') {
        $firstName   = trim($_POST['first_name']   ?? '');
        $lastName    = trim($_POST['last_name']    ?? '');
        $phone       = trim($_POST['phone']        ?? '');
        $mobile      = trim($_POST['mobile']       ?? '');
        $bio         = trim($_POST['bio']          ?? '');
        $licenseNum  = trim($_POST['license_number'] ?? '');
        $yearsExp    = (int)($_POST['years_experience'] ?? 0);
        $specialties = trim($_POST['specialties']  ?? '');
        $address     = trim($_POST['address']      ?? '');
        $city        = trim($_POST['city']         ?? '');
        $state       = trim($_POST['state']        ?? '');
        $zipCode     = trim($_POST['zip_code']     ?? '');
        $website     = trim($_POST['website']      ?? '');
        $facebook    = trim($_POST['facebook']     ?? '');
        $twitter     = trim($_POST['twitter']      ?? '');
        $instagram   = trim($_POST['instagram']    ?? '');
        $linkedin    = trim($_POST['linkedin']     ?? '');
        $emailNotify = isset($_POST['email_notifications']) ? 1 : 0;

        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName))  $errors[] = 'Last name is required.';

        if (empty($errors)) {
            $db->update('agents', [
                'first_name'          => $firstName,
                'last_name'           => $lastName,
                'phone'               => $phone,
                'mobile'              => $mobile,
                'bio'                 => $bio,
                'license_number'      => $licenseNum,
                'years_experience'    => $yearsExp,
                'specialties'         => $specialties,
                'address'             => $address,
                'city'                => $city,
                'state'               => $state,
                'zip_code'            => $zipCode,
                'website'             => $website,
                'facebook'            => $facebook,
                'twitter'             => $twitter,
                'instagram'           => $instagram,
                'linkedin'            => $linkedin,
                'email_notifications' => $emailNotify,
                'updated_at'          => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => current_user_id()]);

            // Update session display name
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;

            set_flash_message('success', 'Profile updated successfully.');
            redirect('agent/profile.php');
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
            if (!password_verify($currentPw, $agent['password'])) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $db->update('agents',
                    ['password' => password_hash($newPw, PASSWORD_DEFAULT), 'updated_at' => date('Y-m-d H:i:s')],
                    'id = :id', ['id' => current_user_id()]
                );
                set_flash_message('success', 'Password changed successfully.');
                redirect('agent/profile.php');
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
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'agent_' . current_user_id() . '_' . time() . '.' . $ext;
                $dest     = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Delete old avatar if it exists
                    if (!empty($agent['avatar'])) {
                        $oldFile = UPLOAD_PATH . 'avatars/' . $agent['avatar'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $db->update('agents',
                        ['avatar' => $filename, 'updated_at' => date('Y-m-d H:i:s')],
                        'id = :id', ['id' => current_user_id()]
                    );
                    set_flash_message('success', 'Profile photo updated.');
                    redirect('agent/profile.php');
                } else {
                    $errors[] = 'Failed to upload image. Please try again.';
                }
            }
        }
    }

    // Reload fresh data after a failed POST so the form is not stale
    $agent = $db->query("SELECT * FROM agents WHERE id = :id LIMIT 1", ['id' => current_user_id()])->fetch();
}

$avatarUrl = !empty($agent['avatar'])
    ? UPLOAD_URL . 'avatars/' . $agent['avatar']
    : base_url('assets/images/default-avatar.png');

$pageTitle = 'My Profile';
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
            --shadow-lg:     0 8px 32px rgba(0,0,0,.12);
            --radius:        10px;
            --radius-lg:     16px;
            --font-family:   'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-family);
            background: var(--secondary);
            color: var(--text-primary);
            font-size: 0.9375rem;
        }

        /* ---- Sidebar ---- */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: 260px; background: var(--primary);
            color: white; overflow-y: auto; z-index: 1000;
            display: flex; flex-direction: column;
        }
        .sidebar-header {
            padding: 1.75rem 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-header a {
            font-family: var(--font-family);
            font-size: 1.6rem; color: white; text-decoration: none;
        }
        .sidebar-header a span { color: var(--accent); }
        .sidebar-nav { padding: 1rem 0; flex: 1; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .8rem 1.5rem;
            color: rgba(255,255,255,.75);
            text-decoration: none; font-size: .9rem;
            transition: all .2s; border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,.08);
            color: white; border-left-color: var(--accent);
        }
        .sidebar-nav i { width: 18px; text-align: center; font-size: .9rem; }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem .75rem; color: rgba(255,255,255,.7);
            text-decoration: none; border-radius: var(--radius);
            font-size: .875rem; transition: all .2s;
        }
        .sidebar-footer a:hover { background: rgba(255,255,255,.08); color: white; }

        /* ---- Layout ---- */
        .main-wrapper { margin-left: 260px; min-height: 100vh; }
        .topbar {
            background: var(--surface);
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar h1 {
            font-family: var(--font-family);
            font-size: 1.5rem; font-weight: 400; color: var(--primary);
        }
        .topbar-user { display: flex; align-items: center; gap: 1rem; }
        .topbar-user .name { font-weight: 600; font-size: .875rem; }
        .topbar-user .role { font-size: .75rem; color: var(--text-secondary); }
        .avatar-sm {
            width: 40px; height: 40px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--border);
        }
        .avatar-placeholder {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .9rem;
        }

        .content { padding: 2rem; max-width: 1100px; }

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

        /* ---- Profile header card ---- */
        .profile-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: var(--radius-lg);
            padding: 2rem;
            display: flex; align-items: center; gap: 1.75rem;
            margin-bottom: 2rem;
            position: relative; overflow: hidden;
        }
        .profile-hero::after {
            content: '';
            position: absolute; right: -40px; top: -40px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            pointer-events: none;
        }
        .hero-avatar-wrap { position: relative; flex-shrink: 0; }
        .hero-avatar {
            width: 100px; height: 100px; border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,.4);
            box-shadow: var(--shadow-md);
        }
        .hero-avatar-edit {
            position: absolute; bottom: 2px; right: 2px;
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
            font-size: 1.6rem; font-weight: 400; margin-bottom: .25rem;
        }
        .hero-info .hero-meta { font-size: .875rem; opacity: .8; }
        .hero-info .hero-meta span { margin-right: 1.25rem; }
        .hero-info .hero-meta i { margin-right: .35rem; }
        .hero-badges { display: flex; gap: .5rem; margin-top: .75rem; flex-wrap: wrap; }
        .hero-badge {
            padding: .25rem .75rem; border-radius: 9999px;
            font-size: .75rem; font-weight: 500;
            background: rgba(255,255,255,.15); color: white;
        }
        .hero-badge.active { background: rgba(16,185,129,.25); color: #6ee7b7; }

        /* ---- Tab navigation ---- */
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
            border-bottom: 3px solid transparent;
            transition: all .2s;
        }
        .tab-btn:hover { color: var(--primary); background: var(--secondary); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--accent); background: #fafbfc; }

        /* ---- Cards ---- */
        .card {
            background: var(--surface); border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: .75rem;
        }
        .card-header h3 {
            font-size: 1rem; font-weight: 600; color: var(--primary);
        }
        .card-header i { color: var(--accent); font-size: 1rem; }
        .card-body { padding: 1.5rem; }

        /* ---- Form ---- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        .form-grid .span-2 { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; gap: .4rem; }
        .form-label {
            font-size: .8125rem; font-weight: 600;
            color: var(--text-secondary); text-transform: uppercase;
            letter-spacing: .03em;
        }
        .form-label span { color: var(--error); }
        .form-control {
            padding: .625rem .875rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font: .9375rem var(--font-family);
            color: var(--text-primary);
            background: var(--surface);
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30,59,90,.1);
        }
        textarea.form-control { resize: vertical; min-height: 100px; }
        .form-hint { font-size: .78rem; color: var(--text-secondary); }

        .checkbox-row {
            display: flex; align-items: center; gap: .6rem;
            padding: .75rem 0;
        }
        .checkbox-row input[type=checkbox] {
            width: 17px; height: 17px; accent-color: var(--primary); cursor: pointer;
        }
        .checkbox-row label { font-size: .9rem; cursor: pointer; }

        /* ---- Avatar upload card ---- */
        .avatar-upload-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
        }
        .avatar-upload-zone:hover { border-color: var(--primary); background: #f8faff; }
        .avatar-upload-zone i { font-size: 2.5rem; color: var(--text-secondary); margin-bottom: .75rem; }
        .avatar-upload-zone p { font-size: .875rem; color: var(--text-secondary); margin-bottom: .5rem; }
        .avatar-upload-zone small { font-size: .78rem; color: #94a3b8; }
        #avatarPreview {
            width: 100px; height: 100px; border-radius: 50%;
            object-fit: cover; border: 3px solid var(--border);
            display: none; margin: 0 auto 1rem;
        }

        /* ---- Password strength ---- */
        .strength-bar {
            height: 4px; border-radius: 2px;
            background: var(--border); margin-top: .4rem; overflow: hidden;
        }
        .strength-fill {
            height: 100%; width: 0; border-radius: 2px;
            transition: width .3s, background .3s;
        }
        .strength-label { font-size: .78rem; color: var(--text-secondary); margin-top: .25rem; }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .65rem 1.5rem; border-radius: var(--radius);
            font: 500 .9rem var(--font-family);
            border: none; cursor: pointer; transition: all .2s;
            text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-light); box-shadow: var(--shadow-md); }
        .btn-accent { background: var(--accent); color: white; }
        .btn-accent:hover { opacity: .9; box-shadow: var(--shadow-md); }
        .btn-outline {
            background: none; color: var(--primary);
            border: 1.5px solid var(--border);
        }
        .btn-outline:hover { border-color: var(--primary); background: var(--secondary); }
        .form-actions {
            display: flex; gap: .75rem; justify-content: flex-end;
            padding-top: 1.25rem; border-top: 1px solid var(--border); margin-top: 1rem;
        }

        /* ---- Tab panels ---- */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ---- Section divider ---- */
        .section-label {
            font-size: .75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: var(--text-secondary);
            margin: 1.5rem 0 .75rem;
            display: flex; align-items: center; gap: .5rem;
        }
        .section-label::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-wrapper { margin-left: 0; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .span-2 { grid-column: 1; }
            .profile-hero { flex-direction: column; text-align: center; }
            .tabs { overflow-x: auto; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/agent.css">
</head>
<body class="agent-portal">

<aside class="sidebar" id="agentSidebar">
    <div class="sidebar-header">
        <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="properties.php"><i class="fas fa-building"></i> My Properties</a>
        <a href="add-property.php"><i class="fas fa-plus-circle"></i> Add Property</a>
        <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
        <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<div class="sidebar-overlay" data-sidebar-overlay></div>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-title">
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="agentSidebar" aria-expanded="false" aria-label="Open menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <h1>My Profile</h1>
        </div>
        <div class="topbar-user">
            <div style="text-align:right;">
                <div class="name"><?php echo sanitize($agent['first_name'] . ' ' . $agent['last_name']); ?></div>
                <div class="role">Real Estate Agent</div>
            </div>
            <?php if (!empty($agent['avatar'])): ?>
                <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="avatar-sm">
            <?php else: ?>
                <div class="avatar-placeholder"><?php echo strtoupper(substr($agent['first_name'], 0, 1)); ?></div>
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
                <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="hero-avatar" id="heroAvatar">
                <label for="quickAvatarInput" class="hero-avatar-edit" title="Change photo">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <div class="hero-info">
                <h2><?php echo sanitize($agent['first_name'] . ' ' . $agent['last_name']); ?></h2>
                <div class="hero-meta">
                    <?php if (!empty($agent['city'])): ?>
                        <span><i class="fas fa-map-marker-alt"></i><?php echo sanitize($agent['city'] . ', ' . $agent['state']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($agent['license_number'])): ?>
                        <span><i class="fas fa-id-badge"></i><?php echo sanitize($agent['license_number']); ?></span>
                    <?php endif; ?>
                    <?php if ($agent['years_experience'] > 0): ?>
                        <span><i class="fas fa-briefcase"></i><?php echo (int)$agent['years_experience']; ?> yrs experience</span>
                    <?php endif; ?>
                </div>
                <div class="hero-badges">
                    <span class="hero-badge <?php echo $agent['status'] === 'active' ? 'active' : ''; ?>">
                        <?php echo ucfirst(sanitize($agent['status'])); ?>
                    </span>
                    <?php if ($agent['is_featured']): ?>
                        <span class="hero-badge"><i class="fas fa-star"></i> Featured Agent</span>
                    <?php endif; ?>
                    <?php if ($agent['email_notifications']): ?>
                        <span class="hero-badge"><i class="fas fa-bell"></i> Notifications On</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('info')">
                <i class="fas fa-user"></i> Personal Info
            </button>
            <button class="tab-btn" onclick="switchTab('contact')">
                <i class="fas fa-address-book"></i> Contact & Social
            </button>
            <button class="tab-btn" onclick="switchTab('photo')">
                <i class="fas fa-camera"></i> Profile Photo
            </button>
            <button class="tab-btn" onclick="switchTab('password')">
                <i class="fas fa-lock"></i> Password
            </button>
        </div>

        <!-- Tab: Personal Info -->
        <div class="tab-panel active" id="tab-info">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="update_profile">

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name <span>*</span></label>
                                <input type="text" name="first_name" class="form-control"
                                    value="<?php echo sanitize($agent['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span>*</span></label>
                                <input type="text" name="last_name" class="form-control"
                                    value="<?php echo sanitize($agent['last_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control"
                                    value="<?php echo sanitize($agent['license_number'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" name="years_experience" class="form-control"
                                    value="<?php echo (int)($agent['years_experience'] ?? 0); ?>" min="0" max="60">
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label">Bio / About Me</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo sanitize($agent['bio'] ?? ''); ?></textarea>
                                <span class="form-hint">Displayed publicly on your agent profile page.</span>
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label">Specialties</label>
                                <input type="text" name="specialties" class="form-control"
                                    value="<?php echo sanitize($agent['specialties'] ?? ''); ?>"
                                    placeholder="e.g. Luxury Homes, Commercial, Investment Properties">
                            </div>
                        </div>

                        <div class="section-label">Notifications</div>
                        <div class="checkbox-row">
                            <input type="checkbox" name="email_notifications" id="emailNotify"
                                <?php echo $agent['email_notifications'] ? 'checked' : ''; ?>>
                            <label for="emailNotify">Receive email notifications for new inquiries</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-outline">Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Tab: Contact & Social -->
        <div class="tab-panel" id="tab-contact">
            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="update_profile">
                <!-- Carry over non-contact fields silently -->
                <input type="hidden" name="first_name"       value="<?php echo sanitize($agent['first_name']); ?>">
                <input type="hidden" name="last_name"        value="<?php echo sanitize($agent['last_name']); ?>">
                <input type="hidden" name="bio"              value="<?php echo sanitize($agent['bio'] ?? ''); ?>">
                <input type="hidden" name="license_number"   value="<?php echo sanitize($agent['license_number'] ?? ''); ?>">
                <input type="hidden" name="years_experience" value="<?php echo (int)($agent['years_experience'] ?? 0); ?>">
                <input type="hidden" name="specialties"      value="<?php echo sanitize($agent['specialties'] ?? ''); ?>">
                <?php if ($agent['email_notifications']): ?><input type="hidden" name="email_notifications" value="1"><?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-phone"></i>
                        <h3>Contact Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="<?php echo sanitize($agent['phone'] ?? ''); ?>"
                                    placeholder="(555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mobile</label>
                                <input type="tel" name="mobile" class="form-control"
                                    value="<?php echo sanitize($agent['mobile'] ?? ''); ?>"
                                    placeholder="(555) 987-6543">
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control"
                                    value="<?php echo sanitize($agent['address'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control"
                                    value="<?php echo sanitize($agent['city'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control"
                                    value="<?php echo sanitize($agent['state'] ?? ''); ?>"
                                    placeholder="NY" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label class="form-label">ZIP Code</label>
                                <input type="text" name="zip_code" class="form-control"
                                    value="<?php echo sanitize($agent['zip_code'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control"
                                    value="<?php echo sanitize($agent['website'] ?? ''); ?>"
                                    placeholder="https://yourwebsite.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-share-alt"></i>
                        <h3>Social Media</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook</label>
                                <input type="url" name="facebook" class="form-control"
                                    value="<?php echo sanitize($agent['facebook'] ?? ''); ?>"
                                    placeholder="https://facebook.com/yourpage">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fab fa-twitter" style="color:#1da1f2"></i> Twitter / X</label>
                                <input type="url" name="twitter" class="form-control"
                                    value="<?php echo sanitize($agent['twitter'] ?? ''); ?>"
                                    placeholder="https://twitter.com/yourhandle">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fab fa-instagram" style="color:#e1306c"></i> Instagram</label>
                                <input type="url" name="instagram" class="form-control"
                                    value="<?php echo sanitize($agent['instagram'] ?? ''); ?>"
                                    placeholder="https://instagram.com/yourprofile">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn</label>
                                <input type="url" name="linkedin" class="form-control"
                                    value="<?php echo sanitize($agent['linkedin'] ?? ''); ?>"
                                    placeholder="https://linkedin.com/in/yourprofile">
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

        <!-- Tab: Photo Upload -->
        <div class="tab-panel" id="tab-photo">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-camera"></i>
                    <h3>Profile Photo</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="avatarForm">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="upload_avatar">

                        <div style="text-align:center; margin-bottom:1.5rem;">
                            <img src="<?php echo $avatarUrl; ?>" alt="Current Avatar"
                                style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid var(--border);margin-bottom:.75rem;" id="bigAvatarPreview">
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
                                autocomplete="new-password" required
                                oninput="checkStrength(this.value)">
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-label" id="strengthLabel">Enter a password</div>
                        </div>
                        <div class="form-group" style="margin-bottom:1.5rem;">
                            <label class="form-label">Confirm New Password <span>*</span></label>
                            <input type="password" name="confirm_password" id="confirmPw" class="form-control"
                                autocomplete="new-password" required
                                oninput="checkMatch()">
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

<!-- Hidden quick-avatar form triggered from hero camera icon -->
<form method="POST" enctype="multipart/form-data" id="quickAvatarForm" style="display:none;">
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="action" value="upload_avatar">
    <input type="file" name="avatar" id="quickAvatarInput"
        accept="image/jpeg,image/png,image/gif,image/webp"
        onchange="document.getElementById('quickAvatarForm').submit();">
</form>

<script>
    // Tab switching
    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // Avatar preview
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

    // Password strength checker
    function checkStrength(pw) {
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        let score = 0;
        if (pw.length >= 8)  score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        const levels = [
            { pct: '0%',   color: '#e2e8f0', text: 'Enter a password' },
            { pct: '25%',  color: '#ef4444', text: 'Weak' },
            { pct: '50%',  color: '#f59e0b', text: 'Fair' },
            { pct: '75%',  color: '#3b82f6', text: 'Good' },
            { pct: '100%', color: '#10b981', text: 'Strong' },
        ];
        fill.style.width    = levels[score].pct;
        fill.style.background = levels[score].color;
        label.textContent   = levels[score].text;
        label.style.color   = levels[score].color;
    }

    // Password match checker
    function checkMatch() {
        const pw  = document.getElementById('newPw').value;
        const cpw = document.getElementById('confirmPw').value;
        const hint = document.getElementById('matchHint');
        if (!cpw) { hint.textContent = ''; return; }
        if (pw === cpw) {
            hint.textContent = '✓ Passwords match';
            hint.style.color = '#10b981';
        } else {
            hint.textContent = '✗ Passwords do not match';
            hint.style.color = '#ef4444';
        }
    }

    // Drag & drop for avatar upload zone
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = 'var(--primary)'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = 'var(--border)'; });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border)';
            const dt = e.dataTransfer;
            if (dt.files.length) {
                const fileInput = document.getElementById('avatarFileInput');
                fileInput.files = dt.files;
                previewAvatar(fileInput);
            }
        });
    }
</script>
<script src="../assets/js/agent-portal.js"></script>
</body>
</html>
