<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/avatar_helper.php';

// Get craft ID from URL
$craftId = intval($_GET['id'] ?? 0);

if (!$craftId) {
    header('Location: crafts.php');
    exit;
}

// Get craft details
$craft = getCraftById($craftId);
if (!$craft) {
    header('Location: crafts.php');
    exit;
}

// Get craft images
$images = getCraftImages($craftId);

// Check if user is logged in
$user = null;
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
}

// Get related crafts
$relatedCrafts = getAllCrafts(4, 0, $craft['category_name'], null, 'created_at', 'DESC');
$relatedCrafts = array_filter($relatedCrafts, function($item) use ($craftId) {
    return $item['id'] != $craftId;
});
$relatedCrafts = array_slice($relatedCrafts, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($craft['title']); ?> - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .product-gallery {
            position: sticky;
            top: 100px;
        }
        
        .main-image {
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }
        
        .main-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .image-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .gi-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .ar-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .thumbnail-gallery {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 5px 0;
        }
        
        .thumbnail {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail.active {
            border-color: var(--primary-color);
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px 0;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .product-category {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .artisan-info {
            background: var(--accent-color);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        
        .artisan-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .product-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .stars {
            color: #ffd700;
        }
        
        .rating-text {
            color: #666;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn-primary-large {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            flex: 1;
            min-width: 200px;
        }
        
        .btn-ar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
        }
        
        .btn-ar:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
            color: white;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px;
        }
        
        .product-details {
            margin-top: 40px;
        }
        
        .detail-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }
        
        .detail-tab {
            padding: 15px 20px;
            background: none;
            border: none;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .detail-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .craft-story {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        
        .specifications table {
            width: 100%;
        }
        
        .specifications th,
        .specifications td {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .specifications th {
            font-weight: 600;
            color: var(--dark-color);
            width: 30%;
        }
        
        .related-crafts {
            margin-top: 60px;
        }
        
        .craft-card-small {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }
        
        .craft-card-small:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .craft-card-small .craft-image {
            height: 200px;
            overflow: hidden;
        }
        
        .craft-card-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .craft-card-small .craft-content {
            padding: 15px;
        }
        
        @media (max-width: 768px) {
            .product-title {
                font-size: 2rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .btn-primary-large {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="crafts.php">Crafts</a></li>
                <li class="breadcrumb-item"><a href="crafts.php?category=<?php echo urlencode($craft['category_name']); ?>"><?php echo htmlspecialchars($craft['category_name']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($craft['title']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainImage" src="<?php echo !empty($images) ? htmlspecialchars($images[0]['image_url']) : 'https://via.placeholder.com/500x400/d4a574/ffffff?text=Craft+Image'; ?>" 
                             alt="<?php echo htmlspecialchars($craft['title']); ?>">
                        
                        <div class="image-badges">
                            <div>
                                <?php if ($craft['gi_tagged']): ?>
                                    <span class="gi-badge">
                                        <i class="fas fa-certificate me-1"></i>
                                        GI Tagged
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($craft['ar_model_url']): ?>
                                    <span class="ar-badge">
                                        <i class="fas fa-cube me-1"></i>
                                        AR Enabled
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnail-gallery">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="changeMainImage('<?php echo htmlspecialchars($image['image_url']); ?>', this)">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['alt_text'] ?: $craft['title']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <div class="product-category">
                        <i class="fas fa-tag me-2"></i>
                        <?php echo htmlspecialchars($craft['category_name']); ?>
                    </div>
                    
                    <h1 class="product-title"><?php echo htmlspecialchars($craft['title']); ?></h1>
                    
                    <!-- Artisan Info -->
                    <div class="artisan-info">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php echo generateArtisanAvatar($craft['artisan_id'], $craft['artisan_name'], 60, '1.8rem'); ?>
                            </div>
                            <div>
                                <h6 class="mb-1">Crafted by <?php echo htmlspecialchars($craft['artisan_name']); ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($craft['district']); ?>, Andhra Pradesh
                                </small>
                                <?php if ($craft['experience_years']): ?>
                                <br><small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $craft['experience_years']; ?> years of experience
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rating -->
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-text">4.5 (24 reviews)</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="product-price"><?php echo formatPrice($craft['price']); ?></div>
                    
                    <!-- Quantity Selector -->
                    <div class="quantity-selector">
                        <label class="fw-bold me-3">Quantity:</label>
                        <button class="quantity-btn" onclick="changeQuantity(-1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $craft['stock_quantity']; ?>">
                        <button class="quantity-btn" onclick="changeQuantity(1)">
                            <i class="fas fa-plus"></i>
                        </button>
                        <small class="text-muted ms-3"><?php echo $craft['stock_quantity']; ?> available</small>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="product-actions">
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary btn-primary-large" onclick="addToCart(<?php echo $craft['id']; ?>)" 
                                data-craft-id="<?php echo $craft['id']; ?>">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Add to Cart
                        </button>
                        
                        <button class="btn btn-outline-primary btn-primary-large" onclick="buyNow(<?php echo $craft['id']; ?>)">
                            <i class="fas fa-bolt me-2"></i>
                            Buy Now
                        </button>
                        <?php else: ?>
                        <a href="auth/login.php" class="btn btn-primary btn-primary-large">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login to Purchase
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($craft['ar_model_url']): ?>
                        <button class="btn btn-ar btn-primary-large" onclick="openARView(<?php echo $craft['id']; ?>)">
                            <i class="fas fa-cube me-2"></i>
                            View in AR
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Additional Actions -->
                    <div class="d-flex gap-3 mb-4">
                        <button class="btn btn-outline-secondary" onclick="addToWishlist(<?php echo $craft['id']; ?>)">
                            <i class="fas fa-heart me-2"></i>
                            Add to Wishlist
                        </button>
                        <button class="btn btn-outline-secondary" onclick="shareProduct()">
                            <i class="fas fa-share-alt me-2"></i>
                            Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Details Tabs -->
        <div class="product-details">
            <div class="detail-tabs">
                <button class="detail-tab active" onclick="showTab('description')">Description</button>
                <button class="detail-tab" onclick="showTab('story')">Cultural Story</button>
                <button class="detail-tab" onclick="showTab('specifications')">Specifications</button>
                <button class="detail-tab" onclick="showTab('reviews')">Reviews (24)</button>
            </div>
            
            <!-- Description Tab -->
            <div id="description" class="tab-content active">
                <div class="row">
                    <div class="col-lg-8">
                        <h4 class="mb-3">About this Craft</h4>
                        <p class="lead"><?php echo nl2br(htmlspecialchars($craft['description'])); ?></p>
                        
                        <?php if ($craft['artisan_bio']): ?>
                        <h5 class="mt-4 mb-3">About the Artisan</h5>
                        <p><?php echo nl2br(htmlspecialchars($craft['artisan_bio'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Cultural Story Tab -->
            <div id="story" class="tab-content">
                <?php if ($craft['story']): ?>
                <div class="craft-story">
                    <h4 class="mb-3">
                        <i class="fas fa-book-open me-2 text-primary"></i>
                        Cultural Heritage Story
                    </h4>
                    <p><?php echo nl2br(htmlspecialchars($craft['story'])); ?></p>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h5>Cultural story coming soon</h5>
                    <p class="text-muted">We're working with the artisan to document the rich heritage behind this craft.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Specifications Tab -->
            <div id="specifications" class="tab-content">
                <div class="specifications">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Material</th>
                                <td><?php echo htmlspecialchars($craft['material'] ?: 'Traditional materials'); ?></td>
                            </tr>
                            <tr>
                                <th>Dimensions</th>
                                <td><?php echo htmlspecialchars($craft['dimensions'] ?: 'Varies by piece'); ?></td>
                            </tr>
                            <tr>
                                <th>Weight</th>
                                <td><?php echo htmlspecialchars($craft['weight'] ?: 'Lightweight'); ?></td>
                            </tr>
                            <tr>
                                <th>Craft Type</th>
                                <td><?php echo htmlspecialchars($craft['craft_type']); ?></td>
                            </tr>
                            <tr>
                                <th>Origin</th>
                                <td><?php echo htmlspecialchars($craft['district']); ?>, Andhra Pradesh</td>
                            </tr>
                            <tr>
                                <th>GI Tag Status</th>
                                <td>
                                    <?php if ($craft['gi_tagged']): ?>
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>GI Tagged</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not GI Tagged</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content">
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-warning mb-3"></i>
                    <h5>Reviews system coming soon</h5>
                    <p class="text-muted">Customer reviews and ratings will be available shortly.</p>
                </div>
            </div>
        </div>
        
        <!-- Related Crafts -->
        <?php if (!empty($relatedCrafts)): ?>
        <div class="related-crafts">
            <h3 class="mb-4">Related Crafts from <?php echo htmlspecialchars($craft['category_name']); ?></h3>
            <div class="row g-4">
                <?php foreach ($relatedCrafts as $relatedCraft): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="craft-card-small">
                        <div class="craft-image">
                            <a href="craft-detail.php?id=<?php echo $relatedCraft['id']; ?>">
                                <img src="<?php echo $relatedCraft['primary_image'] ? htmlspecialchars($relatedCraft['primary_image']) : 'https://via.placeholder.com/280x200/d4a574/ffffff?text=Craft'; ?>" 
                                     alt="<?php echo htmlspecialchars($relatedCraft['title']); ?>">
                            </a>
                        </div>
                        <div class="craft-content">
                            <h6 class="mb-2">
                                <a href="craft-detail.php?id=<?php echo $relatedCraft['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($relatedCraft['title']); ?>
                                </a>
                            </h6>
                            <div class="text-muted small mb-2">by <?php echo htmlspecialchars($relatedCraft['artisan_name']); ?></div>
                            <div class="fw-bold text-primary"><?php echo formatPrice($relatedCraft['price']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- AR Modal -->
    <div class="modal fade" id="arModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">AR View - <?php echo htmlspecialchars($craft['title']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="ar-scene" style="height: 100vh;">
                        <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;">
                            <a-marker preset="hiro">
                                <a-entity id="ar-model" scale="0.5 0.5 0.5"></a-entity>
                            </a-marker>
                            <a-entity camera></a-entity>
                        </a-scene>
                    </div>
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
        function changeMainImage(imageSrc, thumbnail) {
            document.getElementById('mainImage').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }
        
        function changeQuantity(delta) {
            const quantityInput = document.getElementById('quantity');
            const currentValue = parseInt(quantityInput.value);
            const maxValue = parseInt(quantityInput.max);
            const newValue = Math.max(1, Math.min(maxValue, currentValue + delta));
            quantityInput.value = newValue;
        }
        
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.detail-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function buyNow(craftId) {
            const quantity = document.getElementById('quantity').value;
            
            // Add to cart and redirect to checkout
            addToCart(craftId, quantity).then(() => {
                window.location.href = 'checkout.php';
            });
        }
        
        function addToWishlist(craftId) {
            <?php if (isLoggedIn()): ?>
                showNotification('Added to wishlist!', 'success');
            <?php else: ?>
                window.location.href = 'auth/login.php';
            <?php endif; ?>
        }
        
        function shareProduct() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars($craft['title']); ?>',
                    text: 'Check out this beautiful craft from KalaSetuGram',
                    url: window.location.href
                });
            } else {
                // Fallback to copying URL
                navigator.clipboard.writeText(window.location.href);
                showNotification('Product link copied to clipboard!', 'success');
            }
        }
        
        // Override addToCart to include quantity
        function addToCart(craftId, customQuantity = null) {
            const quantity = customQuantity || document.getElementById('quantity').value;
            
            return fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    craft_id: craftId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification(`${quantity} item(s) added to cart!`, 'success');
                    updateCartCount();
                } else {
                    showNotification(result.message || 'Failed to add item to cart', 'error');
                }
                return result;
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showNotification('An error occurred. Please try again.', 'error');
                throw error;
            });
        }
    </script>
</body>
</html>
