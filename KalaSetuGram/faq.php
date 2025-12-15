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
    <title>Frequently Asked Questions - KalaSetuGram</title>
    
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
    
    <!-- FAQ Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Frequently Asked Questions</h1>
                    <p class="lead text-muted">Find answers to common questions about KalaSetuGram</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        
                        <!-- General Questions -->
                        <div class="mb-4">
                            <h4 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>General Questions</h4>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is KalaSetuGram?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    KalaSetuGram is a digital platform that bridges traditional Andhra Pradesh crafts with modern technology. We connect skilled artisans with global customers while preserving cultural heritage through AR experiences and authentic storytelling.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Are all products authentic and handmade?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! All our products are 100% authentic, handmade by verified artisans. Many of our crafts carry GI (Geographical Indication) tags, ensuring their authenticity and origin. We work directly with artisan communities to maintain quality and authenticity.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What is AR craft viewing?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Our AR (Augmented Reality) feature allows you to view crafts in 3D using your smartphone or computer camera. You can see how products look in your space, examine details closely, and experience the crafts virtually before purchasing.
                                </div>
                            </div>
                        </div>

                        <!-- Shopping & Orders -->
                        <div class="mb-4 mt-5">
                            <h4 class="text-primary mb-3"><i class="fas fa-shopping-cart me-2"></i>Shopping & Orders</h4>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Simply browse our crafts, add items to your cart, and proceed to checkout. You'll need to create an account, provide shipping details, and choose your payment method. We accept online payments and cash on delivery.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept multiple payment methods including:
                                    <ul class="mt-2">
                                        <li>Credit/Debit Cards (Visa, MasterCard, RuPay)</li>
                                        <li>UPI (Google Pay, PhonePe, Paytm)</li>
                                        <li>Net Banking</li>
                                        <li>Digital Wallets</li>
                                        <li>Cash on Delivery (COD)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    How long does delivery take?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Delivery times vary based on your location and the craft type:
                                    <ul class="mt-2">
                                        <li>Within Andhra Pradesh: 3-5 business days</li>
                                        <li>Other Indian states: 5-7 business days</li>
                                        <li>Custom/Made-to-order items: 10-15 business days</li>
                                        <li>International shipping: 10-21 business days</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Artisan Program -->
                        <div class="mb-4 mt-5">
                            <h4 class="text-primary mb-3"><i class="fas fa-users me-2"></i>Artisan Program</h4>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    How can I become an artisan partner?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Register as an artisan on our platform, submit your craft portfolio, and complete our verification process. We'll review your application and provide training on using our platform. Once approved, you can start listing and selling your crafts.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    What is the Adopt-an-Artisan program?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Our Adopt-an-Artisan program allows you to directly support artisans through monthly contributions. Your support helps artisans with materials, tools, and sustainable income, while you receive exclusive updates and special crafts from your adopted artisan.
                                </div>
                            </div>
                        </div>

                        <!-- Technical Support -->
                        <div class="mb-4 mt-5">
                            <h4 class="text-primary mb-3"><i class="fas fa-cog me-2"></i>Technical Support</h4>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                    AR feature is not working on my device
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ensure your device supports WebXR and camera access is enabled. AR works best on:
                                    <ul class="mt-2">
                                        <li>Android devices with Chrome browser</li>
                                        <li>iOS devices with Safari browser</li>
                                        <li>Desktop with webcam and modern browser</li>
                                    </ul>
                                    Clear your browser cache and try again. Contact support if issues persist.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                    I forgot my password. How do I reset it?
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click on "Forgot Password" on the login page, enter your registered email address, and we'll send you a password reset link. Follow the instructions in the email to create a new password.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Still have questions? -->
                    <div class="text-center mt-5">
                        <div class="bg-white rounded-3 shadow-lg p-4">
                            <h5 class="text-dark mb-3">Still have questions?</h5>
                            <p class="text-muted mb-3">Can't find the answer you're looking for? Our support team is here to help.</p>
                            <a href="contact.php" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Contact Support
                            </a>
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
