<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get all artisans
$artisans = getAllArtisans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meet Our Artisans - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Artisans Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Meet Our Artisans</h1>
                    <p class="lead text-muted">Discover the master craftspeople preserving centuries-old traditions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Artisans Grid -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Featured Artisan 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Rama Krishna" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Rama Krishna</h5>
                        <p class="text-primary fw-semibold mb-2">Kondapalli Toy Maker</p>
                        <p class="text-muted small mb-3">"Preserving our heritage through colorful wooden creations for over 30 years. Each toy tells a story of our rich culture."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Krishna District</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 30+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-award text-primary me-1"></i> GI Tag Certified</p>
                        </div>
                        <a href="artisan-profile.php?id=1" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
                
                <!-- Featured Artisan 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Lakshmi Devi" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Lakshmi Devi</h5>
                        <p class="text-primary fw-semibold mb-2">Kalamkari Artist</p>
                        <p class="text-muted small mb-3">"Each stroke tells a story of our ancient mythology and cultural heritage. My art connects the past with the present."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Chittoor District</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 25+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-award text-primary me-1"></i> National Award Winner</p>
                        </div>
                        <a href="artisan-profile.php?id=2" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
                
                <!-- Featured Artisan 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Venkat Reddy" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Venkat Reddy</h5>
                        <p class="text-primary fw-semibold mb-2">Etikoppaka Craftsman</p>
                        <p class="text-muted small mb-3">"Creating eco-friendly lacquer toys with natural vegetable dyes. Sustainability meets tradition in every piece."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Visakhapatnam District</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 20+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-leaf text-primary me-1"></i> Eco-Friendly Certified</p>
                        </div>
                        <a href="artisan-profile.php?id=3" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
                
                <!-- Featured Artisan 4 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Sita Mahalakshmi" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Sita Mahalakshmi</h5>
                        <p class="text-primary fw-semibold mb-2">Pochampally Weaver</p>
                        <p class="text-muted small mb-3">"Weaving dreams into reality with traditional Ikat patterns. Every thread carries the essence of our ancestors."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Nalgonda District</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 35+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-award text-primary me-1"></i> UNESCO Recognition</p>
                        </div>
                        <a href="artisan-profile.php?id=4" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
                
                <!-- Featured Artisan 5 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Ravi Kumar" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Ravi Kumar</h5>
                        <p class="text-primary fw-semibold mb-2">Bidriware Artist</p>
                        <p class="text-muted small mb-3">"Metal and silver dance together to create timeless pieces of art. Each design reflects our royal heritage."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Hyderabad</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 28+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-crown text-primary me-1"></i> Royal Patronage</p>
                        </div>
                        <a href="artisan-profile.php?id=5" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
                
                <!-- Featured Artisan 6 -->
                <div class="col-lg-4 col-md-6">
                    <div class="artisan-card bg-white rounded-3 shadow-lg p-4 h-100 text-center">
                        <img src="images/working1.jpg" alt="Padma Devi" class="rounded-3 shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="fw-bold text-dark">Padma Devi</h5>
                        <p class="text-primary fw-semibold mb-2">Nirmal Painter</p>
                        <p class="text-muted small mb-3">"Bringing mythological stories to life through vibrant colors and intricate brushwork. Art is my devotion."</p>
                        <div class="artisan-details">
                            <p class="small mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Nirmal, Telangana</p>
                            <p class="small mb-1"><i class="fas fa-calendar text-primary me-1"></i> 22+ Years Experience</p>
                            <p class="small mb-3"><i class="fas fa-palette text-primary me-1"></i> Traditional Techniques</p>
                        </div>
                        <a href="artisan-profile.php?id=6" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        <h3 class="text-dark mb-3">Become an Artisan Partner</h3>
                        <p class="text-muted mb-4">Join our community of skilled craftspeople and share your art with the world</p>
                        <a href="auth/register.php?role=artisan" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-handshake me-2"></i>Join as Artisan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
