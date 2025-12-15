<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/avatar_helper.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'DESC';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get all categories for filter
$categories = getAllCategories();

// Get crafts with filters
$crafts = getAllCrafts($limit, $offset, $category, $search, $sortBy, $sortOrder);

// Get total count for pagination
$pdo = getConnection();
$countSql = "SELECT COUNT(*) FROM crafts c 
             JOIN craft_categories cc ON c.category_id = cc.id 
             WHERE c.status = 'active'";
$countParams = [];

if ($category) {
    $countSql .= " AND cc.name = ?";
    $countParams[] = $category;
}

if ($search) {
    $countSql .= " AND (c.title LIKE ? OR c.description LIKE ? OR cc.name LIKE ?)";
    $searchTerm = "%$search%";
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$totalCrafts = $stmt->fetchColumn();
$totalPages = ceil($totalCrafts / $limit);

// Check if user is logged in
$user = null;
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Craft Marketplace - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .marketplace-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0 40px;
            margin-top: -76px;
            padding-top: 136px;
        }
        
        .filters-sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 25px;
            margin-bottom: 30px;
            position: sticky;
            top: 100px;
        }
        
        .filter-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .filter-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .filter-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
            padding: 8px 0;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .filter-option:hover {
            color: var(--primary-color);
        }
        
        .filter-option input[type="checkbox"],
        .filter-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .craft-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .craft-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .craft-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }
        
        .craft-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        
        .craft-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .craft-card:hover .craft-image img {
            transform: scale(1.1);
        }
        
        .craft-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .gi-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .featured-badge {
            background: linear-gradient(135deg, #ffd700, #ffb347);
            color: #333;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .craft-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .craft-card:hover .craft-actions {
            opacity: 1;
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .action-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        .craft-content {
            padding: 20px;
        }
        
        .craft-category {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .craft-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .craft-artisan {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .craft-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .craft-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-add-cart {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .craft-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffd700;
        }
        
        .sort-controls {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-light);
            margin-bottom: 30px;
        }
        
        .results-info {
            color: #666;
            margin-bottom: 20px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        
        .page-link {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .craft-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .filters-sidebar {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Marketplace Header -->
    <section class="marketplace-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-3">Craft Marketplace</h1>
                    <p class="lead mb-0">Discover authentic handcrafted treasures from Andhra Pradesh's master artisans</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container py-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filters-sidebar">
                    <h5 class="filter-title">
                        <i class="fas fa-filter me-2"></i>
                        Filters
                    </h5>
                    
                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h6 class="filter-title">Categories</h6>
                        <div class="filter-option">
                            <input type="radio" name="category" value="" id="cat-all" 
                                   <?php echo empty($category) ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label for="cat-all">All Categories</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <input type="radio" name="category" value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                   id="cat-<?php echo $cat['id']; ?>"
                                   <?php echo $category === $cat['name'] ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label for="cat-<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <?php if ($cat['gi_tagged']): ?>
                                    <small class="text-danger">(GI Tagged)</small>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Range Filter -->
                    <div class="filter-section">
                        <h6 class="filter-title">Price Range</h6>
                        <div class="filter-option">
                            <input type="checkbox" id="price-1" value="0-500">
                            <label for="price-1">Under ₹500</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="price-2" value="500-1000">
                            <label for="price-2">₹500 - ₹1,000</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="price-3" value="1000-2500">
                            <label for="price-3">₹1,000 - ₹2,500</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="price-4" value="2500+">
                            <label for="price-4">Above ₹2,500</label>
                        </div>
                    </div>
                    
                    <!-- Special Features -->
                    <div class="filter-section">
                        <h6 class="filter-title">Special Features</h6>
                        <div class="filter-option">
                            <input type="checkbox" id="gi-tagged">
                            <label for="gi-tagged">GI Tagged</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="ar-enabled">
                            <label for="ar-enabled">AR Enabled</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="featured">
                            <label for="featured">Featured</label>
                        </div>
                    </div>
                    
                    <button class="btn btn-outline-primary w-100" onclick="clearFilters()">
                        <i class="fas fa-times me-2"></i>
                        Clear Filters
                    </button>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Sort Controls -->
                <div class="sort-controls">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="results-info">
                                Showing <?php echo count($crafts); ?> of <?php echo $totalCrafts; ?> crafts
                                <?php if ($search): ?>
                                    for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                <?php endif; ?>
                                <?php if ($category): ?>
                                    in <strong><?php echo htmlspecialchars($category); ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center">
                                <label class="me-2">Sort by:</label>
                                <select class="form-select" style="width: auto;" onchange="applySorting(this.value)">
                                    <option value="created_at-DESC" <?php echo ($sortBy === 'created_at' && $sortOrder === 'DESC') ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="created_at-ASC" <?php echo ($sortBy === 'created_at' && $sortOrder === 'ASC') ? 'selected' : ''; ?>>Oldest First</option>
                                    <option value="price-ASC" <?php echo ($sortBy === 'price' && $sortOrder === 'ASC') ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price-DESC" <?php echo ($sortBy === 'price' && $sortOrder === 'DESC') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="title-ASC" <?php echo ($sortBy === 'title' && $sortOrder === 'ASC') ? 'selected' : ''; ?>>Name: A to Z</option>
                                    <option value="title-DESC" <?php echo ($sortBy === 'title' && $sortOrder === 'DESC') ? 'selected' : ''; ?>>Name: Z to A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Crafts Grid -->
                <?php if (empty($crafts)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h4>No crafts found</h4>
                        <p>Try adjusting your search criteria or browse all categories.</p>
                        <a href="crafts.php" class="btn btn-primary">View All Crafts</a>
                    </div>
                <?php else: ?>
                    <div class="craft-grid">
                        <?php foreach ($crafts as $craft): ?>
                        <div class="craft-card">
                            <div class="craft-image">
                                <img src="<?php echo $craft['primary_image'] ? htmlspecialchars($craft['primary_image']) : 'https://via.placeholder.com/280x220/d4a574/ffffff?text=Craft+Image'; ?>" 
                                     alt="<?php echo htmlspecialchars($craft['title']); ?>">
                                
                                <div class="craft-badges">
                                    <div>
                                        <?php if ($craft['gi_tagged']): ?>
                                            <span class="gi-badge">GI Tagged</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if ($craft['status'] === 'featured'): ?>
                                            <span class="featured-badge">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="craft-actions">
                                    <button class="action-btn" title="Quick View" onclick="quickView(<?php echo $craft['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn" title="Add to Wishlist" onclick="addToWishlist(<?php echo $craft['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <?php if ($craft['ar_model_url']): ?>
                                    <button class="action-btn" title="AR View" onclick="openARView(<?php echo $craft['id']; ?>)">
                                        <i class="fas fa-cube"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="craft-content">
                                <div class="craft-category"><?php echo htmlspecialchars($craft['category_name']); ?></div>
                                <h5 class="craft-title">
                                    <a href="craft-detail.php?id=<?php echo $craft['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($craft['title']); ?>
                                    </a>
                                </h5>
                                <div class="craft-artisan d-flex align-items-center">
                                    <div class="me-2" style="transform: scale(0.7);">
                                        <?php echo generateArtisanAvatar($craft['artisan_id'], $craft['artisan_name'], 24, '0.8rem'); ?>
                                    </div>
                                    by <?php echo htmlspecialchars($craft['artisan_name']); ?>
                                </div>
                                <div class="craft-price"><?php echo formatPrice($craft['price']); ?></div>
                                
                                <div class="craft-footer">
                                    <div class="craft-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <small class="text-muted ms-1">(4.5)</small>
                                    </div>
                                    
                                    <?php if (isLoggedIn()): ?>
                                    <button class="btn-add-cart" onclick="addToCart(<?php echo $craft['id']; ?>)" 
                                            data-craft-id="<?php echo $craft['id']; ?>">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        Add to Cart
                                    </button>
                                    <?php else: ?>
                                    <a href="auth/login.php" class="btn-add-cart text-decoration-none">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        Login to Buy
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper">
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
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
        function applyFilters() {
            const category = document.querySelector('input[name="category"]:checked').value;
            const currentUrl = new URL(window.location);
            
            if (category) {
                currentUrl.searchParams.set('category', category);
            } else {
                currentUrl.searchParams.delete('category');
            }
            
            currentUrl.searchParams.delete('page'); // Reset to first page
            window.location.href = currentUrl.toString();
        }
        
        function applySorting(value) {
            const [sortBy, sortOrder] = value.split('-');
            const currentUrl = new URL(window.location);
            
            currentUrl.searchParams.set('sort', sortBy);
            currentUrl.searchParams.set('order', sortOrder);
            currentUrl.searchParams.delete('page'); // Reset to first page
            
            window.location.href = currentUrl.toString();
        }
        
        function clearFilters() {
            window.location.href = 'crafts.php';
        }
        
        function quickView(craftId) {
            // Open quick view modal
            window.location.href = `craft-detail.php?id=${craftId}`;
        }
        
        function addToWishlist(craftId) {
            <?php if (isLoggedIn()): ?>
                // Add to wishlist functionality
                showNotification('Added to wishlist!', 'success');
            <?php else: ?>
                window.location.href = 'auth/login.php';
            <?php endif; ?>
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
