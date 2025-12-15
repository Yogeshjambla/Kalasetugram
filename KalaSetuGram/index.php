<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/avatar_helper.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get featured crafts for homepage
$featuredCrafts = getFeaturedCrafts(6);

// Get some artisans for homepage spotlight
$homepageArtisans = getAllArtisans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KalaSetuGram - Bridging Tradition with Technology</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- AR.js for Augmented Reality -->
    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/AR-js-org/AR.js/aframe/build/aframe-ar.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%);">
        <div class="hero-overlay">
            <div class="container">
                <div class="row align-items-center" style="min-height: 70vh;">
                    <div class="col-lg-6 text-dark">
                        <h1 class="hero-title display-3 fw-bold mb-4 text-dark">Bridging Tradition with Technology</h1>
                        <p class="hero-subtitle fs-5 mb-5 text-muted">Discover authentic Andhra Pradesh crafts, support local artisans, and experience heritage through cutting-edge AR technology.</p>
                        <div class="hero-buttons d-flex flex-column flex-sm-row gap-3">
                            <a href="crafts.php" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg">Explore Andhra's Crafts</a>
                            <a href="heritage-stories.php" class="btn btn-outline-primary btn-lg px-4 py-3 fw-bold">Meet Artisans</a>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-5 mt-lg-0">
                        <div class="hero-image position-relative">
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient rounded-3 opacity-25" style="background: linear-gradient(45deg, #F77737, #E4405F); filter: blur(20px); transform: scale(1.1);"></div>
                            <img src="images/andhra pardesh.jpg" alt="Traditional Andhra Pradesh Kalamkari Map" 
                                 class="img-fluid rounded-3 shadow-lg position-relative" 
                                 style="max-width: 450px; margin: 0 auto; display: block;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Craft Clusters -->
    <section class="featured-crafts py-5" style="background: linear-gradient(135deg, #fef7ff 0%, #f0f9ff 100%);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Featured Craft Clusters</h2>
                    <p class="section-subtitle">Explore the rich heritage of Andhra Pradesh through our curated craft collections</p>
                </div>
            </div>
            <div class="row g-4">
                <?php if (!empty($featuredCrafts)): ?>
                    <?php foreach ($featuredCrafts as $craft): ?>
                        <div class="col-md-4">
                            <div class="craft-card h-100">
                                <div class="craft-image">
                                    <a href="craft-detail.php?id=<?php echo $craft['id']; ?>" class="d-block">
                                        <img src="<?php echo $craft['primary_image'] ? htmlspecialchars($craft['primary_image']) : 'https://via.placeholder.com/600x400/d4a574/ffffff?text=Craft+Image'; ?>" 
                                             alt="<?php echo htmlspecialchars($craft['title']); ?>" class="img-fluid">
                                    </a>
                                    <div class="craft-overlay">
                                        <span class="gi-tag">Featured</span>
                                    </div>
                                </div>
                                <div class="craft-content">
                                    <h4>
                                        <a href="craft-detail.php?id=<?php echo $craft['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($craft['title']); ?>
                                        </a>
                                    </h4>
                                    <p class="mb-2 text-muted">
                                        <?php echo htmlspecialchars($craft['category_name']); ?>
                                        <?php if (!empty($craft['artisan_name'])): ?>
                                            &bull; by <?php echo htmlspecialchars($craft['artisan_name']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (isset($craft['price'])): ?>
                                        <p class="fw-semibold mb-3"><?php echo formatPrice($craft['price']); ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex gap-2">
                                        <a href="craft-detail.php?id=<?php echo $craft['id']; ?>" class="btn btn-primary btn-sm">View Craft</a>
                                        <a href="crafts.php?<?php echo http_build_query(['category' => $craft['category_name']]); ?>" class="btn btn-outline-primary btn-sm">More like this</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Engaging placeholders shown until real featured crafts are added -->
                    <div class="col-md-3">
                        <div class="craft-card h-100">
                            <div class="craft-image">
                                <a href="crafts.php?<?php echo http_build_query(['search' => 'Kondapalli Toys']); ?>" class="d-block">
                                    <img src="images/Kondapalli Toys.jpg" 
                                         alt="Kondapalli Toys" class="img-fluid">
                                </a>
                                <div class="craft-overlay">
                                    <span class="gi-tag">Sample Cluster</span>
                                </div>
                            </div>
                            <div class="craft-content">
                                <h4>
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Kondapalli Toys']); ?>" class="text-decoration-none text-dark">Kondapalli Toys</a>
                                </h4>
                                <p class="mb-2 text-muted">Colorful wooden toys crafted from soft Tella Poniki wood in Krishna district.</p>
                                <div class="d-flex gap-2">
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Kondapalli Toys']); ?>" class="btn btn-primary btn-sm">Browse Crafts</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="craft-card h-100">
                            <div class="craft-image">
                                <a href="crafts.php?<?php echo http_build_query(['search' => 'Kalamkari']); ?>" class="d-block">
                                    <img src="images/Kalamkari Art.jpg" 
                                         alt="Kalamkari Art" class="img-fluid">
                                </a>
                                <div class="craft-overlay">
                                    <span class="gi-tag">Sample Cluster</span>
                                </div>
                            </div>
                            <div class="craft-content">
                                <h4>
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Kalamkari']); ?>" class="text-decoration-none text-dark">Kalamkari Art</a>
                                </h4>
                                <p class="mb-2 text-muted">Hand-painted textiles using natural dyes and mythological stories.</p>
                                <div class="d-flex gap-2">
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Kalamkari']); ?>" class="btn btn-primary btn-sm">Browse Crafts</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="craft-card h-100">
                            <div class="craft-image">
                                <a href="crafts.php?<?php echo http_build_query(['search' => 'Etikoppaka']); ?>" class="d-block">
                                    <img src="images/Etikoppaka Toys.jpg" 
                                         alt="Etikoppaka Toys" class="img-fluid">
                                </a>
                                <div class="craft-overlay">
                                    <span class="gi-tag">Sample Cluster</span>
                                </div>
                            </div>
                            <div class="craft-content">
                                <h4>
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Etikoppaka']); ?>" class="text-decoration-none text-dark">Etikoppaka Toys</a>
                                </h4>
                                <p class="mb-2 text-muted">Eco-friendly lacquer toys made with natural colors from Etikoppaka village.</p>
                                <div class="d-flex gap-2">
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Etikoppaka']); ?>" class="btn btn-primary btn-sm">Browse Crafts</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="craft-card h-100">
                            <div class="craft-image">
                                <a href="crafts.php?<?php echo http_build_query(['search' => 'Pochampally Ikat']); ?>" class="d-block">
                                    <img src="images/Pochampally Ikat.jpg" 
                                         alt="Pochampally Ikat" class="img-fluid">
                                </a>
                                <div class="craft-overlay">
                                    <span class="gi-tag">Sample Cluster</span>
                                </div>
                            </div>
                            <div class="craft-content">
                                <h4>
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Pochampally Ikat']); ?>" class="text-decoration-none text-dark">Pochampally Ikat</a>
                                </h4>
                                <p class="mb-2 text-muted">Iconic Ikat weave with intricate geometric patterns from Pochampally region.</p>
                                <div class="d-flex gap-2">
                                    <a href="crafts.php?<?php echo http_build_query(['search' => 'Pochampally Ikat']); ?>" class="btn btn-primary btn-sm">Browse Crafts</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Meet the Artisans -->
    <section class="artisans-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Meet the Artisans</h2>
                    <p class="section-subtitle">Connect with master craftspeople preserving centuries-old traditions</p>
                </div>
            </div>

            <div class="row g-4 justify-content-center">
                <?php if (!empty($homepageArtisans)): ?>
                    <?php 
                        $count = 0;
                        foreach ($homepageArtisans as $artisan): 
                            if ($count >= 3) break;
                            $count++;
                    ?>
                        <div class="col-md-4">
                            <div class="artisan-card text-center bg-white rounded-3 shadow-lg p-4 h-100">
                                <div class="mb-3 d-flex justify-content-center">
                                    <img src="images/working1.jpg" alt="<?php echo htmlspecialchars($artisan['artisan_name']); ?>" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($artisan['artisan_name']); ?></h5>
                                <p class="text-primary fw-semibold mb-2">
                                    <?php echo htmlspecialchars($artisan['craft_type'] ?? 'Artisan'); ?>
                                </p>
                                <p class="text-muted small mb-2">
                                    <?php if (!empty($artisan['district'])): ?>
                                        <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                        <?php echo htmlspecialchars($artisan['district']); ?>
                                    <?php elseif (!empty($artisan['artisan_location'])): ?>
                                        <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                        <?php echo htmlspecialchars($artisan['artisan_location']); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($artisan['bio'])): ?>
                                    <p class="text-muted small">"<?php echo htmlspecialchars($artisan['bio']); ?>"</p>
                                <?php else: ?>
                                    <p class="text-muted small">"Showcasing authentic craftsmanship from Andhra Pradesh."</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Simple, abstract placeholders if no artisans are registered yet -->
                    <div class="col-md-4">
                        <div class="artisan-card text-center bg-white rounded-3 shadow-lg p-4 h-100">
                            <div class="mb-3 d-flex justify-content-center">
                                <img src="images/working1.jpg" alt="Artisan" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold text-dark">Your Name Here</h5>
                            <p class="text-primary fw-semibold mb-2">Kondapalli Toy Maker</p>
                            <p class="text-muted small">"Join KalaSetuGram and bring your craft story to life."</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="artisan-card text-center bg-white rounded-3 shadow-lg p-4 h-100">
                            <div class="mb-3 d-flex justify-content-center">
                                <img src="images/working.jpg" alt="Artisan" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold text-dark">Your Name Here</h5>
                            <p class="text-primary fw-semibold mb-2">Kalamkari Artist</p>
                            <p class="text-muted small">"Share your heritage art with buyers across the world."</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="artisan-card text-center bg-white rounded-3 shadow-lg p-4 h-100">
                            <div class="mb-3 d-flex justify-content-center">
                                <img src="images/working.jpg" alt="Artisan" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold text-dark">Your Name Here</h5>
                            <p class="text-primary fw-semibold mb-2">Etikoppaka Craftsman</p>
                            <p class="text-muted small">"Eco-friendly crafts that celebrate traditional techniques."</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Interactive Heritage Map -->
    <section class="heritage-map py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title text-dark">Interactive Heritage Map</h2>
                    <p class="section-subtitle text-muted">Explore craft villages across Andhra Pradesh</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="map-container bg-white rounded-3 shadow-lg p-3 mb-4">
                        <div id="heritage-map" style="height: 500px; width: 100%; border-radius: 12px;"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="craft-locations">
                        <h5 class="text-dark mb-4"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Craft Locations</h5>
                        
                        <a href="crafts.php?<?php echo http_build_query(['search' => 'Kondapalli Toys']); ?>" class="text-decoration-none text-reset">
                            <div class="location-card bg-white rounded-3 shadow-sm p-4 mb-3 border-start border-4 border-danger">
                                <h6 class="text-danger"><i class="fas fa-map-pin me-2"></i>Kondapalli, Krishna District</h6>
                                <p class="mb-2 small"><strong>Specialty:</strong> Wooden Toys</p>
                                <p class="text-muted small">Famous for colorful wooden toys and figurines made from soft Tella Poniki wood.</p>
                            </div>
                        </a>
                        
                        <a href="crafts.php?<?php echo http_build_query(['search' => 'Kalamkari']); ?>" class="text-decoration-none text-reset">
                            <div class="location-card bg-white rounded-3 shadow-sm p-4 mb-3 border-start border-4 border-success">
                                <h6 class="text-success"><i class="fas fa-map-pin me-2"></i>Srikalahasti, Chittoor District</h6>
                                <p class="mb-2 small"><strong>Specialty:</strong> Kalamkari Art</p>
                                <p class="text-muted small">Ancient hand-painted textile art using natural dyes and depicting mythological stories.</p>
                            </div>
                        </a>
                        
                        <a href="crafts.php?<?php echo http_build_query(['search' => 'Etikoppaka']); ?>" class="text-decoration-none text-reset">
                            <div class="location-card bg-white rounded-3 shadow-sm p-4 mb-3 border-start border-4 border-warning">
                                <h6 class="text-warning"><i class="fas fa-map-pin me-2"></i>Etikoppaka, Visakhapatnam District</h6>
                                <p class="mb-2 small"><strong>Specialty:</strong> Lacquer Toys</p>
                                <p class="text-muted small">Eco-friendly toys made with natural lacquer and vegetable dyes.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials py-5" style="background: linear-gradient(135deg, #fef7ff 0%, #f0f9ff 100%);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title text-dark">What Our Customers Say</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"The AR experience made me feel like I was in the artisan's workshop. Amazing quality crafts!"</p>
                        <div class="testimonial-author">
                            <strong>Priya Sharma</strong>
                            <small>Mumbai</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Supporting artisans directly through this platform feels so meaningful. Beautiful crafts!"</p>
                        <div class="testimonial-author">
                            <strong>Rajesh Kumar</strong>
                            <small>Delhi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="stars mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"The cultural stories behind each craft make every purchase special and meaningful."</p>
                        <div class="testimonial-author">
                            <strong>Anita Reddy</strong>
                            <small>Bangalore</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <!-- Map Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map centered on Andhra Pradesh
            var map = L.map('heritage-map').setView([15.9129, 79.7400], 7);
            
            // Add beautiful light-colored tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            
            // Custom icons for different crafts
            var kondapalliIcon = L.divIcon({
                html: '<i class="fas fa-map-pin text-danger fs-3"></i>',
                iconSize: [30, 30],
                className: 'custom-div-icon'
            });
            
            var kalamkariIcon = L.divIcon({
                html: '<i class="fas fa-map-pin text-success fs-3"></i>',
                iconSize: [30, 30],
                className: 'custom-div-icon'
            });
            
            var etikoppakaIcon = L.divIcon({
                html: '<i class="fas fa-map-pin text-warning fs-3"></i>',
                iconSize: [30, 30],
                className: 'custom-div-icon'
            });
            
            // Add markers for craft locations
            L.marker([16.6190, 80.6210], {icon: kondapalliIcon})
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <h6 class="text-danger mb-2"><i class="fas fa-map-pin me-1"></i>Kondapalli, Krishna District</h6>
                        <p class="mb-1 small"><strong>Specialty:</strong> Wooden Toys</p>
                        <p class="text-muted small mb-0">Famous for colorful wooden toys and figurines made from soft Tella Poniki wood.</p>
                    </div>
                `);
            
            L.marker([13.7500, 79.1000], {icon: kalamkariIcon})
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <h6 class="text-success mb-2"><i class="fas fa-map-pin me-1"></i>Srikalahasti, Chittoor District</h6>
                        <p class="mb-1 small"><strong>Specialty:</strong> Kalamkari Art</p>
                        <p class="text-muted small mb-0">Ancient hand-painted textile art using natural dyes and depicting mythological stories.</p>
                    </div>
                `);
            
            L.marker([17.6500, 82.8400], {icon: etikoppakaIcon})
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <h6 class="text-warning mb-2"><i class="fas fa-map-pin me-1"></i>Etikoppaka, Visakhapatnam District</h6>
                        <p class="mb-1 small"><strong>Specialty:</strong> Lacquer Toys</p>
                        <p class="text-muted small mb-0">Eco-friendly toys made with natural lacquer and vegetable dyes.</p>
                    </div>
                `);
        });
    </script>
    
    <style>
        .custom-div-icon {
            background: none;
            border: none;
        }
        
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .leaflet-popup-tip {
            background: white;
        }
    </style>
</body>
</html>
