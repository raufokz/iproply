<?php
/**
 * Realty - Admin Inquiries Management
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
    $conditions .= 'i.status = :status';
    $params['status'] = $status;
}

if (!empty($search)) {
    if (!empty($conditions)) $conditions .= ' AND ';
    $conditions .= "(i.name LIKE :search OR i.email LIKE :search OR i.property_id LIKE :search)";
    $params['search'] = "%{$search}%";
}

// Get total count
$countQuery = "SELECT COUNT(*) as count FROM inquiries i";
if (!empty($conditions)) {
    $countQuery .= " WHERE {$conditions}";
}
$countResult = $db->query($countQuery, $params)->fetchAll();
$totalItems = $countResult[0]['count'];
$totalPages = ceil($totalItems / $perPage);

// Handle page boundaries
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Get inquiries with pagination
$offset = ($page - 1) * $perPage;
$query = "SELECT i.*, p.title as property_title, a.first_name, a.last_name FROM inquiries i 
          LEFT JOIN properties p ON i.property_id = p.id 
          LEFT JOIN agents a ON p.agent_id = a.id";
if (!empty($conditions)) {
    $query .= " WHERE {$conditions}";
}
$query .= " ORDER BY i.created_at DESC LIMIT {$perPage} OFFSET {$offset}";

$inquiries = $db->query($query, $params)->fetchAll();

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $inquiryId = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
    
    if ($action === 'respond' && $inquiryId > 0) {
        $db->update('inquiries', ['status' => 'responded'], 'id = :id', ['id' => $inquiryId]);
        set_flash_message('Inquiry marked as responded', 'success');
    } elseif ($action === 'archive' && $inquiryId > 0) {
        $db->update('inquiries', ['status' => 'archived'], 'id = :id', ['id' => $inquiryId]);
        set_flash_message('Inquiry archived', 'success');
    } elseif ($action === 'delete' && $inquiryId > 0) {
        $db->delete('inquiries', 'id = :id', ['id' => $inquiryId]);
        set_flash_message('Inquiry deleted', 'success');
    }
    
    // Redirect to prevent form resubmission
    redirect('inquiries.php?status=' . $status);
}

$pageTitle = 'Manage Inquiries';
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
            font: 0.875rem var(--font-family);
        }
        
        .search-form input {
            flex: 1;
            min-width: 200px;
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
        
        .inquiry-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .inquiry-row:hover {
            background-color: var(--secondary);
        }
        
        .inquiry-name {
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
            color: white;
            text-decoration: none;
        }
        
        .btn-respond {
            background-color: var(--success);
        }
        
        .btn-respond:hover {
            opacity: 0.9;
        }
        
        .btn-archive {
            background-color: var(--warning);
        }
        
        .btn-archive:hover {
            opacity: 0.9;
        }
        
        .btn-delete {
            background-color: var(--error);
        }
        
        .btn-delete:hover {
            opacity: 0.9;
        }
        
        .btn-view {
            background-color: var(--info);
        }
        
        .btn-view:hover {
            opacity: 0.9;
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
            <a href="agents.php">
                <i class="fas fa-users"></i> Agents
            </a>
            <a href="inquiries.php" class="active">
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
                <h1>Manage Inquiries</h1>
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
                        <input type="text" name="search" placeholder="Search by name, email, or property..." value="<?php echo $search; ?>">
                        <select name="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="responded" <?php echo $status === 'responded' ? 'selected' : ''; ?>>Responded</option>
                            <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>
            
            <!-- Inquiries List -->
            <div class="card">
                <div class="card-header">
                    <h2>Inquiries (<?php echo $totalItems; ?> total)</h2>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($inquiries)): ?>
                        <div class="empty-state" style="padding: 2rem;">
                            <i class="fas fa-envelope"></i>
                            <p>No inquiries found</p>
                        </div>
                    <?php else: ?>
                        <div class="header-row">
                            <div>Name</div>
                            <div>Property</div>
                            <div>Email</div>
                            <div>Status</div>
                            <div style="text-align: right;">Actions</div>
                        </div>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <div class="inquiry-row">
                                <div class="inquiry-name"><?php echo sanitize($inquiry['name']); ?></div>
                                <div><?php echo sanitize($inquiry['property_title'] ?? 'N/A'); ?></div>
                                <div><?php echo sanitize($inquiry['email']); ?></div>
                                <div>
                                    <span class="badge badge-<?php echo $inquiry['status'] === 'new' ? 'error' : ($inquiry['status'] === 'responded' ? 'success' : 'info'); ?>">
                                        <?php echo ucfirst($inquiry['status']); ?>
                                    </span>
                                </div>
                                <div class="actions" style="justify-content: flex-end;">
                                    <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($inquiry['status'] !== 'responded'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="respond">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" class="action-btn btn-respond">
                                                <i class="fas fa-reply"></i> Respond
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($inquiry['status'] !== 'archived'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="archive">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" class="action-btn btn-archive">
                                                <i class="fas fa-archive"></i> Archive
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete" onclick="return confirm('Delete this inquiry?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
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
                        <a href="inquiries.php?page=1&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-step-backward"></i></a>
                        <a href="inquiries.php?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="inquiries.php?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="inquiries.php?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a>
                        <a href="inquiries.php?page=<?php echo $totalPages; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-step-forward"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
