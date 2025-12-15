<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/avatar_helper.php';

// Require admin access
requireAdmin();

$pdo = getConnection();
$message = '';
$error = '';

// Handle user actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $userId = (int)$_POST['user_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                $stmt->execute([$userId]);
                $message = "User deleted successfully!";
            } catch (Exception $e) {
                $error = "Error deleting user: " . $e->getMessage();
            }
            break;
            
        case 'toggle_status':
            $userId = (int)$_POST['user_id'];
            $newStatus = $_POST['status'] === 'active' ? 'inactive' : 'active';
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
                $stmt->execute([$newStatus, $userId]);
                $message = "User status updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating user status: " . $e->getMessage();
            }
            break;
    }
}

// Get all users with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $whereClause .= " AND role = ?";
    $params[] = $role_filter;
}

// Get total count
$countSql = "SELECT COUNT(*) FROM users $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Get users
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get user statistics (with error handling for missing columns)
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active' => 0, // Will be updated after database fix
    'customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'artisans' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'artisan'")->fetchColumn()
];

// Try to get active count if status column exists
try {
    $stats['active'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
} catch (Exception $e) {
    // Column doesn't exist yet, assume all users are active
    $stats['active'] = $stats['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - KalaSetuGram Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, var(--dark-color), var(--secondary-color));
            min-height: 100vh;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .role-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .role-admin { background: #e7f3ff; color: #0066cc; }
        .role-artisan { background: #fff3e0; color: #e65100; }
        .role-customer { background: #f3e5f5; color: #7b1fa2; }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h4 class="mb-1">
                <i class="fas fa-palette me-2"></i>
                KalaSetuGram
            </h4>
            <small>Admin Panel</small>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crafts.php">
                        <i class="fas fa-palette"></i>
                        Manage Crafts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="artisans.php">
                        <i class="fas fa-users"></i>
                        Manage Artisans
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="users.php">
                        <i class="fas fa-user-friends"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags"></i>
                        Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="coupons.php">
                        <i class="fas fa-ticket-alt"></i>
                        Coupons
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="heritage-stories.php">
                        <i class="fas fa-book-open"></i>
                        Heritage Stories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home"></i>
                        View Website
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">User Management</h1>
                <p class="text-muted">Manage all registered users</p>
            </div>
            <button class="btn btn-primary d-md-none" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['total']); ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['active']); ?></div>
                    <div class="stats-label">Active Users</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['customers']); ?></div>
                    <div class="stats-label">Customers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['artisans']); ?></div>
                    <div class="stats-label">Artisans</div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search Users</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or email">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Role</label>
                        <select class="form-select" name="role">
                            <option value="">All Roles</option>
                            <option value="customer" <?php echo $role_filter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="artisan" <?php echo $role_filter === 'artisan' ? 'selected' : ''; ?>>Artisan</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Users List (<?php echo number_format($totalUsers); ?> total)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php echo generateUserAvatar($user['id'], $user['name'], 40, '1.2rem'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                            <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                                    <?php if ($user['email_verified']): ?>
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Verified</small>
                                    <?php else: ?>
                                        <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Unverified</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status'] ?? 'active'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <div class="btn-group" role="group">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to toggle this user status?')">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $user['status'] ?? 'active'; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                                <i class="fas fa-toggle-<?php echo ($user['status'] ?? 'active') === 'active' ? 'on' : 'off'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                        <span class="text-muted">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
        }
    </script>
</body>
</html>
