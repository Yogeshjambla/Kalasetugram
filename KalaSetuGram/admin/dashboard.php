<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Get dashboard statistics
$pdo = getConnection();

// Total counts
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalArtisans = $pdo->query("SELECT COUNT(*) FROM artisans")->fetchColumn();
$totalCrafts = $pdo->query("SELECT COUNT(*) FROM crafts")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Revenue statistics
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'completed'")->fetchColumn();
$monthlyRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

// Top selling crafts
$topCrafts = $pdo->query("
    SELECT c.title, c.price, cc.name as category, COUNT(oi.id) as sales_count, SUM(oi.total) as total_revenue
    FROM crafts c 
    JOIN craft_categories cc ON c.category_id = cc.id
    LEFT JOIN order_items oi ON c.id = oi.craft_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.payment_status = 'completed'
    GROUP BY c.id 
    ORDER BY sales_count DESC 
    LIMIT 5
")->fetchAll();

// Monthly sales data for chart
$monthlySales = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE payment_status = 'completed' 
    AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
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
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow-light);
            margin-bottom: 30px;
        }
        
        .recent-orders {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }
        
        .table-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 25px;
            margin: 0;
        }
        
        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce7ff; color: #004085; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        
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
                    <a class="nav-link active" href="dashboard.php">
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
    
    <!-- Admin Content -->
    <div class="admin-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($totalUsers); ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($totalArtisans); ?></div>
                    <div class="stats-label">Active Artisans</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($totalCrafts); ?></div>
                    <div class="stats-label">Total Crafts</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135d, #43e97b, #38f9d7);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($totalOrders); ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>
        </div>
        
        <!-- Revenue Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stats-number"><?php echo formatPrice($totalRevenue); ?></div>
                    <div class="stats-label">Total Revenue</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #a8edea, #fed6e3);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-number"><?php echo formatPrice($monthlyRevenue); ?></div>
                    <div class="stats-label">This Month's Revenue</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Sales Chart -->
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="mb-4">Monthly Sales Overview</h5>
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
            
            <!-- Top Selling Crafts -->
            <div class="col-lg-4">
                <div class="recent-orders">
                    <h5 class="table-header">Top Selling Crafts</h5>
                    <div class="p-3">
                        <?php foreach ($topCrafts as $craft): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($craft['title']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($craft['category']); ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary"><?php echo $craft['sales_count']; ?> sales</div>
                                <small class="text-muted"><?php echo formatPrice($craft['total_revenue'] ?: 0); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="recent-orders">
                    <h5 class="table-header">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                    <td class="fw-bold text-primary"><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($monthlySales, 'month')) . "'"; ?>],
                datasets: [{
                    label: 'Revenue',
                    data: [<?php echo implode(',', array_column($monthlySales, 'revenue')); ?>],
                    borderColor: '#d4a574',
                    backgroundColor: 'rgba(212, 165, 116, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Orders',
                    data: [<?php echo implode(',', array_column($monthlySales, 'order_count')); ?>],
                    borderColor: '#8b4513',
                    backgroundColor: 'rgba(139, 69, 19, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
        
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
        }
        
        // Auto-refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
