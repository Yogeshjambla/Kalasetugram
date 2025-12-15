<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$pdo = getConnection();

// Get analytics data
$analytics = [
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'completed'")->fetchColumn(),
    'monthly_revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn(),
    'total_artisans' => $pdo->query("SELECT COUNT(*) FROM artisans")->fetchColumn(),
    'total_crafts' => $pdo->query("SELECT COUNT(*) FROM crafts")->fetchColumn()
];

// Monthly sales data
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

// Top categories
$topCategories = $pdo->query("
    SELECT cc.name, COUNT(c.id) as craft_count
    FROM craft_categories cc
    LEFT JOIN crafts c ON cc.id = c.category_id
    GROUP BY cc.id, cc.name
    ORDER BY craft_count DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - KalaSetuGram Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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
                    <a class="nav-link active" href="reports.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Reports & Analytics</h1>
                <p class="text-muted">Business insights and performance metrics</p>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stats-number">₹<?php echo number_format($analytics['total_revenue']); ?></div>
                    <div class="stats-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-number">₹<?php echo number_format($analytics['monthly_revenue']); ?></div>
                    <div class="stats-label">This Month</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($analytics['total_orders']); ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($analytics['total_users']); ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="chart-container">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-line me-2"></i>
                        Monthly Revenue Trend
                    </h5>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="chart-container">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-pie me-2"></i>
                        Top Categories
                    </h5>
                    <canvas id="categoryChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-palette me-2"></i>
                            Craft Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-primary"><?php echo number_format($analytics['total_crafts']); ?></h3>
                                <p class="text-muted mb-0">Total Crafts</p>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success"><?php echo number_format($analytics['total_artisans']); ?></h3>
                                <p class="text-muted mb-0">Active Artisans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Performance Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-info">
                                    <?php echo $analytics['total_orders'] > 0 ? '₹' . number_format($analytics['total_revenue'] / $analytics['total_orders']) : '₹0'; ?>
                                </h3>
                                <p class="text-muted mb-0">Avg Order Value</p>
                            </div>
                            <div class="col-6">
                                <h3 class="text-warning">
                                    <?php echo $analytics['total_artisans'] > 0 ? number_format($analytics['total_crafts'] / $analytics['total_artisans'], 1) : '0'; ?>
                                </h3>
                                <p class="text-muted mb-0">Crafts per Artisan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return '"' . date('M Y', strtotime($item['month'] . '-01')) . '"'; }, $monthlySales)); ?>],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: [<?php echo implode(',', array_column($monthlySales, 'revenue')); ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return '"' . $item['name'] . '"'; }, $topCategories)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($topCategories, 'craft_count')); ?>],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
