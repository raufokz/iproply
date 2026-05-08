<?php
/**
 * Realty - Admin Properties Management
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Property.php';

// Check authentication
$auth = new Auth();
$auth->requireAdmin();

// Initialize database
$db = Database::getInstance();
$propertyModel = new Property();

// Get filter and search parameters
$allowedStatusFilters = array_merge(['all', 'featured'], Property::ADMIN_STATUSES);
$status = isset($_GET['status']) ? strtolower(trim((string) $_GET['status'])) : 'all';
if (!in_array($status, $allowedStatusFilters, true)) {
    $status = 'all';
}
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = ADMIN_ITEMS_PER_PAGE;

// -------------------------------------------------------
// FIX #1: Process POST actions BEFORE running SELECT queries
// so the redirect fires before any stale data is fetched.
// FIX #2: CSRF protection added to every action.
// FIX #3: set_flash_message() argument order corrected
//         to match config.php signature: ($type, $message)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Verify CSRF token
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash_message('error', 'Invalid request. Please try again.');
        redirect('admin/properties.php?status=' . $status);
    }

    $action     = trim((string) $_POST['action']);
    $propertyId = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;

    if ($propertyId > 0) {
        switch ($action) {
            case 'approve':
                // property_status = admin workflow column (active/inactive/pending…)
                $propertyModel->updateStatus($propertyId, Property::STATUS_ACTIVE, current_user_id());
                set_flash_message('success', 'Property approved successfully.');
                break;

            case 'reject':
                if ($propertyModel->delete($propertyId)) {
                    set_flash_message('success', 'Property removed and images cleaned up.');
                } else {
                    set_flash_message('error', implode(' ', $propertyModel->getErrors()) ?: 'Unable to remove property.');
                }
                break;

            case 'feature':
                $db->update('properties', ['is_featured' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $propertyId]);
                set_flash_message('success', 'Property marked as featured.');
                break;

            case 'unfeature':
                $db->update('properties', ['is_featured' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $propertyId]);
                set_flash_message('success', 'Property removed from featured.');
                break;

            case 'deactivate':
                $propertyModel->updateStatus($propertyId, Property::STATUS_INACTIVE, current_user_id());
                set_flash_message('success', 'Property deactivated successfully.');
                break;

            case 'activate':
                $propertyModel->updateStatus($propertyId, Property::STATUS_ACTIVE, current_user_id());
                set_flash_message('success', 'Property activated successfully.');
                break;

            case 'draft':
                $propertyModel->updateStatus($propertyId, Property::STATUS_DRAFT, current_user_id());
                set_flash_message('success', 'Property moved to draft.');
                break;

            case 'pending':
                $propertyModel->updateStatus($propertyId, Property::STATUS_PENDING, current_user_id());
                set_flash_message('success', 'Property moved to pending review.');
                break;

            case 'sold':
                $propertyModel->updateStatus($propertyId, Property::STATUS_SOLD, current_user_id());
                set_flash_message('success', 'Property marked as sold.');
                break;

            case 'rented':
                $propertyModel->updateStatus($propertyId, Property::STATUS_RENTED, current_user_id());
                set_flash_message('success', 'Property marked as rented.');
                break;

            default:
                set_flash_message('error', 'Unknown action.');
                break;
        }
    } else {
        set_flash_message('error', 'Invalid property ID.');
    }

    // Redirect to prevent form resubmission
    redirect('admin/properties.php?status=' . urlencode($status) . '&search=' . urlencode($search) . '&page=' . $page);
}

// -------------------------------------------------------
// Build query conditions
// -------------------------------------------------------
$conditions = '';
$params     = [];

if ($status === 'featured') {
    $conditions .= 'p.is_featured = 1';
} elseif ($status !== 'all') {
    // property_status is the admin workflow column (active/inactive/pending/sold…)
    // p.status is the listing type (sale/rent/sold) — NOT what we filter by here
    $conditions        .= 'p.property_status = :status';
    $params['status']   = $status;
}

if (!empty($search)) {
    if (!empty($conditions)) $conditions .= ' AND ';
    $pat = "%{$search}%";
    $conditions .= '(p.title LIKE :ap_s1 OR p.description LIKE :ap_s2 OR p.address LIKE :ap_s3 OR p.city LIKE :ap_s4 OR p.state LIKE :ap_s5 OR a.first_name LIKE :ap_s6 OR a.last_name LIKE :ap_s7)';
    $params['ap_s1'] = $pat;
    $params['ap_s2'] = $pat;
    $params['ap_s3'] = $pat;
    $params['ap_s4'] = $pat;
    $params['ap_s5'] = $pat;
    $params['ap_s6'] = $pat;
    $params['ap_s7'] = $pat;
}

// Get total count — must JOIN agents too so the same p.-prefixed conditions work
$countQuery = "SELECT COUNT(*) as count FROM properties p LEFT JOIN agents a ON p.agent_id = a.id";
if (!empty($conditions)) {
    $countQuery .= " WHERE {$conditions}";
}
$countResult = $db->query($countQuery, $params)->fetchAll();
$totalItems  = $countResult[0]['count'];
$totalPages  = ceil($totalItems / $perPage);

// Handle page boundaries
if ($page < 1) $page = 1;
if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

// Get properties with pagination
$offset = ($page - 1) * $perPage;
$query  = "SELECT p.*, a.first_name, a.last_name
           FROM properties p
           LEFT JOIN agents a ON p.agent_id = a.id";
if (!empty($conditions)) {
    $query .= " WHERE {$conditions}";
}
$query .= " ORDER BY p.created_at DESC LIMIT {$perPage} OFFSET {$offset}";

$properties = $db->query($query, $params)->fetchAll();

// Pre-generate CSRF token for use in all forms on this page
$csrfToken = generate_csrf_token();

$pageTitle = 'Manage Properties';
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

        .property-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .property-row:hover {
            background-color: var(--secondary);
        }

        .property-title {
            font-weight: 500;
        }

        .property-price {
            font-weight: 600;
            color: var(--success);
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

        .btn-approve:hover { opacity: 0.9; }

        .btn-reject {
            background-color: var(--error);
            color: white;
        }

        .btn-reject:hover { opacity: 0.9; }

        .btn-feature {
            background-color: #f6ad55;
            color: white;
        }

        .btn-feature:hover { opacity: 0.9; }

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
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="properties.php" class="active"><i class="fas fa-building"></i> Properties</a>
            <a href="agents.php"><i class="fas fa-users"></i> Agents</a>
            <a href="inquiries.php"><i class="fas fa-envelope"></i> Inquiries</a>
            <a href="blogs.php"><i class="fas fa-blog"></i> Blogs</a>
            <a href="pages.php"><i class="fas fa-file-lines"></i> Pages</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?php echo base_url(); ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                <h1>Manage Properties</h1>
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

            <!-- Flash Messages -->
            <?php $flashMessages = get_flash_messages(); ?>
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?php echo sanitize($message['type']); ?>">
                        <?php echo sanitize($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Search & Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="search-form">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search by title, address, or description..."
                            value="<?php echo sanitize($search); ?>"
                        >
                        <select name="status">
                            <option value="all"      <?php echo $status === 'all'      ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending"  <?php echo $status === 'pending'  ? 'selected' : ''; ?>>Pending Approval</option>
                            <option value="draft"    <?php echo $status === 'draft'    ? 'selected' : ''; ?>>Draft</option>
                            <option value="active"   <?php echo $status === 'active'   ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="sold"     <?php echo $status === 'sold'     ? 'selected' : ''; ?>>Sold</option>
                            <option value="rented"   <?php echo $status === 'rented'   ? 'selected' : ''; ?>>Rented</option>
                            <option value="featured" <?php echo $status === 'featured' ? 'selected' : ''; ?>>Featured</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <!-- Properties List -->
            <div class="card">
                <div class="card-header">
                    <h2>Properties (<?php echo $totalItems; ?> total)</h2>
                </div>
                <div class="card-body" style="padding: 0;">

                    <?php if (empty($properties)): ?>
                        <div class="empty-state" style="padding: 2rem;">
                            <i class="fas fa-home"></i>
                            <p>No properties found.</p>
                        </div>
                    <?php else: ?>

                        <div class="header-row">
                            <div>Title</div>
                            <div>Agent</div>
                            <div>Price</div>
                            <div>Status</div>
                            <div style="text-align: right;">Actions</div>
                        </div>

                        <?php foreach ($properties as $property): ?>
                            <div class="property-row">

                                <!-- Title -->
                                <div class="property-title">
                                    <?php echo sanitize(truncate($property['title'], 30)); ?>
                                </div>

                                <!-- Agent -->
                                <div>
                                    <?php
                                        $agentName = trim(($property['first_name'] ?? '') . ' ' . ($property['last_name'] ?? ''));
                                        echo sanitize($agentName ?: '—');
                                    ?>
                                </div>

                                <!-- Price -->
                                <div class="property-price">
                                    $<?php echo number_format($property['price']); ?>
                                </div>

                                <!-- Status badges -->
                                <div>
                                    <?php
                                        // property_status drives both the badge colour and which action buttons show
                                        $badgeClass = match($property['property_status']) {
                                            'pending'  => 'warning',
                                            'draft'    => 'info',
                                            'active',
                                            'featured' => 'success',
                                            'inactive' => 'info',
                                            'sold',
                                            'rented'   => 'info',
                                            default    => 'info',
                                        };
                                    ?>
                                    <span class="badge badge-<?php echo $badgeClass; ?>">
                                        <?php echo ucfirst(sanitize($property['property_status'])); ?>
                                    </span>
                                    <?php if ($property['is_featured']): ?>
                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                    <!-- Also show listing type (sale/rent) as a secondary label -->
                                    <span class="badge badge-info" style="margin-left:0.25rem;">
                                        <?php echo ucfirst(sanitize($property['status'])); ?>
                                    </span>
                                </div>

                                <!-- Action buttons — each form carries the CSRF token -->
                                <div class="actions" style="justify-content: flex-end;">

                                    <a href="edit-property.php?id=<?php echo (int)$property['id']; ?>" class="action-btn btn-feature" style="text-decoration:none;">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>

                                    <?php if ($property['property_status'] === 'pending'): ?>

                                        <!-- Approve -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                            <button type="submit" class="action-btn btn-approve"
                                                    onclick="return confirm('Approve this property?');">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Reject -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                            <button type="submit" class="action-btn btn-reject"
                                                    onclick="return confirm('Reject and permanently remove this property?');">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>

                                    <?php elseif (in_array($property['property_status'], ['active', 'featured'])): ?>

                                        <!-- Feature / Unfeature -->
                                        <?php if (!$property['is_featured']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="feature">
                                                <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                                <button type="submit" class="action-btn btn-feature">
                                                    <i class="fas fa-star"></i> Feature
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="unfeature">
                                                <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                                <button type="submit" class="action-btn btn-feature">
                                                    <i class="fas fa-star"></i> Unfeature
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Deactivate -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                            <button type="submit" class="action-btn btn-reject"
                                                    onclick="return confirm('Deactivate this property?');">
                                                <i class="fas fa-pause"></i> Deactivate
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <!-- Activate — for inactive / sold / rented -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                                            <button type="submit" class="action-btn btn-approve"
                                                    onclick="return confirm('Activate this property?');">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                </div><!-- /.actions -->
                            </div><!-- /.property-row -->
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div><!-- /.card-body -->
            </div><!-- /.card -->

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">

                    <?php if ($page > 1): ?>
                        <a href="properties.php?page=1&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-step-backward"></i>
                        </a>
                        <a href="properties.php?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="properties.php?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="properties.php?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="properties.php?page=<?php echo $totalPages; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-step-forward"></i>
                        </a>
                    <?php endif; ?>

                </div><!-- /.pagination -->
            <?php endif; ?>

        </main>
    </div><!-- /.main-wrapper -->

</body>
</html>
