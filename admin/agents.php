<?php
/**
 * iProply - Admin Agents Management
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Initialize database
$db = Database::getInstance();

// Get filter and search parameters
$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;

// Build query conditions
$conditions = '';
$params = [];

if ($status !== 'all') {
    $conditions .= 'status = :status';
    $params['status'] = $status;
}

if (!empty($search)) {
    if (!empty($conditions)) $conditions .= ' AND ';
    $conditions .= "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params['search'] = "%{$search}%";
}

// Get total count
$countQuery = "SELECT COUNT(*) as count FROM agents";
if (!empty($conditions)) {
    $countQuery .= " WHERE {$conditions}";
}
$countResult = $db->query($countQuery, $params)->fetchAll();
$totalItems = $countResult[0]['count'];
$totalPages = ceil($totalItems / $perPage);

// Handle page boundaries
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Get agents with pagination
$offset = ($page - 1) * $perPage;
$query = "SELECT * FROM agents";
if (!empty($conditions)) {
    $query .= " WHERE {$conditions}";
}
$query .= " ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";

$agents = $db->query($query, $params)->fetchAll();

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $agentId = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
    $newStatus = isset($_POST['status']) ? sanitize($_POST['status']) : '';
    
    if ($action === 'approve' && $agentId > 0) {
        $db->update('agents', ['status' => 'active'], 'id = :id', ['id' => $agentId]);
        set_flash_message('Agent approved successfully', 'success');
    } elseif ($action === 'reject' && $agentId > 0) {
        $db->delete('agents', 'id = :id', ['id' => $agentId]);
        set_flash_message('Agent rejected and removed', 'success');
    } elseif ($action === 'change-status' && $agentId > 0 && in_array($newStatus, ['active', 'inactive', 'suspended'])) {
        $db->update('agents', ['status' => $newStatus], 'id = :id', ['id' => $agentId]);
        $statusLabels = ['active' => 'activated', 'inactive' => 'deactivated', 'suspended' => 'suspended'];
        set_flash_message('Agent ' . $statusLabels[$newStatus] . ' successfully', 'success');
    }
    
    // Redirect to prevent form resubmission
    redirect('agents.php?status=' . $status);
}

$pageTitle = 'Manage Agents';
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
        .search-form {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .search-form input,
        .search-form select,
        .search-form button {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font: 0.875rem 'Inter', sans-serif;
        }
        
        .search-form input {
            flex: 1;
            min-width: 200px;
        }
        
        .status-badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: 2px solid var(--border);
            background: white;
            transition: all 0.2s;
        }
        
        .status-badge:hover {
            border-color: var(--primary);
        }
        
        .status-badge.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--text-primary);
        }
        
        .pagination a:hover {
            background-color: var(--secondary);
        }
        
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .agent-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .agent-row:hover {
            background-color: var(--secondary);
        }
        
        .agent-name {
            font-weight: 500;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-approve {
            background-color: var(--success);
            color: white;
        }
        
        .btn-approve:hover {
            opacity: 0.9;
        }
        
        .btn-reject {
            background-color: var(--error);
            color: white;
        }
        
        .btn-reject:hover {
            opacity: 0.9;
        }
        
        .btn-deactivate {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-deactivate:hover {
            opacity: 0.9;
        }
        
        .btn-activate {
            background-color: #48bb78;
            color: white;
        }
        
        .header-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            background-color: var(--secondary);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            color: var(--text-secondary);
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
                <h1>Manage Agents</h1>
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
            
            <!-- Search & Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo $search; ?>">
                        <select name="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>
            
            <!-- Agents List -->
            <div class="card">
                <div class="card-header">
                    <h2>Agents (<?php echo $totalItems; ?> total)</h2>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($agents)): ?>
                        <div class="empty-state" style="padding: 2rem;">
                            <i class="fas fa-users"></i>
                            <p>No agents found</p>
                        </div>
                    <?php else: ?>
                        <div class="header-row">
                            <div>Name</div>
                            <div>Email</div>
                            <div>Phone</div>
                            <div>Status</div>
                            <div style="text-align: right;">Actions</div>
                        </div>
                        <?php foreach ($agents as $agent): ?>
                            <div class="agent-row">
                                <div class="agent-name"><?php echo sanitize($agent['first_name'] . ' ' . $agent['last_name']); ?></div>
                                <div><?php echo sanitize($agent['email']); ?></div>
                                <div><?php echo sanitize($agent['phone'] ?? $agent['mobile']); ?></div>
                                <div>
                                    <span class="badge badge-<?php echo $agent['status'] === 'pending' ? 'warning' : ($agent['status'] === 'active' ? 'success' : ($agent['status'] === 'suspended' ? 'error' : 'info')); ?>">
                                        <?php echo ucfirst($agent['status']); ?>
                                    </span>
                                </div>
                                <div class="actions" style="justify-content: flex-end;">
                                    <?php if ($agent['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <button type="submit" class="action-btn btn-approve" onclick="return confirm('Approve this agent?');">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <button type="submit" class="action-btn btn-reject" onclick="return confirm('Reject and remove this agent?');">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php elseif ($agent['status'] === 'active'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Change agent status to Inactive?');">
                                            <input type="hidden" name="action" value="change-status">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="action-btn btn-deactivate">
                                                <i class="fas fa-pause"></i> Deactivate
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Suspend this agent?');">
                                            <input type="hidden" name="action" value="change-status">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="action-btn btn-reject">
                                                <i class="fas fa-ban"></i> Suspend
                                            </button>
                                        </form>
                                        <a href="view-agent.php?id=<?php echo $agent['id']; ?>" class="action-btn btn-primary" style="color: white; text-decoration: none;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php elseif ($agent['status'] === 'inactive'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Activate this agent?');">
                                            <input type="hidden" name="action" value="change-status">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="action-btn btn-activate">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Activate this agent?');">
                                            <input type="hidden" name="action" value="change-status">
                                            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="action-btn btn-activate">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="agents.php?page=1&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-step-backward"></i></a>
                        <a href="agents.php?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="agents.php?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="agents.php?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a>
                        <a href="agents.php?page=<?php echo $totalPages; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-step-forward"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>