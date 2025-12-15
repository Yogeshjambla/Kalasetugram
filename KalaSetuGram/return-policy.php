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
    <title>Return Policy - KalaSetuGram</title>
    
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
    
    <!-- Return Policy Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Return & Exchange Policy</h1>
                    <p class="lead text-muted">Your satisfaction is our priority. Learn about our return and exchange process.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Return Policy Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        
                        <!-- Return Timeline -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-calendar-alt me-2"></i>Return Timeline</h3>
                            <div class="alert alert-success">
                                <i class="fas fa-clock me-2"></i><strong>7-Day Return Policy:</strong> You can return most items within 7 days of delivery for a full refund or exchange.
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-4 h-100">
                                        <h5 class="text-success"><i class="fas fa-check-circle me-2"></i>Eligible for Return</h5>
                                        <ul class="small text-muted">
                                            <li>Damaged or defective items</li>
                                            <li>Wrong item delivered</li>
                                            <li>Significantly different from description</li>
                                            <li>Quality issues</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-4 h-100">
                                        <h5 class="text-danger"><i class="fas fa-times-circle me-2"></i>Not Eligible for Return</h5>
                                        <ul class="small text-muted">
                                            <li>Custom/personalized items</li>
                                            <li>Items damaged by misuse</li>
                                            <li>Items without original packaging</li>
                                            <li>Hygiene-sensitive products</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Return Process -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-undo me-2"></i>How to Return an Item</h3>
                            <div class="timeline">
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="fw-bold">1</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Initiate Return Request</h6>
                                        <p class="text-muted small">Log into your account, go to 'My Orders', and click 'Return Item' next to the product you want to return.</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="fw-bold">2</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Select Return Reason</h6>
                                        <p class="text-muted small">Choose the reason for return and provide additional details if required. Upload photos for damaged items.</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="fw-bold">3</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Schedule Pickup</h6>
                                        <p class="text-muted small">We'll arrange a free pickup from your address. Pack the item in its original packaging with all accessories.</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="fw-bold">4</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-dark">Quality Check & Refund</h6>
                                        <p class="text-muted small">Once we receive and verify the item, your refund will be processed within 3-5 business days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Policy -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-exchange-alt me-2"></i>Exchange Policy</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 p-4">
                                        <h5 class="text-dark">Size/Color Exchange</h5>
                                        <p class="text-muted small">Exchange for different size or color of the same product, subject to availability.</p>
                                        <span class="badge bg-success">Free Exchange</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 p-4">
                                        <h5 class="text-dark">Product Exchange</h5>
                                        <p class="text-muted small">Exchange for a different product of equal or higher value. Price difference applies.</p>
                                        <span class="badge bg-warning">Price Difference</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Refund Information -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-money-bill-wave me-2"></i>Refund Information</h3>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Payment Method</th>
                                            <th>Refund Timeline</th>
                                            <th>Refund Mode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Credit/Debit Card</td>
                                            <td>5-7 business days</td>
                                            <td>Original payment method</td>
                                        </tr>
                                        <tr>
                                            <td>UPI/Digital Wallet</td>
                                            <td>3-5 business days</td>
                                            <td>Original payment method</td>
                                        </tr>
                                        <tr>
                                            <td>Net Banking</td>
                                            <td>5-7 business days</td>
                                            <td>Bank account</td>
                                        </tr>
                                        <tr>
                                            <td>Cash on Delivery</td>
                                            <td>7-10 business days</td>
                                            <td>Bank transfer</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Special Cases -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-4"><i class="fas fa-star me-2"></i>Special Cases</h3>
                            
                            <div class="accordion" id="specialCasesAccordion">
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#case1">
                                            Damaged in Transit
                                        </button>
                                    </h2>
                                    <div id="case1" class="accordion-collapse collapse show" data-bs-parent="#specialCasesAccordion">
                                        <div class="accordion-body">
                                            If your item arrives damaged, please report it within 24 hours of delivery. We'll arrange immediate replacement or full refund without requiring you to return the damaged item.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#case2">
                                            Wrong Item Delivered
                                        </button>
                                    </h2>
                                    <div id="case2" class="accordion-collapse collapse" data-bs-parent="#specialCasesAccordion">
                                        <div class="accordion-body">
                                            If you receive a wrong item, we'll arrange immediate pickup and send the correct item at no additional cost. You can keep the wrong item until the correct one arrives.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#case3">
                                            Custom/Personalized Items
                                        </button>
                                    </h2>
                                    <div id="case3" class="accordion-collapse collapse" data-bs-parent="#specialCasesAccordion">
                                        <div class="accordion-body">
                                            Custom items can only be returned if there's a manufacturing defect or if the item doesn't match your specifications. Returns due to change of mind are not accepted.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact for Returns -->
                        <div class="text-center">
                            <h4 class="text-dark mb-3">Need Help with Returns?</h4>
                            <p class="text-muted mb-4">Our customer support team is ready to assist you with your return request</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="tel:+919876543210" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-phone me-2"></i>Call Support
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="mailto:returns@kalasetugramdb.com" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-envelope me-2"></i>Email Returns Team
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="support.php" class="btn btn-primary w-100">
                                        <i class="fas fa-headset me-2"></i>Live Support
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
