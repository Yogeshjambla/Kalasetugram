<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an artisan
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Get or create artisan profile
$artisan = $pdo->prepare("SELECT * FROM artisans WHERE user_id = ?");
$artisan->execute([$userId]);
$artisan = $artisan->fetch();

// If user doesn't have artisan profile, create one
if (!$artisan && $_SESSION['user_role'] === 'artisan') {
    $pdo->prepare("INSERT INTO artisans (user_id, craft_type, district) VALUES (?, 'General', 'Not specified')")
        ->execute([$userId]);
    
    $artisan = $pdo->prepare("SELECT * FROM artisans WHERE user_id = ?");
    $artisan->execute([$userId]);
    $artisan = $artisan->fetch();
}

// Get artisan statistics
$stats = [
    'crafts' => $pdo->prepare("SELECT COUNT(*) FROM crafts WHERE artisan_id = ?"),
    'orders' => $pdo->prepare("SELECT COUNT(*) FROM order_items oi JOIN crafts c ON oi.craft_id = c.id WHERE c.artisan_id = ?"),
    'revenue' => $pdo->prepare("SELECT COALESCE(SUM(oi.total), 0) FROM order_items oi JOIN crafts c ON oi.craft_id = c.id JOIN orders o ON oi.order_id = o.id WHERE c.artisan_id = ? AND o.payment_status = 'completed'"),
    'supporters' => $pdo->prepare("SELECT COUNT(*) FROM adopt_artisan WHERE artisan_id = ? AND status = 'active'")
];

foreach ($stats as $key => $stmt) {
    $stmt->execute([$artisan['id'] ?? 0]);
    $stats[$key] = $stmt->fetchColumn();
}

// Get recent crafts
$recentCrafts = $pdo->prepare("
    SELECT c.*, cc.name as category_name,
           (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
    FROM crafts c 
    JOIN craft_categories cc ON c.category_id = cc.id 
    WHERE c.artisan_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$recentCrafts->execute([$artisan['id'] ?? 0]);
$recentCrafts = $recentCrafts->fetchAll();

// Get recent orders
$recentOrders = $pdo->prepare("
    SELECT o.*, u.name as customer_name, oi.quantity, c.title as craft_title
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN crafts c ON oi.craft_id = c.id
    JOIN users u ON o.user_id = u.id
    WHERE c.artisan_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recentOrders->execute([$artisan['id'] ?? 0]);
$recentOrders = $recentOrders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Dashboard - KalaSetuGram</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .dashboard-sidebar {
            background: linear-gradient(135deg, #d4a574, #8b4513);
            min-height: 100vh;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        
        .dashboard-content {
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
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
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
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #8b4513;
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .recent-items {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #d4a574;
            color: white;
            padding: 20px 25px;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .dashboard-sidebar.show {
                transform: translateX(0);
            }
            
            .dashboard-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Artisan Sidebar -->
    <div class="dashboard-sidebar" id="artisanSidebar">
        <div class="sidebar-header">
            <h4 class="mb-1">
                <i class="fas fa-palette me-2"></i>
                KalaSetuGram
            </h4>
            <small>Artisan Dashboard</small>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#profile">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#crafts">
                        <i class="fas fa-palette"></i>
                        My Crafts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#orders">
                        <i class="fas fa-shopping-cart"></i>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#supporters">
                        <i class="fas fa-heart"></i>
                        My Supporters
                    </a>
                </li>
            </ul>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i>
                        View Website
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted mb-0">Manage your artisan profile and crafts</p>
            </div>
            <div>
                <button class="btn btn-outline-primary d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['crafts']); ?></div>
                    <div class="stats-label">My Crafts</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['orders']); ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stats-number">₹<?php echo number_format($stats['revenue']); ?></div>
                    <div class="stats-label">Total Revenue</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['supporters']); ?></div>
                    <div class="stats-label">Supporters</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Crafts -->
            <div class="col-lg-6">
                <div class="recent-items">
                    <h5 class="table-header">Recent Crafts</h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Craft</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCrafts as $craft): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($craft['primary_image']): ?>
                                            <img src="<?php echo htmlspecialchars($craft['primary_image']); ?>" 
                                                 alt="Craft" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($craft['title']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($craft['category_name']); ?></td>
                                    <td class="fw-bold text-primary">₹<?php echo number_format($craft['price']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $craft['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($craft['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="col-lg-6">
                <div class="recent-items">
                    <h5 class="table-header">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Craft</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['craft_title']); ?></td>
                                    <td class="fw-bold text-primary">₹<?php echo number_format($order['total_amount']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="add-craft.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Add New Craft
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="crafts.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-eye me-2"></i>
                                    View All Crafts
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="profile-edit.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Profile
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="adopt-artisan.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-heart me-2"></i>
                                    View Adoption
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            document.getElementById('artisanSidebar').classList.toggle('show');
        }
    </script>
</body>
</html>
