<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/avatar_helper.php';

// Get available artisans for adoption
$pdo = getConnection();

$artisans = $pdo->query("
    SELECT a.*, u.name, u.location, u.phone, 
           COUNT(c.id) as craft_count,
           AVG(c.price) as avg_price,
           (SELECT COUNT(*) FROM adopt_artisan aa WHERE aa.artisan_id = a.id AND aa.status = 'active') as current_supporters
    FROM artisans a 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN crafts c ON a.id = c.artisan_id AND c.status = 'active'
    WHERE a.verification_status = 'verified'
    GROUP BY a.id 
    ORDER BY current_supporters ASC, a.created_at DESC
")->fetchAll();

// Handle adoption form submission
if ($_POST && isLoggedIn()) {
    $artisanId = intval($_POST['artisan_id']);
    $monthlyAmount = floatval($_POST['monthly_amount']);
    $duration = intval($_POST['duration']); // months
    
    if ($artisanId && $monthlyAmount >= 500 && $duration >= 1) {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+$duration months"));
        
        $stmt = $pdo->prepare("
            INSERT INTO adopt_artisan (user_id, artisan_id, monthly_amount, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $artisanId, $monthlyAmount, $startDate, $endDate])) {
            $success = "Thank you for adopting an artisan! Your support will make a real difference.";
        } else {
            $error = "Failed to process adoption. Please try again.";
        }
    } else {
        $error = "Please fill all fields correctly. Minimum support amount is ₹500/month.";
    }
}

// Get user's current adoptions if logged in
$userAdoptions = [];
if (isLoggedIn()) {
    $userAdoptions = $pdo->prepare("
        SELECT aa.*, a.craft_type, u.name as artisan_name 
        FROM adopt_artisan aa 
        JOIN artisans a ON aa.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE aa.user_id = ? AND aa.status = 'active'
        ORDER BY aa.created_at DESC
    ");
    $userAdoptions->execute([$_SESSION['user_id']]);
    $userAdoptions = $userAdoptions->fetchAll();
}

// Impact statistics (live or 0)
$impactArtisansSupported = 0;
$impactMonthlySupport = 0;
$impactActiveSupporters = 0;
$impactCraftForms = 0;

try {
    // Artisans supported: distinct artisans with active adoptions
    $stmt = $pdo->query("SELECT COUNT(DISTINCT artisan_id) FROM adopt_artisan WHERE status = 'active'");
    $impactArtisansSupported = (int) $stmt->fetchColumn();

    // Monthly support: sum of monthly_amount for active adoptions
    $stmt = $pdo->query("SELECT COALESCE(SUM(monthly_amount), 0) FROM adopt_artisan WHERE status = 'active'");
    $impactMonthlySupport = (float) $stmt->fetchColumn();

    // Active supporters: distinct users with active adoptions
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM adopt_artisan WHERE status = 'active'");
    $impactActiveSupporters = (int) $stmt->fetchColumn();

    // Craft forms preserved: distinct craft types among artisans with at least one active adoption
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT a.craft_type)
        FROM adopt_artisan aa
        JOIN artisans a ON aa.artisan_id = a.id
        WHERE aa.status = 'active' AND a.craft_type IS NOT NULL AND a.craft_type != ''
    ");
    $impactCraftForms = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    // If anything fails, keep impact numbers at 0 without breaking the page
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopt an Artisan - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .adopt-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 80px 0;
            margin-top: -76px;
            padding-top: 156px;
            position: relative;
            overflow: hidden;
        }
        
        .adopt-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern-overlay.png') repeat;
            opacity: 0.1;
        }
        
        .adopt-container {
            padding: 60px 0;
        }
        
        .program-intro {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            padding: 50px;
            text-align: center;
            margin-bottom: 60px;
        }
        
        .intro-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 2.5rem;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .benefit-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-medium);
        }
        
        .benefit-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
        }
        
        .artisan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .artisan-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .artisan-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-heavy);
        }
        
        .artisan-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        
        .artisan-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            margin: 0 auto 15px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .support-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .artisan-content {
            padding: 25px;
        }
        
        .artisan-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .adoption-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .amount-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .amount-btn {
            padding: 10px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
        }
        
        .amount-btn.selected,
        .amount-btn:hover {
            border-color: var(--primary-color);
            background: var(--accent-color);
            color: var(--primary-color);
        }
        
        .user-adoptions {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .adoption-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            margin-bottom: 15px;
        }
        
        .impact-section {
            background: linear-gradient(135deg, var(--accent-color), #fff);
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            margin: 60px 0;
        }
        
        .impact-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .impact-stat {
            text-align: center;
        }
        
        .impact-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .artisan-grid {
                grid-template-columns: 1fr;
            }
            
            .artisan-stats {
                grid-template-columns: 1fr;
            }
            
            .amount-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Adopt Header -->
    <section class="adopt-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">Adopt an Artisan</h1>
                    <p class="lead mb-0">Support traditional craftspeople and help preserve cultural heritage for future generations</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container adopt-container">
        <!-- Program Introduction -->
        <div class="program-intro">
            <div class="intro-icon">
                <i class="fas fa-hands-helping"></i>
            </div>
            <h2 class="mb-4">Empower Artisans, Preserve Heritage</h2>
            <p class="lead mb-4">
                Our Adopt-an-Artisan program creates a direct connection between you and traditional craftspeople, 
                providing them with steady income while preserving centuries-old art forms.
            </p>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>Direct Impact</h5>
                    <p>Your support goes directly to artisans, helping them sustain their craft and livelihood.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h5>Skill Preservation</h5>
                    <p>Help preserve traditional techniques and pass them on to the next generation.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Community Building</h5>
                    <p>Connect with artisans and their communities, learning about their culture and traditions.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h5>Exclusive Access</h5>
                    <p>Get first access to new creations and special pieces from your adopted artisan.</p>
                </div>
            </div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- User's Current Adoptions -->
        <?php if (isLoggedIn() && !empty($userAdoptions)): ?>
        <div class="user-adoptions">
            <h4 class="mb-4">
                <i class="fas fa-heart text-danger me-2"></i>
                Your Adopted Artisans
            </h4>
            
            <?php foreach ($userAdoptions as $adoption): ?>
            <div class="adoption-item">
                <div class="flex-grow-1">
                    <h6 class="mb-1"><?php echo htmlspecialchars($adoption['artisan_name']); ?></h6>
                    <small class="text-muted"><?php echo htmlspecialchars($adoption['craft_type']); ?></small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success"><?php echo formatPrice($adoption['monthly_amount']); ?>/month</div>
                    <small class="text-muted">Since <?php echo date('M Y', strtotime($adoption['start_date'])); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Available Artisans -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Available Artisans</h3>
            <span class="text-muted"><?php echo count($artisans); ?> artisans available for adoption</span>
        </div>
        
        <div class="artisan-grid">
            <?php foreach ($artisans as $artisan): ?>
            <div class="artisan-card">
                <div class="artisan-header">
                    <div class="support-badge">
                        <?php echo $artisan['current_supporters']; ?> supporters
                    </div>
                    
                    <div class="mb-2" style="display: flex; justify-content: center;">
                        <img src="images/working1.jpg" alt="<?php echo htmlspecialchars($artisan['name']); ?>" class="rounded-circle shadow" style="width: 90px; height: 90px; object-fit: cover;">
                    </div>
                    
                    <h5 class="mb-1"><?php echo htmlspecialchars($artisan['name']); ?></h5>
                    <small><?php echo htmlspecialchars($artisan['craft_type']); ?> Artisan</small>
                </div>
                
                <div class="artisan-content">
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <?php echo htmlspecialchars($artisan['location']); ?>
                    </div>
                    
                    <div class="artisan-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $artisan['experience_years'] ?: '10+'; ?></div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $artisan['craft_count']; ?></div>
                            <div class="stat-label">Active Crafts</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo formatPrice($artisan['avg_price'] ?: 1500); ?></div>
                            <div class="stat-label">Avg. Price</div>
                        </div>
                    </div>
                    
                    <?php if ($artisan['bio']): ?>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars(substr($artisan['bio'], 0, 100)) . '...'; ?></p>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="adoption-form">
                        <form method="POST" action="">
                            <input type="hidden" name="artisan_id" value="<?php echo $artisan['id']; ?>">
                            
                            <label class="form-label fw-bold">Monthly Support Amount:</label>
                            <div class="amount-options">
                                <div class="amount-btn" onclick="selectAmount(500, this)">₹500</div>
                                <div class="amount-btn" onclick="selectAmount(1000, this)">₹1,000</div>
                                <div class="amount-btn" onclick="selectAmount(2000, this)">₹2,000</div>
                                <div class="amount-btn" onclick="selectAmount(5000, this)">₹5,000</div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="monthly_amount" 
                                           placeholder="Custom amount" min="500" required>
                                </div>
                                <div class="col-6">
                                    <select name="duration" class="form-select" required>
                                        <option value="">Duration</option>
                                        <option value="3">3 months</option>
                                        <option value="6">6 months</option>
                                        <option value="12">1 year</option>
                                        <option value="24">2 years</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 mt-3">
                                <i class="fas fa-heart me-2"></i>
                                Adopt This Artisan
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="text-center">
                        <a href="auth/login.php" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login to Adopt
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Impact Section -->
        <div class="impact-section">
            <h3 class="mb-4">Our Impact So Far</h3>
            <p class="lead mb-0">
                Together, we're making a real difference in preserving traditional crafts and supporting artisan communities.
            </p>
            
            <div class="impact-stats">
                <div class="impact-stat">
                    <div class="impact-number"><?php echo $impactArtisansSupported; ?></div>
                    <div>Artisans Supported</div>
                </div>
                
                <div class="impact-stat">
                    <div class="impact-number"><?php echo $impactMonthlySupport > 0 ? formatPrice($impactMonthlySupport) . '/mo' : '0'; ?></div>
                    <div>Monthly Support</div>
                </div>
                
                <div class="impact-stat">
                    <div class="impact-number"><?php echo $impactActiveSupporters; ?></div>
                    <div>Active Supporters</div>
                </div>
                
                <div class="impact-stat">
                    <div class="impact-number"><?php echo $impactCraftForms; ?></div>
                    <div>Craft Forms Preserved</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        function selectAmount(amount, element) {
            // Remove selected class from all buttons
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked button
            element.classList.add('selected');
            
            // Set the amount in the input field
            const form = element.closest('.adoption-form');
            const amountInput = form.querySelector('input[name="monthly_amount"]');
            amountInput.value = amount;
        }
        
        // Initialize animations on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe artisan cards for animation
            document.querySelectorAll('.artisan-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
