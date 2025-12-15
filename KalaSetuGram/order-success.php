<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get order number from URL
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    header('Location: index.php');
    exit;
}

// Get order details
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, c.title, c.price as craft_price, cc.name as category_name,
           (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
    FROM order_items oi 
    JOIN crafts c ON oi.craft_id = c.id 
    JOIN craft_categories cc ON c.category_id = cc.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$orderItems = $stmt->fetchAll();

// Get shipping address
$shippingAddress = json_decode($order['shipping_address'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 80px 0;
            margin-top: -76px;
            padding-top: 156px;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 4rem;
        }
        
        .order-container {
            padding: 60px 0;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .order-header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 20px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .item-category {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--dark-color);
            text-align: right;
        }
        
        .summary-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark-color);
        }
        
        .shipping-info {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .next-steps {
            background: var(--accent-color);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-top: 40px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn-action {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .order-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Success Header -->
    <section class="success-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Order Confirmed!</h1>
                    <p class="lead mb-0">Thank you for supporting traditional artisans</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container order-container">
        <div class="row">
            <!-- Order Details -->
            <div class="col-lg-8">
                <div class="order-card">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="order-number">Order #<?php echo htmlspecialchars($orderNumber); ?></div>
                                <small class="text-muted">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div>
                                <span class="order-status <?php echo $order['order_status'] === 'confirmed' ? 'status-confirmed' : 'status-pending'; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="mb-4">Order Items</h4>
                    
                    <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo $item['primary_image'] ? htmlspecialchars($item['primary_image']) : 'https://via.placeholder.com/80x80/d4a574/ffffff?text=C'; ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             class="item-image">
                        
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['title']); ?></div>
                            <div class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                        
                        <div class="item-price">
                            <div><?php echo formatPrice($item['price']); ?> each</div>
                            <div class="fw-bold"><?php echo formatPrice($item['total']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Order Summary -->
                    <div class="summary-section">
                        <h5 class="mb-3">Order Summary</h5>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($order['total_amount'] - $order['tax_amount'] + $order['discount_amount']); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>GST (18%)</span>
                            <span><?php echo formatPrice($order['tax_amount']); ?></span>
                        </div>
                        
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="summary-row">
                            <span>Discount <?php echo $order['coupon_code'] ? '(' . htmlspecialchars($order['coupon_code']) . ')' : ''; ?></span>
                            <span class="text-success">-<?php echo formatPrice($order['discount_amount']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span class="text-success">FREE</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Total Paid</span>
                            <span><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping & Payment Info -->
            <div class="col-lg-4">
                <!-- Shipping Information -->
                <div class="shipping-info">
                    <h4 class="section-title">
                        <i class="fas fa-shipping-fast"></i>
                        Shipping Information
                    </h4>
                    
                    <div class="mb-3">
                        <strong><?php echo htmlspecialchars($shippingAddress['name']); ?></strong><br>
                        <?php echo htmlspecialchars($shippingAddress['phone']); ?>
                    </div>
                    
                    <div class="text-muted">
                        <?php echo htmlspecialchars($shippingAddress['address']); ?><br>
                        <?php echo htmlspecialchars($shippingAddress['city']); ?>, <?php echo htmlspecialchars($shippingAddress['state']); ?><br>
                        <?php echo htmlspecialchars($shippingAddress['pincode']); ?>
                    </div>
                    
                    <hr>
                    
                    <h5 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Payment Method
                    </h5>
                    
                    <div class="d-flex align-items-center">
                        <?php if ($order['payment_method'] === 'razorpay'): ?>
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            <span>Credit/Debit Card</span>
                        <?php elseif ($order['payment_method'] === 'paypal'): ?>
                            <i class="fab fa-paypal text-info me-2"></i>
                            <span>PayPal</span>
                        <?php else: ?>
                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                            <span>Cash on Delivery</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            Payment Status: 
                            <span class="<?php echo $order['payment_status'] === 'completed' ? 'text-success' : 'text-warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </small>
                    </div>
                    
                    <?php if ($order['transaction_id']): ?>
                    <div class="mt-1">
                        <small class="text-muted">
                            Transaction ID: <?php echo htmlspecialchars($order['transaction_id']); ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Next Steps -->
                <div class="next-steps">
                    <h5 class="mb-3">
                        <i class="fas fa-clock text-primary me-2"></i>
                        What's Next?
                    </h5>
                    
                    <div class="text-start">
                        <div class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Order confirmation email sent
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-box text-warning me-2"></i>
                            Artisan will prepare your crafts
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-truck text-info me-2"></i>
                            We'll ship within 2-3 business days
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-home text-primary me-2"></i>
                            Delivery in 5-7 business days
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="orders.php" class="btn btn-primary btn-action">
                <i class="fas fa-list me-2"></i>
                View All Orders
            </a>
            
            <a href="crafts.php" class="btn btn-outline-primary btn-action">
                <i class="fas fa-shopping-bag me-2"></i>
                Continue Shopping
            </a>
            
            <button class="btn btn-outline-secondary btn-action" onclick="window.print()">
                <i class="fas fa-print me-2"></i>
                Print Receipt
            </button>
            
            <button class="btn btn-outline-success btn-action" onclick="shareOrder()">
                <i class="fas fa-share-alt me-2"></i>
                Share Order
            </button>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        function shareOrder() {
            if (navigator.share) {
                navigator.share({
                    title: 'My KalaSetuGram Order',
                    text: 'I just ordered beautiful traditional crafts from KalaSetuGram!',
                    url: window.location.href
                });
            } else {
                // Fallback to copying URL
                navigator.clipboard.writeText(window.location.href);
                showNotification('Order link copied to clipboard!', 'success');
            }
        }
        
        // Show success message
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Order placed successfully! You will receive a confirmation email shortly.', 'success');
        });
    </script>
</body>
</html>
