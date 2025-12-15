<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Policy - KalaSetuGram</title>
    
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
    
    <!-- Shipping Policy Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Shipping Policy</h1>
                    <p class="lead text-muted">Learn about our shipping methods, delivery times, and charges</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Shipping Policy Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        
                        <!-- Shipping Methods -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-shipping-fast me-2"></i>Shipping Methods</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-4 h-100">
                                        <h5 class="text-dark"><i class="fas fa-truck text-primary me-2"></i>Standard Delivery</h5>
                                        <p class="text-muted mb-2">5-7 business days</p>
                                        <p class="small">Free shipping on orders above ₹999</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-4 h-100">
                                        <h5 class="text-dark"><i class="fas fa-bolt text-warning me-2"></i>Express Delivery</h5>
                                        <p class="text-muted mb-2">2-3 business days</p>
                                        <p class="small">Additional charges apply</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Timeline -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-clock me-2"></i>Delivery Timeline</h3>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Location</th>
                                            <th>Standard Delivery</th>
                                            <th>Express Delivery</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Within Andhra Pradesh</td>
                                            <td>3-5 business days</td>
                                            <td>1-2 business days</td>
                                        </tr>
                                        <tr>
                                            <td>South India (TN, KA, KL, TG)</td>
                                            <td>4-6 business days</td>
                                            <td>2-3 business days</td>
                                        </tr>
                                        <tr>
                                            <td>North & West India</td>
                                            <td>5-7 business days</td>
                                            <td>3-4 business days</td>
                                        </tr>
                                        <tr>
                                            <td>Northeast & Remote Areas</td>
                                            <td>7-10 business days</td>
                                            <td>5-7 business days</td>
                                        </tr>
                                        <tr>
                                            <td>International</td>
                                            <td>10-21 business days</td>
                                            <td>7-14 business days</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Shipping Charges -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-rupee-sign me-2"></i>Shipping Charges</h3>
                            <div class="alert alert-success">
                                <i class="fas fa-gift me-2"></i><strong>Free Shipping:</strong> On all orders above ₹999 within India
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Order Value</th>
                                            <th>Within State</th>
                                            <th>Other States</th>
                                            <th>International</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Below ₹500</td>
                                            <td>₹50</td>
                                            <td>₹80</td>
                                            <td>₹500</td>
                                        </tr>
                                        <tr>
                                            <td>₹500 - ₹999</td>
                                            <td>₹30</td>
                                            <td>₹60</td>
                                            <td>₹400</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td>Above ₹999</td>
                                            <td>Free</td>
                                            <td>Free</td>
                                            <td>₹300</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Special Items -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-star me-2"></i>Special Items</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="border-start border-4 border-warning ps-3">
                                        <h5 class="text-dark">Custom/Made-to-Order</h5>
                                        <p class="text-muted small">Items crafted specifically for you may take 10-15 additional business days for creation before shipping.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border-start border-4 border-info ps-3">
                                        <h5 class="text-dark">Fragile Items</h5>
                                        <p class="text-muted small">Delicate crafts are packed with extra care and may require additional handling time.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Processing -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-cogs me-2"></i>Order Processing</h3>
                            <div class="timeline">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">1</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Order Confirmation</h6>
                                        <p class="text-muted small">Within 2 hours of placing your order</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">2</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Processing</h6>
                                        <p class="text-muted small">1-2 business days for quality check and packaging</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">3</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Dispatch</h6>
                                        <p class="text-muted small">You'll receive tracking details via SMS and email</p>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">4</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Delivery</h6>
                                        <p class="text-muted small">Safe delivery to your doorstep</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-exclamation-triangle me-2"></i>Important Notes</h3>
                            <div class="alert alert-info">
                                <ul class="mb-0">
                                    <li>Delivery times are estimates and may vary during festivals or adverse weather conditions</li>
                                    <li>We don't deliver on Sundays and national holidays</li>
                                    <li>For international shipping, additional customs duties may apply</li>
                                    <li>Address changes are not possible once the order is dispatched</li>
                                    <li>We'll attempt delivery 3 times before returning the package to our warehouse</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="text-center">
                            <h4 class="text-dark mb-3">Need Help with Your Order?</h4>
                            <p class="text-muted mb-4">Our customer support team is here to assist you</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="tel:+919876543210" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-phone me-2"></i>Call Us
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="mailto:support@kalasetugramdb.com" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-envelope me-2"></i>Email Us
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="contact.php" class="btn btn-primary w-100">
                                        <i class="fas fa-comments me-2"></i>Live Chat
                                    </a>
                                </div>
                            </div>
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
</body>
</html>
