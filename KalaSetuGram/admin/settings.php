<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - KalaSetuGram Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .setting-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .setting-item:last-child {
            border-bottom: none;
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
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="settings.php">
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
                <h1 class="h3 mb-0">Settings</h1>
                <p class="text-muted">Configure system settings and preferences</p>
            </div>
        </div>

        <!-- General Settings -->
        <div class="settings-card">
            <h5 class="mb-4">
                <i class="fas fa-cog me-2"></i>
                General Settings
            </h5>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Site Name</h6>
                        <p class="text-muted mb-0">The name of your website</p>
                    </div>
                    <div>
                        <input type="text" class="form-control" value="KalaSetuGram" style="width: 200px;">
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Site Description</h6>
                        <p class="text-muted mb-0">Brief description of your platform</p>
                    </div>
                    <div>
                        <textarea class="form-control" rows="2" style="width: 300px;">Traditional crafts marketplace connecting artisans with customers</textarea>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Contact Email</h6>
                        <p class="text-muted mb-0">Primary contact email address</p>
                    </div>
                    <div>
                        <input type="email" class="form-control" value="info@kalasetugramdb.com" style="width: 250px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Settings -->
        <div class="settings-card">
            <h5 class="mb-4">
                <i class="fas fa-credit-card me-2"></i>
                Payment Settings
            </h5>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Payment Gateway</h6>
                        <p class="text-muted mb-0">Select your payment processor</p>
                    </div>
                    <div>
                        <select class="form-select" style="width: 200px;">
                            <option>Razorpay</option>
                            <option>PayU</option>
                            <option>Paytm</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Currency</h6>
                        <p class="text-muted mb-0">Default currency for transactions</p>
                    </div>
                    <div>
                        <select class="form-select" style="width: 150px;">
                            <option>INR (₹)</option>
                            <option>USD ($)</option>
                            <option>EUR (€)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Platform Commission</h6>
                        <p class="text-muted mb-0">Commission percentage on sales</p>
                    </div>
                    <div>
                        <div class="input-group" style="width: 150px;">
                            <input type="number" class="form-control" value="5" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="settings-card">
            <h5 class="mb-4">
                <i class="fas fa-bell me-2"></i>
                Notification Settings
            </h5>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Email Notifications</h6>
                        <p class="text-muted mb-0">Send email notifications for orders</p>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">SMS Notifications</h6>
                        <p class="text-muted mb-0">Send SMS updates to customers</p>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Admin Alerts</h6>
                        <p class="text-muted mb-0">Receive alerts for new orders</p>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="settings-card">
            <h5 class="mb-4">
                <i class="fas fa-shield-alt me-2"></i>
                Security Settings
            </h5>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Two-Factor Authentication</h6>
                        <p class="text-muted mb-0">Enable 2FA for admin accounts</p>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Session Timeout</h6>
                        <p class="text-muted mb-0">Auto logout after inactivity</p>
                    </div>
                    <div>
                        <select class="form-select" style="width: 150px;">
                            <option>30 minutes</option>
                            <option>1 hour</option>
                            <option>2 hours</option>
                            <option>Never</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="text-end">
            <button class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>
                Save Settings
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
