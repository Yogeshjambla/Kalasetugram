<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get order number from URL
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    header('Location: cart.php');
    exit;
}

// Get order details
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, c.title, c.price as craft_price
    FROM order_items oi 
    JOIN crafts c ON oi.craft_id = c.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$orderItems = $stmt->fetchAll();

// Payment processing
if ($_POST) {
    $paymentMethod = $order['payment_method'];
    
    if ($paymentMethod === 'razorpay') {
        // Handle Razorpay payment verification
        $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
        $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
        $razorpaySignature = $_POST['razorpay_signature'] ?? '';
        
        // Verify payment signature (in production, use proper Razorpay verification)
        if ($razorpayPaymentId && $razorpayOrderId && $razorpaySignature) {
            // Update order status
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed', order_status = 'confirmed', transaction_id = ? WHERE id = ?");
            $stmt->execute([$razorpayPaymentId, $order['id']]);
            
            header("Location: order-success.php?order=" . $orderNumber);
            exit;
        } else {
            $error = 'Payment verification failed';
        }
    } elseif ($paymentMethod === 'paypal') {
        // Handle PayPal payment
        $paypalPaymentId = $_POST['paypal_payment_id'] ?? '';
        
        if ($paypalPaymentId) {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed', order_status = 'confirmed', transaction_id = ? WHERE id = ?");
            $stmt->execute([$paypalPaymentId, $order['id']]);
            
            header("Location: order-success.php?order=" . $orderNumber);
            exit;
        } else {
            $error = 'PayPal payment failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Razorpay Checkout -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=INR"></script>
    
    <style>
        .payment-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0 40px;
            margin-top: -76px;
            padding-top: 136px;
        }
        
        .payment-container {
            padding: 40px 0;
        }
        
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .payment-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 3rem;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: left;
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
        
        .payment-methods {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .payment-btn {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .payment-btn:hover {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .payment-btn i {
            font-size: 2rem;
        }
        
        .razorpay-btn {
            color: #3395ff;
        }
        
        .paypal-btn {
            color: #0070ba;
        }
        
        .security-info {
            background: #e8f5e8;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Payment Header -->
    <section class="payment-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-3">Secure Payment</h1>
                    <p class="lead mb-0">Complete your payment to confirm your order</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container payment-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="payment-card">
            <div class="payment-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            
            <h2 class="mb-4">Complete Your Payment</h2>
            <p class="text-muted mb-4">Order #<?php echo htmlspecialchars($orderNumber); ?></p>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h5 class="mb-3">Order Summary</h5>
                
                <?php foreach ($orderItems as $item): ?>
                <div class="summary-row">
                    <span><?php echo htmlspecialchars($item['title']); ?> (×<?php echo $item['quantity']; ?>)</span>
                    <span><?php echo formatPrice($item['total']); ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-row">
                    <span>GST (18%)</span>
                    <span><?php echo formatPrice($order['tax_amount']); ?></span>
                </div>
                
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="summary-row">
                    <span>Discount</span>
                    <span class="text-success">-<?php echo formatPrice($order['discount_amount']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <span>Total Amount</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="payment-methods">
                <?php if ($order['payment_method'] === 'razorpay'): ?>
                    <button class="payment-btn razorpay-btn" onclick="initiateRazorpayPayment()">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <div>Pay with Razorpay</div>
                            <small class="text-muted">Credit/Debit Card, UPI, Net Banking</small>
                        </div>
                    </button>
                    
                <?php elseif ($order['payment_method'] === 'paypal'): ?>
                    <div id="paypal-button-container"></div>
                    
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Cash on Delivery order confirmed. No payment required now.
                    </div>
                    <a href="order-success.php?order=<?php echo urlencode($orderNumber); ?>" class="btn btn-primary btn-lg">
                        View Order Details
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Security Info -->
            <div class="security-info">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    <strong>Your payment is secure</strong>
                </div>
                <small class="text-muted">
                    We use industry-standard encryption to protect your payment information. 
                    Your card details are never stored on our servers.
                </small>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h5>Processing Payment...</h5>
            <p class="text-muted mb-0">Please do not close this window</p>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Razorpay Payment Integration
        function initiateRazorpayPayment() {
            const options = {
                "key": "rzp_test_YOUR_KEY_ID", // Replace with your Razorpay Key ID
                "amount": <?php echo $order['total_amount'] * 100; ?>, // Amount in paise
                "currency": "INR",
                "name": "KalaSetuGram",
                "description": "Order #<?php echo $orderNumber; ?>",
                "order_id": "<?php echo $orderNumber; ?>", // This should be Razorpay order ID in production
                "handler": function (response) {
                    // Payment successful
                    document.getElementById('loadingOverlay').style.display = 'flex';
                    
                    // Submit payment details to server
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="razorpay_payment_id" value="${response.razorpay_payment_id}">
                        <input type="hidden" name="razorpay_order_id" value="${response.razorpay_order_id}">
                        <input type="hidden" name="razorpay_signature" value="${response.razorpay_signature}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($_SESSION['user_name']); ?>",
                    "email": "<?php echo htmlspecialchars($_SESSION['user_email']); ?>"
                },
                "theme": {
                    "color": "#d4a574"
                },
                "modal": {
                    "ondismiss": function() {
                        showNotification('Payment cancelled', 'warning');
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        }
        
        <?php if ($order['payment_method'] === 'paypal'): ?>
        // PayPal Payment Integration
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($order['total_amount'], 2, '.', ''); ?>'
                        },
                        description: 'KalaSetuGram Order #<?php echo $orderNumber; ?>'
                    }]
                });
            },
            onApprove: function(data, actions) {
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                return actions.order.capture().then(function(details) {
                    // Submit payment details to server
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="paypal_payment_id" value="${details.id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
            },
            onError: function(err) {
                console.error('PayPal Error:', err);
                showNotification('Payment failed. Please try again.', 'error');
            },
            onCancel: function(data) {
                showNotification('Payment cancelled', 'warning');
            }
        }).render('#paypal-button-container');
        <?php endif; ?>
        
        // Prevent accidental page refresh during payment
        window.addEventListener('beforeunload', function(e) {
            if (document.getElementById('loadingOverlay').style.display === 'flex') {
                e.preventDefault();
                e.returnValue = 'Payment is in progress. Are you sure you want to leave?';
            }
        });
    </script>
</body>
</html>
