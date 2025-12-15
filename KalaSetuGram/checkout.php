<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$taxRate = 0.18; // 18% GST
$taxAmount = $subtotal * $taxRate;
$shippingCost = 0; // Free shipping
$total = $subtotal + $taxAmount + $shippingCost;

// Get user details
$user = getUserById($_SESSION['user_id']);

// Handle form submission
if ($_POST) {
    $shippingAddress = [
        'name' => sanitizeInput($_POST['shipping_name']),
        'phone' => sanitizeInput($_POST['shipping_phone']),
        'address' => sanitizeInput($_POST['shipping_address']),
        'city' => sanitizeInput($_POST['shipping_city']),
        'state' => sanitizeInput($_POST['shipping_state']),
        'pincode' => sanitizeInput($_POST['shipping_pincode'])
    ];
    
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    $couponCode = sanitizeInput($_POST['coupon_code'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($shippingAddress['name'])) $errors[] = 'Name is required';
    if (empty($shippingAddress['phone'])) $errors[] = 'Phone is required';
    if (empty($shippingAddress['address'])) $errors[] = 'Address is required';
    if (empty($shippingAddress['city'])) $errors[] = 'City is required';
    if (empty($shippingAddress['state'])) $errors[] = 'State is required';
    if (empty($shippingAddress['pincode'])) $errors[] = 'Pincode is required';
    if (empty($paymentMethod)) $errors[] = 'Payment method is required';
    
    if (empty($errors)) {
        // Create order
        $shippingAddressString = json_encode($shippingAddress);
        $result = createOrder($_SESSION['user_id'], $cartItems, $shippingAddressString, $paymentMethod, $couponCode);
        
        if ($result['success']) {
            // Redirect to payment or success page
            if ($paymentMethod === 'cod') {
                header("Location: order-success.php?order=" . $result['order_number']);
                exit;
            } else {
                // Redirect to payment gateway
                header("Location: payment.php?order=" . $result['order_number']);
                exit;
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = implode(', ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .checkout-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0 40px;
            margin-top: -76px;
            padding-top: 136px;
        }
        
        .checkout-container {
            padding: 40px 0;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        .step {
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: relative;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .step.active .step-number {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -20px;
            width: 40px;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
        }
        
        .checkout-section {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 30px;
            margin-bottom: 30px;
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
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .payment-method {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .payment-method input[type="radio"] {
            margin-right: 15px;
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .order-summary {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 30px;
            position: sticky;
            top: 100px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image-small {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .item-details {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary-color);
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
        
        .security-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .checkout-steps {
                flex-direction: column;
                align-items: center;
            }
            
            .step:not(:last-child)::after {
                display: none;
            }
            
            .order-summary {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Checkout Header -->
    <section class="checkout-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-3">Secure Checkout</h1>
                    <p class="lead mb-0">Complete your order and support traditional artisans</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container checkout-container">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-number">1</div>
                <span>Cart</span>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <span>Checkout</span>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Payment</span>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <span>Confirmation</span>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="checkoutForm">
            <div class="row">
                <!-- Checkout Form -->
                <div class="col-lg-8">
                    <!-- Shipping Information -->
                    <div class="checkout-section">
                        <h3 class="section-title">
                            <i class="fas fa-shipping-fast"></i>
                            Shipping Information
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="shipping_name" name="shipping_name" 
                                           placeholder="Full Name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    <label for="shipping_name">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" 
                                           placeholder="Phone Number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                    <label for="shipping_phone">Phone Number</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating">
                            <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                      placeholder="Address" style="height: 100px;" required></textarea>
                            <label for="shipping_address">Complete Address</label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                           placeholder="City" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" required>
                                    <label for="shipping_city">City</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="shipping_state" name="shipping_state" required>
                                        <option value="">Select State</option>
                                        <option value="Andhra Pradesh" selected>Andhra Pradesh</option>
                                        <option value="Telangana">Telangana</option>
                                        <option value="Karnataka">Karnataka</option>
                                        <option value="Tamil Nadu">Tamil Nadu</option>
                                        <option value="Kerala">Kerala</option>
                                    </select>
                                    <label for="shipping_state">State</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="shipping_pincode" name="shipping_pincode" 
                                           placeholder="Pincode" pattern="[0-9]{6}" required>
                                    <label for="shipping_pincode">Pincode</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i>
                            Payment Method
                        </h3>
                        
                        <div class="payment-method" onclick="selectPayment('razorpay')">
                            <input type="radio" name="payment_method" value="razorpay" id="razorpay">
                            <label for="razorpay" class="d-flex align-items-center">
                                <i class="fas fa-credit-card payment-icon text-primary"></i>
                                <div>
                                    <strong>Credit/Debit Card & UPI</strong>
                                    <br><small class="text-muted">Secure payment via Razorpay</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-method" onclick="selectPayment('paypal')">
                            <input type="radio" name="payment_method" value="paypal" id="paypal">
                            <label for="paypal" class="d-flex align-items-center">
                                <i class="fab fa-paypal payment-icon text-info"></i>
                                <div>
                                    <strong>PayPal</strong>
                                    <br><small class="text-muted">Pay with your PayPal account</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-method" onclick="selectPayment('cod')">
                            <input type="radio" name="payment_method" value="cod" id="cod">
                            <label for="cod" class="d-flex align-items-center">
                                <i class="fas fa-money-bill-wave payment-icon text-success"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <br><small class="text-muted">Pay when you receive your order</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Coupon Code -->
                    <div class="checkout-section">
                        <h3 class="section-title">
                            <i class="fas fa-tag"></i>
                            Coupon Code (Optional)
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="coupon_code" name="coupon_code" placeholder="Coupon Code">
                                    <label for="coupon_code">Enter coupon code</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary h-100 w-100" onclick="validateCoupon()">
                                    Apply Coupon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h4 class="mb-4">Order Summary</h4>
                        
                        <!-- Order Items -->
                        <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['primary_image'] ? htmlspecialchars($item['primary_image']) : 'https://via.placeholder.com/60x60/d4a574/ffffff?text=C'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="item-image-small">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="item-details">
                                    Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?>
                                </div>
                            </div>
                            <div class="item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Summary Totals -->
                        <div class="mt-4">
                            <div class="summary-row">
                                <span>Subtotal</span>
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
                        </div>
                        
                        <!-- Place Order Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Place Order
                            </button>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="security-info">
                            <div class="mb-2">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <strong>Secure Checkout</strong>
                            </div>
                            <small class="text-muted">
                                Your payment information is encrypted and secure. 
                                We never store your card details.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        function selectPayment(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(pm => {
                pm.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(method).checked = true;
        }
        
        function validateCoupon() {
            const couponCode = document.getElementById('coupon_code').value.trim();
            
            if (!couponCode) {
                showNotification('Please enter a coupon code', 'warning');
                return;
            }
            
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
                console.error('Error validating coupon:', error);
                showNotification('Failed to validate coupon. Please try again.', 'error');
            });
        }
        
        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                e.preventDefault();
                showNotification('Please select a payment method', 'warning');
                return;
            }
            
            // Validate phone number
            const phone = document.getElementById('shipping_phone').value;
            if (!/^[0-9]{10}$/.test(phone.replace(/\D/g, ''))) {
                e.preventDefault();
                showNotification('Please enter a valid 10-digit phone number', 'warning');
                return;
            }
            
            // Validate pincode
            const pincode = document.getElementById('shipping_pincode').value;
            if (!/^[0-9]{6}$/.test(pincode)) {
                e.preventDefault();
                showNotification('Please enter a valid 6-digit pincode', 'warning');
                return;
            }
        });
        
        // Auto-fill city based on pincode
        document.getElementById('shipping_pincode').addEventListener('blur', function() {
            const pincode = this.value;
            if (pincode.length === 6) {
                // You can integrate with a pincode API here
                // For now, just a placeholder
                console.log('Validating pincode:', pincode);
            }
        });
    </script>
</body>
</html>
