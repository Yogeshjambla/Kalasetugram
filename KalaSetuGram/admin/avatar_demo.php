<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/avatar_helper.php';

// Require admin access
requireAdmin();

$pdo = getConnection();

// Get some sample artisans and users
$artisans = $pdo->query("
    SELECT a.*, u.name 
    FROM artisans a 
    JOIN users u ON a.user_id = u.id 
    LIMIT 10
")->fetchAll();

$users = $pdo->query("
    SELECT * FROM users 
    WHERE role != 'admin' 
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avatar Demo - KalaSetuGram Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .demo-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .avatar-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .avatar-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .size-demo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="demo-card text-center">
                    <h1 class="mb-3">
                        <i class="fas fa-palette me-2"></i>
                        Emoji Avatar System Demo
                    </h1>
                    <p class="lead text-muted">
                        User-friendly emoji avatars for artisans and users - no more identical placeholder images!
                    </p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                <!-- Artisan Avatars -->
                <div class="demo-card">
                    <h3 class="mb-4">
                        <i class="fas fa-users me-2"></i>
                        Artisan Avatars (Craft-themed Emojis)
                    </h3>
                    <p class="text-muted mb-4">
                        Each artisan gets a unique craft-related emoji and colorful gradient background based on their ID.
                    </p>
                    
                    <div class="avatar-grid">
                        <?php foreach ($artisans as $artisan): ?>
                        <div class="avatar-item">
                            <div class="mb-3">
                                <?php echo generateArtisanAvatar($artisan['id'], $artisan['name'], 80, '2rem'); ?>
                            </div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($artisan['name']); ?></h6>
                            <small class="text-muted">ID: <?php echo $artisan['id']; ?></small>
                            <div class="size-demo">
                                <?php echo generateArtisanAvatar($artisan['id'], $artisan['name'], 30, '0.8rem'); ?>
                                <?php echo generateArtisanAvatar($artisan['id'], $artisan['name'], 50, '1.2rem'); ?>
                                <?php echo generateArtisanAvatar($artisan['id'], $artisan['name'], 70, '1.8rem'); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- User Avatars -->
                <div class="demo-card">
                    <h3 class="mb-4">
                        <i class="fas fa-user-friends me-2"></i>
                        User Avatars (Friendly Face Emojis)
                    </h3>
                    <p class="text-muted mb-4">
                        Regular users get friendly face emojis with beautiful gradient backgrounds.
                    </p>
                    
                    <div class="avatar-grid">
                        <?php foreach ($users as $user): ?>
                        <div class="avatar-item">
                            <div class="mb-3">
                                <?php echo generateUserAvatar($user['id'], $user['name'], 80, '2rem'); ?>
                            </div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                            <small class="text-muted">
                                <?php echo ucfirst($user['role']); ?> • ID: <?php echo $user['id']; ?>
                            </small>
                            <div class="size-demo">
                                <?php echo generateUserAvatar($user['id'], $user['name'], 30, '0.8rem'); ?>
                                <?php echo generateUserAvatar($user['id'], $user['name'], 50, '1.2rem'); ?>
                                <?php echo generateUserAvatar($user['id'], $user['name'], 70, '1.8rem'); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Features -->
                <div class="demo-card">
                    <h3 class="mb-4">
                        <i class="fas fa-star me-2"></i>
                        Avatar System Features
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div>
                                    <h6>Unique & Consistent</h6>
                                    <p class="text-muted mb-0">Each user gets the same avatar every time based on their ID</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    <i class="fas fa-palette text-primary"></i>
                                </div>
                                <div>
                                    <h6>Colorful Gradients</h6>
                                    <p class="text-muted mb-0">Beautiful gradient backgrounds with 15 different color combinations</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    <i class="fas fa-smile text-warning"></i>
                                </div>
                                <div>
                                    <h6>User-Friendly Emojis</h6>
                                    <p class="text-muted mb-0">Craft emojis for artisans, friendly faces for users</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    <i class="fas fa-mobile-alt text-info"></i>
                                </div>
                                <div>
                                    <h6>Responsive Sizes</h6>
                                    <p class="text-muted mb-0">Easily customizable sizes for different contexts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Implementation -->
                <div class="demo-card">
                    <h3 class="mb-4">
                        <i class="fas fa-code me-2"></i>
                        How to Use
                    </h3>
                    
                    <div class="bg-light p-3 rounded">
                        <h6>For Artisans:</h6>
                        <code>generateArtisanAvatar($artisanId, $name, $size, $fontSize)</code>
                        
                        <h6 class="mt-3">For Users:</h6>
                        <code>generateUserAvatar($userId, $name, $size, $fontSize)</code>
                        
                        <h6 class="mt-3">Example:</h6>
                        <code>&lt;?php echo generateArtisanAvatar(1, 'Rama Krishna', 60, '1.5rem'); ?&gt;</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
