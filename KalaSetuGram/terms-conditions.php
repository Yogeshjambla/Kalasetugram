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
    <title>Terms & Conditions - KalaSetuGram</title>
    
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
    
    <!-- Terms Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Terms & Conditions</h1>
                    <p class="lead text-muted">Please read these terms carefully before using our services</p>
                    <p class="small text-muted">Last updated: <?php echo date('F d, Y'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        
                        <!-- Introduction -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">1. Introduction</h3>
                            <p>Welcome to KalaSetuGram ("we," "our," or "us"). These Terms and Conditions ("Terms") govern your use of our website and services. By accessing or using KalaSetuGram, you agree to be bound by these Terms.</p>
                            <p>KalaSetuGram is a digital platform that connects traditional artisans from Andhra Pradesh with customers worldwide, promoting authentic handmade crafts and cultural heritage.</p>
                        </div>

                        <!-- Definitions -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">2. Definitions</h3>
                            <ul>
                                <li><strong>"Platform"</strong> refers to the KalaSetuGram website and mobile applications</li>
                                <li><strong>"User"</strong> refers to any person who accesses or uses our Platform</li>
                                <li><strong>"Artisan"</strong> refers to verified craftspeople who sell products on our Platform</li>
                                <li><strong>"Buyer"</strong> refers to users who purchase products from our Platform</li>
                                <li><strong>"Content"</strong> refers to all text, images, videos, and other materials on our Platform</li>
                            </ul>
                        </div>

                        <!-- User Accounts -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">3. User Accounts</h3>
                            <h5>3.1 Account Registration</h5>
                            <ul>
                                <li>You must provide accurate and complete information when creating an account</li>
                                <li>You are responsible for maintaining the confidentiality of your account credentials</li>
                                <li>You must be at least 18 years old to create an account</li>
                                <li>One person may not maintain multiple accounts</li>
                            </ul>
                            
                            <h5>3.2 Account Responsibilities</h5>
                            <ul>
                                <li>You are responsible for all activities that occur under your account</li>
                                <li>Notify us immediately of any unauthorized use of your account</li>
                                <li>We reserve the right to suspend or terminate accounts that violate these Terms</li>
                            </ul>
                        </div>

                        <!-- Platform Usage -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">4. Platform Usage</h3>
                            <h5>4.1 Permitted Use</h5>
                            <ul>
                                <li>Browse and purchase authentic handmade crafts</li>
                                <li>Create and manage your user profile</li>
                                <li>Communicate with artisans through our messaging system</li>
                                <li>Leave honest reviews and ratings</li>
                                <li>Use AR features for product visualization</li>
                            </ul>
                            
                            <h5>4.2 Prohibited Activities</h5>
                            <ul>
                                <li>Violating any applicable laws or regulations</li>
                                <li>Infringing on intellectual property rights</li>
                                <li>Posting false, misleading, or defamatory content</li>
                                <li>Attempting to hack or disrupt our Platform</li>
                                <li>Selling counterfeit or non-authentic products</li>
                                <li>Harassing or threatening other users</li>
                            </ul>
                        </div>

                        <!-- Artisan Terms -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">5. Artisan Terms</h3>
                            <h5>5.1 Artisan Verification</h5>
                            <ul>
                                <li>All artisans must complete our verification process</li>
                                <li>Products must be authentic, handmade crafts</li>
                                <li>Artisans must provide accurate product descriptions and images</li>
                                <li>GI-tagged products must have proper certification</li>
                            </ul>
                            
                            <h5>5.2 Commission and Payments</h5>
                            <ul>
                                <li>KalaSetuGram charges a commission on each sale</li>
                                <li>Payments are processed within 7-14 business days after delivery confirmation</li>
                                <li>Artisans are responsible for applicable taxes on their earnings</li>
                            </ul>
                        </div>

                        <!-- Orders and Payments -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">6. Orders and Payments</h3>
                            <h5>6.1 Order Process</h5>
                            <ul>
                                <li>All orders are subject to acceptance by the artisan</li>
                                <li>Prices are displayed in Indian Rupees (INR)</li>
                                <li>We reserve the right to cancel orders for any reason</li>
                                <li>Custom orders may have different terms and timelines</li>
                            </ul>
                            
                            <h5>6.2 Payment Terms</h5>
                            <ul>
                                <li>Payment must be made at the time of order placement</li>
                                <li>We accept various payment methods as displayed on our Platform</li>
                                <li>All transactions are processed securely through our payment partners</li>
                                <li>Refunds are processed according to our Return Policy</li>
                            </ul>
                        </div>

                        <!-- Intellectual Property -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">7. Intellectual Property</h3>
                            <h5>7.1 Our Rights</h5>
                            <ul>
                                <li>KalaSetuGram owns all rights to our Platform, including design, code, and content</li>
                                <li>Our trademarks, logos, and brand names are protected</li>
                                <li>Users may not reproduce, distribute, or create derivative works without permission</li>
                            </ul>
                            
                            <h5>7.2 User Content</h5>
                            <ul>
                                <li>You retain ownership of content you upload to our Platform</li>
                                <li>You grant us a license to use, display, and promote your content</li>
                                <li>You represent that you have the right to share all content you upload</li>
                            </ul>
                        </div>

                        <!-- Privacy and Data -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">8. Privacy and Data Protection</h3>
                            <ul>
                                <li>Your privacy is important to us. Please review our Privacy Policy</li>
                                <li>We collect and use personal information as described in our Privacy Policy</li>
                                <li>We implement appropriate security measures to protect your data</li>
                                <li>You have rights regarding your personal data as per applicable laws</li>
                            </ul>
                        </div>

                        <!-- Disclaimers -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">9. Disclaimers and Limitations</h3>
                            <h5>9.1 Platform Availability</h5>
                            <ul>
                                <li>We strive for 99.9% uptime but cannot guarantee uninterrupted service</li>
                                <li>Maintenance and updates may temporarily affect availability</li>
                                <li>We are not liable for losses due to service interruptions</li>
                            </ul>
                            
                            <h5>9.2 Product Quality</h5>
                            <ul>
                                <li>While we verify artisans, we cannot guarantee the quality of all products</li>
                                <li>Artisans are primarily responsible for product quality and descriptions</li>
                                <li>We facilitate transactions but are not party to the sale contract</li>
                            </ul>
                        </div>

                        <!-- Termination -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">10. Termination</h3>
                            <ul>
                                <li>You may terminate your account at any time by contacting us</li>
                                <li>We may suspend or terminate accounts that violate these Terms</li>
                                <li>Upon termination, your right to use our Platform ceases immediately</li>
                                <li>Certain provisions of these Terms survive termination</li>
                            </ul>
                        </div>

                        <!-- Governing Law -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">11. Governing Law and Disputes</h3>
                            <ul>
                                <li>These Terms are governed by the laws of India</li>
                                <li>Any disputes will be resolved in the courts of Hyderabad, Telangana</li>
                                <li>We encourage resolving disputes through our customer support first</li>
                                <li>Arbitration may be required for certain types of disputes</li>
                            </ul>
                        </div>

                        <!-- Changes to Terms -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">12. Changes to Terms</h3>
                            <ul>
                                <li>We may update these Terms from time to time</li>
                                <li>Significant changes will be communicated via email or Platform notifications</li>
                                <li>Continued use of our Platform constitutes acceptance of updated Terms</li>
                                <li>You should review these Terms periodically</li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">13. Contact Information</h3>
                            <p>If you have questions about these Terms, please contact us:</p>
                            <div class="bg-light rounded-3 p-4">
                                <p class="mb-2"><strong>KalaSetuGram</strong></p>
                                <p class="mb-2"><i class="fas fa-envelope me-2"></i>legal@kalasetugramdb.com</p>
                                <p class="mb-2"><i class="fas fa-phone me-2"></i>+91 9876543210</p>
                                <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Hyderabad, Andhra Pradesh, India</p>
                            </div>
                        </div>

                        <!-- Acceptance -->
                        <div class="alert alert-primary">
                            <h5><i class="fas fa-info-circle me-2"></i>Acceptance of Terms</h5>
                            <p class="mb-0">By using KalaSetuGram, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
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
