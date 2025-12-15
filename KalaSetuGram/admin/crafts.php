<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pdo = getConnection();
$message = '';
$error = '';

// Handle craft actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $craftId = (int)$_POST['craft_id'];
            try {
                // Delete craft images first
                $pdo->prepare("DELETE FROM craft_images WHERE craft_id = ?")->execute([$craftId]);
                // Delete craft
                $stmt = $pdo->prepare("DELETE FROM crafts WHERE id = ?");
                $stmt->execute([$craftId]);
                $message = "Craft deleted successfully!";
            } catch (Exception $e) {
                $error = "Error deleting craft: " . $e->getMessage();
            }
            break;
            
        case 'toggle_status':
            $craftId = (int)$_POST['craft_id'];
            $newStatus = $_POST['status'] === 'active' ? 'inactive' : 'active';
            try {
                $stmt = $pdo->prepare("UPDATE crafts SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $craftId]);
                $message = "Craft status updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating craft status: " . $e->getMessage();
            }
            break;
            
        case 'feature':
            $craftId = (int)$_POST['craft_id'];
            $featured = $_POST['featured'] === '1' ? 0 : 1;
            try {
                $stmt = $pdo->prepare("UPDATE crafts SET featured = ? WHERE id = ?");
                $stmt->execute([$featured, $craftId]);
                $message = "Craft featured status updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating featured status: " . $e->getMessage();
            }
            break;
    }
}

// Get crafts with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $whereClause .= " AND c.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $whereClause .= " AND c.status = ?";
    $params[] = $status_filter;
}

// Get total count
$countSql = "SELECT COUNT(*) FROM crafts c 
             JOIN craft_categories cc ON c.category_id = cc.id 
             JOIN artisans a ON c.artisan_id = a.id 
             JOIN users u ON a.user_id = u.id 
             $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalCrafts = $countStmt->fetchColumn();
$totalPages = ceil($totalCrafts / $limit);

// Get crafts
$sql = "SELECT c.*, cc.name as category_name, u.name as artisan_name,
        (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
        FROM crafts c 
        JOIN craft_categories cc ON c.category_id = cc.id 
        JOIN artisans a ON c.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        $whereClause 
        ORDER BY c.created_at DESC 
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$crafts = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM craft_categories ORDER BY name")->fetchAll();

// Get craft statistics (with error handling for missing columns)
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM crafts")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM crafts WHERE status = 'active'")->fetchColumn(),
    'featured' => 0, // Will be updated after database fix
    'categories' => $pdo->query("SELECT COUNT(*) FROM craft_categories")->fetchColumn()
];

// Try to get featured count if column exists
try {
    $stats['featured'] = $pdo->query("SELECT COUNT(*) FROM crafts WHERE featured = 1")->fetchColumn();
} catch (Exception $e) {
    // Column doesn't exist yet
    $stats['featured'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Craft Management - KalaSetuGram Admin</title>
    
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
        
        .craft-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .craft-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .craft-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
        .status-draft { background: #fff3cd; color: #856404; }
        
        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
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
                    <a class="nav-link active" href="crafts.php">
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
                    <a class="nav-link" href="users.php">
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
                <h1 class="h3 mb-0">Craft Management</h1>
                <p class="text-muted">Manage all crafts and products</p>
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
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['total']); ?></div>
                    <div class="stats-label">Total Crafts</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['active']); ?></div>
                    <div class="stats-label">Active Crafts</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['featured']); ?></div>
                    <div class="stats-label">Featured</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['categories']); ?></div>
                    <div class="stats-label">Categories</div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search Crafts</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title or description">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="crafts.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Crafts Grid -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Crafts (<?php echo number_format($totalCrafts); ?> total)</h5>
        </div>

        <div class="row">
            <?php foreach ($crafts as $craft): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="craft-card">
                    <div class="position-relative">
                        <?php if ($craft['primary_image']): ?>
                            <img src="../<?php echo htmlspecialchars($craft['primary_image']); ?>" alt="<?php echo htmlspecialchars($craft['title']); ?>" class="craft-image">
                        <?php else: ?>
                            <div class="craft-image d-flex align-items-center justify-content-center">
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($craft['featured']) && $craft['featured']): ?>
                            <div class="featured-badge">
                                <i class="fas fa-star me-1"></i>Featured
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-3">
                        <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($craft['title']); ?></h6>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($craft['description'], 0, 80)) . '...'; ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary">₹<?php echo number_format($craft['price']); ?></span>
                            <span class="status-badge status-<?php echo $craft['status']; ?>">
                                <?php echo ucfirst($craft['status']); ?>
                            </span>
                        </div>
                        
                        <div class="small text-muted mb-3">
                            <div><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($craft['category_name']); ?></div>
                            <div><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($craft['artisan_name']); ?></div>
                        </div>
                        
                        <div class="btn-group w-100" role="group">
                            <form method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to toggle the status?')">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="craft_id" value="<?php echo $craft['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $craft['status']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-warning w-100" title="Toggle Status">
                                    <i class="fas fa-toggle-<?php echo $craft['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                </button>
                            </form>
                            
                            <form method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to toggle featured status?')">
                                <input type="hidden" name="action" value="feature">
                                <input type="hidden" name="craft_id" value="<?php echo $craft['id']; ?>">
                                <input type="hidden" name="featured" value="<?php echo isset($craft['featured']) ? $craft['featured'] : 0; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info w-100" title="Toggle Featured" <?php echo !isset($craft['featured']) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            
                            <form method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to delete this craft? This action cannot be undone.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="craft_id" value="<?php echo $craft['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Delete Craft">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
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
