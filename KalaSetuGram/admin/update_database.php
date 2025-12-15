<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pdo = getConnection();
$updates = [];
$errors = [];

try {
    // Check and add missing columns to crafts table
    $result = $pdo->query("SHOW COLUMNS FROM crafts LIKE 'featured'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE crafts ADD COLUMN featured BOOLEAN DEFAULT FALSE");
        $updates[] = "Added 'featured' column to crafts table";
    }
    
    // Update crafts status enum to include 'draft'
    $pdo->exec("ALTER TABLE crafts MODIFY COLUMN status ENUM('active', 'inactive', 'draft') DEFAULT 'active'");
    $updates[] = "Updated crafts status enum to include 'draft'";
    
    // Check and add missing columns to users table
    $result = $pdo->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        $updates[] = "Added 'status' column to users table";
    }
    
    // Check and add missing columns to artisans table
    $result = $pdo->query("SHOW COLUMNS FROM artisans LIKE 'specialization'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE artisans ADD COLUMN specialization VARCHAR(200)");
        $updates[] = "Added 'specialization' column to artisans table";
    }
    
    $result = $pdo->query("SHOW COLUMNS FROM artisans LIKE 'location'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE artisans ADD COLUMN location VARCHAR(200)");
        $updates[] = "Added 'location' column to artisans table";
    }
    
    $result = $pdo->query("SHOW COLUMNS FROM artisans LIKE 'verified'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE artisans ADD COLUMN verified BOOLEAN DEFAULT FALSE");
        $updates[] = "Added 'verified' column to artisans table";
    }
    
    // Check and add missing columns to orders table
    $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        $updates[] = "Added 'status' column to orders table";
    }
    
    // Create coupons table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_amount DECIMAL(10,2) DEFAULT 0,
        max_uses INT DEFAULT 100,
        used_count INT DEFAULT 0,
        expires_at DATETIME,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $updates[] = "Ensured coupons table exists with correct structure";
    
    // Update existing data
    $pdo->exec("UPDATE crafts SET featured = FALSE WHERE featured IS NULL");
    $pdo->exec("UPDATE users SET status = 'active' WHERE status IS NULL");
    $pdo->exec("UPDATE artisans SET verified = FALSE WHERE verified IS NULL");
    $updates[] = "Updated existing records with default values";
    
} catch (Exception $e) {
    $errors[] = "Error updating database: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - KalaSetuGram Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body style="background: #f8f9fa;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-database me-2"></i>
                            Database Update Results
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($updates)): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Successfully Updated:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($updates as $update): ?>
                                        <li><?php echo htmlspecialchars($update); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Errors:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Database Update Complete!</strong> Your admin pages should now work properly.
                        </div>
                        
                        <div class="text-center">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Go to Admin Dashboard
                            </a>
                            <a href="../index.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-home me-2"></i>
                                Back to Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
