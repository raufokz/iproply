<?php
/**
 * Realty - Agent Inquiries
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Inquiry.php';
require_once '../includes/Mail.php';

// Check authentication
$auth = new Auth();
$auth->requireAgent();

$inquiryModel = new Inquiry();
$db = Database::getInstance();

// ------------------------------------------------------------------
// Handle POST reply — sends email via Mail class
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {

    // CSRF check
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('agent/inquiries.php');
    }

    $inquiryId   = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
    $replyBody   = trim($_POST['reply_message'] ?? '');

    if ($inquiryId <= 0 || empty($replyBody)) {
        set_flash_message('error', 'Reply message cannot be empty.');
        redirect('agent/inquiries.php');
    }

    // Fetch the inquiry + property so we have all data for the email
    $inquiry = $inquiryModel->getById($inquiryId);

    if (!$inquiry) {
        set_flash_message('error', 'Inquiry not found.');
        redirect('agent/inquiries.php');
    }

    // Security: make sure this inquiry belongs to the logged-in agent
    if ((int)$inquiry['agent_id'] !== (int)current_user_id()) {
        set_flash_message('error', 'Unauthorized action.');
        redirect('agent/inquiries.php');
    }

    // Fetch the agent row so Mail has first_name / last_name / email
    $agent = $db->query(
        "SELECT * FROM agents WHERE id = :id LIMIT 1",
        ['id' => current_user_id()]
    )->fetch();

    if (!$agent || empty($agent['email'])) {
        set_flash_message('error', 'Could not load your agent profile. Please update your profile email.');
        redirect('agent/inquiries.php');
    }

    // Build and send the reply email to the enquirer
    $mail    = new Mail();
    $subject = 'Re: Your Inquiry about ' . ($inquiry['property_title'] ?? 'a Property');

    $agentName  = trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')) ?: 'Your Agent';
    $agentEmail = $agent['email'];
    $agentPhone = $agent['phone'] ?? '';

    $emailBody = '
    <h2>Hello ' . sanitize($inquiry['name'] ?? '') . ',</h2>
    <p>Thank you for your inquiry. Please see the response from your agent below.</p>

    <div style="background:#f5f5f5;padding:20px;border-radius:5px;margin:20px 0;border-left:4px solid #1e3b5a;">
        ' . nl2br(sanitize($replyBody)) . '
    </div>

    <div style="border:1px solid #e0e0e0;border-radius:5px;padding:15px;margin:15px 0;">
        <h3 style="color:#1e3b5a;margin-bottom:10px;">Agent Contact Details</h3>
        <p><strong>Name:</strong> ' . sanitize($agentName) . '</p>
        <p><strong>Email:</strong> <a href="mailto:' . sanitize($agentEmail) . '">' . sanitize($agentEmail) . '</a></p>
        ' . ($agentPhone ? '<p><strong>Phone:</strong> ' . sanitize($agentPhone) . '</p>' : '') . '
    </div>

    <p style="color:#666;font-size:13px;">
        This is a reply to your inquiry submitted on ' . format_date($inquiry['created_at']) . '.
        If you have further questions, please reply directly to this email or contact your agent.
    </p>
    ';

    $sent = $mail->send(
        $inquiry['email'],
        $inquiry['name'] ?? 'Customer',
        $subject,
        $emailBody
    );

    if ($sent) {
        // Mark inquiry as responded
        $inquiryModel->updateStatus($inquiryId, 'responded', current_user_id());
        set_flash_message('success', 'Reply sent successfully to ' . $inquiry['email'] . '.');
    } else {
        $errors = $mail->getErrors();
        $errMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown mail error.';
        set_flash_message('error', 'Failed to send reply: ' . $errMsg);
    }

    redirect('agent/inquiries.php');
}

// ------------------------------------------------------------------
// Handle GET actions (read / respond / delete)
// ------------------------------------------------------------------
if (isset($_GET['action']) && isset($_GET['id'])) {
    $inquiryId = (int)$_GET['id'];

    switch ($_GET['action']) {
        case 'read':
            $inquiryModel->markAsRead($inquiryId);
            set_flash_message('success', 'Inquiry marked as read.');
            break;

        case 'respond':
            $inquiryModel->updateStatus($inquiryId, 'responded', current_user_id());
            set_flash_message('success', 'Inquiry marked as responded.');
            break;

        case 'delete':
            $inquiryModel->delete($inquiryId);
            set_flash_message('success', 'Inquiry deleted.');
            break;
    }

    redirect('agent/inquiries.php');
}

// ------------------------------------------------------------------
// Fetch inquiries list
// ------------------------------------------------------------------
$status  = $_GET['status'] ?? '';
$filters = ['agent_id' => current_user_id()];
if ($status) {
    $filters['status'] = $status;
}

$page           = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage        = 20;
$inquiries      = $inquiryModel->getAll($filters, $page, $perPage);
$totalInquiries = $inquiryModel->getTotalCount($filters);
$totalPages     = ceil($totalInquiries / $perPage);
$stats          = $inquiryModel->getStats(current_user_id());

$csrfToken = generate_csrf_token();
$pageTitle = 'Inquiries';
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
            --shadow-sm: 0 1px 2px rgba(0,0,0,.05);
            --shadow-lg: 0 10px 40px rgba(0,0,0,.15);
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--secondary);
            color: var(--text-primary);
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: 260px;
            background-color: var(--primary);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-header a { font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-nav { padding: 1rem 0; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .875rem 1.5rem;
            color: rgba(255,255,255,.8);
            text-decoration: none;
            transition: all .2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: rgba(255,255,255,.1); color: white;
        }
        .sidebar-nav i { width: 20px; text-align: center; }
        .sidebar-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: .75rem;
            padding: .75rem 1rem;
            color: rgba(255,255,255,.8);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all .2s;
        }
        .sidebar-footer a:hover { background-color: rgba(255,255,255,.1); color: white; }

        /* ---------- Layout ---------- */
        .main-wrapper { margin-left: 260px; min-height: 100vh; }
        .topbar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-title h1 { font-size: 1.5rem; font-weight: 600; }
        .topbar-user { display: flex; align-items: center; gap: 1rem; }
        .user-info { text-align: right; }
        .user-info .name { font-weight: 600; font-size: .875rem; }
        .user-info .role { font-size: .75rem; color: var(--text-secondary); }
        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background-color: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
        }
        .content { padding: 2rem; }

        /* ---------- Stats bar ---------- */
        .stats-bar { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .stat-pill {
            background-color: white; padding: .75rem 1.25rem;
            border-radius: var(--radius-md);
            display: flex; align-items: center; gap: .5rem;
            font-size: .875rem;
        }
        .stat-pill.new { color: var(--error); }
        .stat-pill.read { color: var(--info); }
        .stat-pill.responded { color: var(--success); }
        .stat-pill .count { font-weight: 700; font-size: 1.125rem; }

        /* ---------- Card ---------- */
        .card { background-color: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h2 { font-size: 1.125rem; font-weight: 600; }
        .card-body { padding: 1.5rem; }

        /* ---------- Filter tabs ---------- */
        .filter-tabs { display: flex; gap: .5rem; }
        .filter-tab {
            padding: .5rem 1rem; border-radius: var(--radius-md);
            font-size: .875rem; text-decoration: none; color: var(--text-secondary);
            transition: all .2s;
        }
        .filter-tab:hover, .filter-tab.active { background-color: var(--primary); color: white; }

        /* ---------- Table ---------- */
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        .table th { font-weight: 600; font-size: .75rem; text-transform: uppercase; color: var(--text-secondary); }
        .table tr:hover { background-color: var(--secondary); }
        .table tr.new { background-color: #fff5f5; }

        /* ---------- Badges ---------- */
        .badge {
            display: inline-block; padding: .25rem .75rem;
            border-radius: 9999px; font-size: .75rem; font-weight: 500;
        }
        .badge-success { background-color: #f0fff4; color: var(--success); }
        .badge-warning { background-color: #fffaf0; color: var(--warning); }
        .badge-info    { background-color: #ebf8ff; color: var(--info); }
        .badge-error   { background-color: #fff5f5; color: var(--error); }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 500; cursor: pointer;
            text-decoration: none; border: none;
            font-size: .875rem; transition: all .2s;
        }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { opacity: .9; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-warning:hover { opacity: .9; }
        .btn-danger  { background-color: var(--error);   color: white; }
        .btn-danger:hover { opacity: .9; }

        .actions { display: flex; gap: .5rem; flex-wrap: wrap; }

        /* ---------- Alert ---------- */
        .alert { padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; }
        .alert-success { background-color: #f0fff4; color: var(--success); border: 1px solid #9ae6b4; }
        .alert-error   { background-color: #fff5f5; color: var(--error);   border: 1px solid #feb2b2; }

        /* ---------- Misc ---------- */
        .empty-state { text-align: center; padding: 3rem; color: var(--text-secondary); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: .5; }
        .inquiry-preview { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ---------- Pagination ---------- */
        .pagination { display: flex; justify-content: center; gap: .5rem; margin-top: 1.5rem; }
        .pagination a, .pagination span {
            display: flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 .75rem;
            border-radius: var(--radius-md); font-size: .875rem; font-weight: 500;
            text-decoration: none;
        }
        .pagination a { background-color: white; color: var(--text-primary); border: 1px solid var(--border); }
        .pagination a:hover { background-color: var(--primary); color: white; border-color: var(--primary); }
        .pagination .active { background-color: var(--primary); color: white; }

        /* ---------- Reply Modal ---------- */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }

        .modal {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 560px;
            box-shadow: var(--shadow-lg);
            animation: slideUp .25s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h3 { font-size: 1.125rem; font-weight: 600; }
        .modal-close {
            background: none; border: none; cursor: pointer;
            font-size: 1.25rem; color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: var(--radius-md);
            transition: background .2s;
        }
        .modal-close:hover { background: var(--secondary); }

        .modal-body { padding: 1.5rem; }

        .modal-meta {
            background: var(--secondary);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.25rem;
            font-size: .875rem;
        }
        .modal-meta p { margin-bottom: .35rem; }
        .modal-meta p:last-child { margin-bottom: 0; }
        .modal-meta strong { color: var(--text-primary); }

        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-weight: 500; font-size: .875rem; margin-bottom: .4rem; }
        .form-control {
            width: 100%; padding: .625rem .875rem;
            border: 1px solid var(--border); border-radius: var(--radius-md);
            font: .875rem 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            transition: border-color .2s;
            resize: vertical;
        }
        .form-control:focus { outline: none; border-color: var(--primary); }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: .75rem;
        }
        .btn-secondary { background-color: var(--secondary); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-secondary:hover { background-color: #e8e8e8; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform .3s; }
            .sidebar.active { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .stats-bar { flex-wrap: wrap; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/agent.css">
</head>
<body class="agent-portal">

    <!-- Reply Modal -->
    <div class="modal-overlay" id="replyModal">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-reply"></i> Reply to Inquiry</h3>
                <button class="modal-close" onclick="closeModal()" aria-label="Close">&times;</button>
            </div>
            <form method="POST" id="replyForm">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="inquiry_id" id="modalInquiryId" value="">

                <div class="modal-body">
                    <!-- Summary of who we're replying to -->
                    <div class="modal-meta">
                        <p><strong>To:</strong> <span id="modalToName"></span> &lt;<span id="modalToEmail"></span>&gt;</p>
                        <p><strong>Re:</strong> <span id="modalPropertyTitle"></span></p>
                        <p><strong>Their message:</strong> <span id="modalOriginalMessage"></span></p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="replyMessage">Your Reply *</label>
                        <textarea
                            class="form-control"
                            id="replyMessage"
                            name="reply_message"
                            rows="7"
                            placeholder="Type your reply here..."
                            required
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar" id="agentSidebar">
        <div class="sidebar-header">
            <a href="<?php echo base_url(); ?>"><?php echo APP_NAME; ?></a>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="properties.php"><i class="fas fa-building"></i> My Properties</a>
            <a href="add-property.php"><i class="fas fa-plus-circle"></i> Add Property</a>
            <a href="inquiries.php" class="active"><i class="fas fa-envelope"></i> Inquiries</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" data-sidebar-overlay></div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="agentSidebar" aria-expanded="false" aria-label="Open menu">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                <h1>Inquiries</h1>
            </div>
            <div class="topbar-user">
                <div class="user-info">
                    <div class="name"><?php echo sanitize($_SESSION['user_name']); ?></div>
                    <div class="role">Real Estate Agent</div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <main class="content">

            <!-- Flash Messages -->
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $msg): ?>
                    <div class="alert alert-<?php echo sanitize($msg['type']); ?>">
                        <?php echo sanitize($msg['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-bar">
                <div class="stat-pill new">
                    <i class="fas fa-envelope"></i>
                    <span class="count"><?php echo (int)$stats['new_count']; ?></span>
                    <span>New</span>
                </div>
                <div class="stat-pill read">
                    <i class="fas fa-envelope-open"></i>
                    <span class="count"><?php echo (int)$stats['read_count']; ?></span>
                    <span>Read</span>
                </div>
                <div class="stat-pill responded">
                    <i class="fas fa-check-circle"></i>
                    <span class="count"><?php echo (int)$stats['responded_count']; ?></span>
                    <span>Responded</span>
                </div>
            </div>

            <!-- Inquiries table -->
            <div class="card">
                <div class="card-header">
                    <h2>All Inquiries</h2>
                    <div class="filter-tabs">
                        <a href="inquiries.php"          class="filter-tab <?php echo !$status              ? 'active' : ''; ?>">All</a>
                        <a href="?status=new"            class="filter-tab <?php echo $status === 'new'       ? 'active' : ''; ?>">New</a>
                        <a href="?status=read"           class="filter-tab <?php echo $status === 'read'      ? 'active' : ''; ?>">Read</a>
                        <a href="?status=responded"      class="filter-tab <?php echo $status === 'responded' ? 'active' : ''; ?>">Responded</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($inquiries)): ?>
                        <div class="empty-state">
                            <i class="fas fa-envelope"></i>
                            <h3>No inquiries found</h3>
                            <p>Inquiries from potential buyers will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Name</th>
                                        <th>Property</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inquiries as $inquiry): ?>
                                        <tr class="<?php echo $inquiry['status'] === 'new' ? 'new' : ''; ?>">

                                            <!-- Status badge -->
                                            <td>
                                                <?php
                                                $badgeClass = match($inquiry['status']) {
                                                    'new'       => 'error',
                                                    'responded' => 'success',
                                                    default     => 'info',
                                                };
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo ucfirst(sanitize($inquiry['status'])); ?>
                                                </span>
                                            </td>

                                            <!-- Contact info -->
                                            <td>
                                                <strong><?php echo sanitize($inquiry['name']); ?></strong><br>
                                                <small style="color:var(--text-secondary);"><?php echo sanitize($inquiry['email']); ?></small>
                                                <?php if (!empty($inquiry['phone'])): ?>
                                                    <br><small style="color:var(--text-secondary);"><?php echo sanitize($inquiry['phone']); ?></small>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Property link -->
                                            <td>
                                                <a href="../property.php?slug=<?php echo urlencode($inquiry['property_slug'] ?? ''); ?>" target="_blank">
                                                    <?php echo sanitize(truncate($inquiry['property_title'] ?? '', 30)); ?>
                                                </a>
                                            </td>

                                            <!-- Message preview -->
                                            <td>
                                                <div class="inquiry-preview" title="<?php echo sanitize($inquiry['message']); ?>">
                                                    <?php echo sanitize(truncate($inquiry['message'], 50)); ?>
                                                </div>
                                            </td>

                                            <!-- Date -->
                                            <td><?php echo format_date($inquiry['created_at']); ?></td>

                                            <!-- Actions -->
                                            <td>
                                                <div class="actions">

                                                    <!-- Mark as read -->
                                                    <?php if ($inquiry['status'] === 'new'): ?>
                                                        <a href="?action=read&id=<?php echo (int)$inquiry['id']; ?>"
                                                           class="btn btn-warning" title="Mark as Read">
                                                            <i class="fas fa-envelope-open"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Reply button — opens modal, no mailto: -->
                                                    <button
                                                        type="button"
                                                        class="btn btn-success"
                                                        title="Reply via Email"
                                                        onclick="openModal(
                                                            <?php echo (int)$inquiry['id']; ?>,
                                                            <?php echo json_encode($inquiry['name']       ?? ''); ?>,
                                                            <?php echo json_encode($inquiry['email']      ?? ''); ?>,
                                                            <?php echo json_encode($inquiry['property_title'] ?? ''); ?>,
                                                            <?php echo json_encode(truncate($inquiry['message'] ?? '', 120)); ?>
                                                        )"
                                                    >
                                                        <i class="fas fa-reply"></i> Reply
                                                    </button>

                                                    <!-- Mark as responded -->
                                                    <?php if ($inquiry['status'] !== 'responded'): ?>
                                                        <a href="?action=respond&id=<?php echo (int)$inquiry['id']; ?>"
                                                           class="btn btn-primary" title="Mark as Responded">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Delete -->
                                                    <a href="?action=delete&id=<?php echo (int)$inquiry['id']; ?>"
                                                       class="btn btn-danger" title="Delete"
                                                       onclick="return confirm('Delete this inquiry permanently?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i === $page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script>
        function openModal(id, name, email, propertyTitle, message) {
            document.getElementById('modalInquiryId').value    = id;
            document.getElementById('modalToName').textContent        = name;
            document.getElementById('modalToEmail').textContent       = email;
            document.getElementById('modalPropertyTitle').textContent = propertyTitle;
            document.getElementById('modalOriginalMessage').textContent = message;
            document.getElementById('replyMessage').value = '';
            document.getElementById('replyModal').classList.add('open');
            document.getElementById('replyMessage').focus();
        }

        function closeModal() {
            document.getElementById('replyModal').classList.remove('open');
        }

        // Close modal when clicking outside
        document.getElementById('replyModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
    <script src="../assets/js/agent-portal.js"></script>

</body>
</html>
