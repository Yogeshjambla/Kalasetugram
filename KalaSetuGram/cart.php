<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$taxRate = 0.18; // 18% GST
$taxAmount = $subtotal * $taxRate;
$total = $subtotal + $taxAmount;

// Handle cart updates
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_quantity') {
        $cartId = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        
        if (updateCartQuantity($_SESSION['user_id'], $cartId, $quantity)) {
            header('Location: cart.php?updated=1');
            exit;
        }
    } elseif ($action === 'remove_item') {
        $cartId = intval($_POST['cart_id']);
        
        if (removeFromCart($_SESSION['user_id'], $cartId)) {
            header('Location: cart.php?removed=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .cart-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0 40px;
            margin-top: -76px;
            padding-top: 136px;
        }
        
        .cart-container {
            padding: 40px 0;
        }
        
        .cart-item {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            box-shadow: var(--shadow-medium);
        }
        
        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            object-fit: cover;
        }
        
        .item-details h5 {
            color: var(--dark-color);
            margin-bottom: 8px;
        }
        
        .item-category {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .item-artisan {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
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
            padding: 6px;
        }
        
        .item-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .item-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .remove-btn {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            color: #c82333;
            transform: scale(1.1);
        }
        
        .cart-summary {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 30px;
            position: sticky;
            top: 100px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark-color);
        }
        
        .coupon-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .coupon-input {
            display: flex;
            gap: 10px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 30px;
        }
        
        .continue-shopping {
            background: var(--accent-color);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .cart-item {
                padding: 20px;
            }
            
            .item-image {
                width: 80px;
                height: 80px;
            }
            
            .cart-summary {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Cart Header -->
    <section class="cart-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-3">Shopping Cart</h1>
                    <p class="lead mb-0">Review your selected crafts before checkout</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container cart-container">
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                Cart updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['removed'])): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <i class="fas fa-info-circle me-2"></i>
                Item removed from cart.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p class="mb-4">Looks like you haven't added any crafts to your cart yet.</p>
                <a href="crafts.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Cart Items (<?php echo count($cartItems); ?>)</h4>
                        <a href="crafts.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>
                            Add More Items
                        </a>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['primary_image'] ? htmlspecialchars($item['primary_image']) : 'https://via.placeholder.com/120x120/d4a574/ffffff?text=Craft'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="item-image">
                            </div>
                            
                            <div class="col-md-4">
                                <div class="item-details">
                                    <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                                    <div class="item-artisan">
                                        <i class="fas fa-user me-1"></i>
                                        by <?php echo htmlspecialchars($item['artisan_name']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="item-price"><?php echo formatPrice($item['price']); ?></div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="quantity-controls">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </form>
                                    
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="10" 
                                           onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                    
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-1">
                                <div class="d-flex flex-column align-items-end">
                                    <div class="item-total mb-2"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="remove-btn" title="Remove item" 
                                                onclick="return confirm('Are you sure you want to remove this item?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Continue Shopping -->
                    <div class="continue-shopping">
                        <h5 class="mb-3">
                            <i class="fas fa-heart text-primary me-2"></i>
                            Support More Artisans
                        </h5>
                        <p class="mb-3">Discover more authentic crafts and help preserve traditional art forms.</p>
                        <a href="crafts.php" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            Browse More Crafts
                        </a>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5 class="mb-4">Order Summary</h5>
                        
                        <!-- Coupon Section -->
                        <div class="coupon-section">
                            <h6 class="mb-3">
                                <i class="fas fa-tag me-2"></i>
                                Have a Coupon?
                            </h6>
                            <div class="coupon-input">
                                <input type="text" class="form-control" placeholder="Enter coupon code" id="couponCode">
                                <button class="btn btn-outline-primary" onclick="applyCoupon()">Apply</button>
                            </div>
                        </div>
                        
                        <!-- Summary Details -->
                        <div class="summary-row">
                            <span>Subtotal (<?php echo count($cartItems); ?> items)</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>GST (18%)</span>
                            <span><?php echo formatPrice($taxAmount); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span class="text-success">FREE</span>
                        </div>
                        
                        <div class="summary-row" id="discount-row" style="display: none;">
                            <span>Discount</span>
                            <span class="text-success" id="discount-amount">-₹0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Total</span>
                            <span id="total-amount"><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <!-- Checkout Button -->
                        <div class="d-grid mt-4">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Proceed to Checkout
                            </a>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Secure checkout with SSL encryption
                            </small>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="text-center mt-3">
                            <small class="text-muted d-block mb-2">We accept:</small>
                            <div class="payment-icons">
                                <i class="fab fa-cc-visa fa-2x me-2 text-primary"></i>
                                <i class="fab fa-cc-mastercard fa-2x me-2 text-warning"></i>
                                <i class="fas fa-mobile-alt fa-2x me-2 text-success"></i>
                                <i class="fab fa-paypal fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        function updateQuantity(cartId, quantity) {
            if (quantity < 1) {
                if (confirm('Remove this item from cart?')) {
                    removeItem(cartId);
                }
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="cart_id" value="${cartId}">
                <input type="hidden" name="quantity" value="${quantity}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function removeItem(cartId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="remove_item">
                <input type="hidden" name="cart_id" value="${cartId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function applyCoupon() {
            const couponCode = document.getElementById('couponCode').value.trim();
            
            if (!couponCode) {
                showNotification('Please enter a coupon code', 'warning');
                return;
            }
            
            // Simulate coupon validation
            fetch('api/validate-coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    coupon_code: couponCode,
                    cart_total: <?php echo $subtotal; ?>
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('Coupon applied successfully!', 'success');
                    
                    // Update UI with discount
                    const discountRow = document.getElementById('discount-row');
                    const discountAmount = document.getElementById('discount-amount');
                    const totalAmount = document.getElementById('total-amount');
                    
                    discountRow.style.display = 'flex';
                    discountAmount.textContent = '-₹' + result.discount_amount;
                    
                    const newTotal = <?php echo $total; ?> - result.discount_amount;
                    totalAmount.textContent = '₹' + newTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    
                } else {
                    showNotification(result.message || 'Invalid coupon code', 'error');
                }
            })
            .catch(error => {
                console.error('Error applying coupon:', error);
                showNotification('Failed to apply coupon. Please try again.', 'error');
            });
        }
        
        // Auto-save cart changes
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for quantity inputs
            document.querySelectorAll('.quantity-input').forEach(input => {
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const cartId = this.getAttribute('data-cart-id');
                        const quantity = parseInt(this.value);
                        if (quantity > 0) {
                            updateQuantity(cartId, quantity);
                        }
                    }, 1000); // Wait 1 second after user stops typing
                });
            });
        });
    </script>
</body>
</html>
