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
    <title>Privacy Policy - KalaSetuGram</title>
    
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
    
    <!-- Privacy Policy Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Privacy Policy</h1>
                    <p class="lead text-muted">Your privacy is important to us. Learn how we collect, use, and protect your information.</p>
                    <p class="small text-muted">Last updated: <?php echo date('F d, Y'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Policy Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        
                        <!-- Introduction -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">1. Introduction</h3>
                            <p>KalaSetuGram ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
                            <p>By using KalaSetuGram, you consent to the data practices described in this Privacy Policy.</p>
                        </div>

                        <!-- Information We Collect -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">2. Information We Collect</h3>
                            
                            <h5>2.1 Personal Information</h5>
                            <p>We may collect personal information that you provide directly to us, including:</p>
                            <ul>
                                <li>Name, email address, and phone number</li>
                                <li>Shipping and billing addresses</li>
                                <li>Payment information (processed securely by our payment partners)</li>
                                <li>Profile information and preferences</li>
                                <li>Communication history with our support team</li>
                            </ul>
                            
                            <h5>2.2 Automatically Collected Information</h5>
                            <p>When you visit our Platform, we automatically collect certain information:</p>
                            <ul>
                                <li>IP address and device information</li>
                                <li>Browser type and version</li>
                                <li>Pages visited and time spent on our Platform</li>
                                <li>Referring website information</li>
                                <li>Location data (with your permission)</li>
                            </ul>
                            
                            <h5>2.3 Cookies and Tracking Technologies</h5>
                            <p>We use cookies and similar technologies to:</p>
                            <ul>
                                <li>Remember your preferences and settings</li>
                                <li>Analyze website traffic and usage patterns</li>
                                <li>Provide personalized content and recommendations</li>
                                <li>Improve our Platform's functionality</li>
                            </ul>
                        </div>

                        <!-- How We Use Information -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">3. How We Use Your Information</h3>
                            <p>We use the collected information for various purposes:</p>
                            
                            <h5>3.1 Service Provision</h5>
                            <ul>
                                <li>Process and fulfill your orders</li>
                                <li>Manage your account and profile</li>
                                <li>Provide customer support</li>
                                <li>Send order confirmations and updates</li>
                                <li>Enable AR features and personalization</li>
                            </ul>
                            
                            <h5>3.2 Communication</h5>
                            <ul>
                                <li>Send important notices about our services</li>
                                <li>Respond to your inquiries and requests</li>
                                <li>Send marketing communications (with your consent)</li>
                                <li>Notify you about new products and features</li>
                            </ul>
                            
                            <h5>3.3 Platform Improvement</h5>
                            <ul>
                                <li>Analyze usage patterns to improve our Platform</li>
                                <li>Develop new features and services</li>
                                <li>Conduct research and analytics</li>
                                <li>Prevent fraud and ensure security</li>
                            </ul>
                        </div>

                        <!-- Information Sharing -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">4. How We Share Your Information</h3>
                            <p>We may share your information in the following circumstances:</p>
                            
                            <h5>4.1 With Artisans</h5>
                            <ul>
                                <li>Order details and shipping information for order fulfillment</li>
                                <li>Communication through our messaging system</li>
                                <li>Reviews and ratings (publicly visible)</li>
                            </ul>
                            
                            <h5>4.2 With Service Providers</h5>
                            <ul>
                                <li>Payment processors for transaction processing</li>
                                <li>Shipping companies for order delivery</li>
                                <li>Cloud storage providers for data hosting</li>
                                <li>Analytics providers for platform improvement</li>
                            </ul>
                            
                            <h5>4.3 Legal Requirements</h5>
                            <ul>
                                <li>When required by law or legal process</li>
                                <li>To protect our rights and property</li>
                                <li>To ensure user safety and prevent fraud</li>
                                <li>In connection with business transfers or mergers</li>
                            </ul>
                        </div>

                        <!-- Data Security -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">5. Data Security</h3>
                            <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="security-item">
                                        <h6><i class="fas fa-shield-alt text-success me-2"></i>Encryption</h6>
                                        <p class="small text-muted">All data transmission is encrypted using SSL/TLS protocols</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="security-item">
                                        <h6><i class="fas fa-lock text-success me-2"></i>Access Controls</h6>
                                        <p class="small text-muted">Strict access controls limit who can view your information</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="security-item">
                                        <h6><i class="fas fa-server text-success me-2"></i>Secure Storage</h6>
                                        <p class="small text-muted">Data is stored on secure servers with regular backups</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="security-item">
                                        <h6><i class="fas fa-eye text-success me-2"></i>Regular Monitoring</h6>
                                        <p class="small text-muted">Continuous monitoring for security threats and vulnerabilities</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Your Rights -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">6. Your Privacy Rights</h3>
                            <p>You have certain rights regarding your personal information:</p>
                            
                            <div class="accordion" id="rightsAccordion">
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#right1">
                                            <i class="fas fa-eye me-2"></i>Right to Access
                                        </button>
                                    </h2>
                                    <div id="right1" class="accordion-collapse collapse show" data-bs-parent="#rightsAccordion">
                                        <div class="accordion-body">
                                            You can request a copy of the personal information we hold about you. Contact our support team to make this request.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right2">
                                            <i class="fas fa-edit me-2"></i>Right to Correction
                                        </button>
                                    </h2>
                                    <div id="right2" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                        <div class="accordion-body">
                                            You can update or correct your personal information through your account settings or by contacting us.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right3">
                                            <i class="fas fa-trash me-2"></i>Right to Deletion
                                        </button>
                                    </h2>
                                    <div id="right3" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                        <div class="accordion-body">
                                            You can request deletion of your personal information, subject to certain legal and business requirements.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right4">
                                            <i class="fas fa-ban me-2"></i>Right to Opt-Out
                                        </button>
                                    </h2>
                                    <div id="right4" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                        <div class="accordion-body">
                                            You can opt-out of marketing communications at any time by clicking unsubscribe links or updating your preferences.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Retention -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">7. Data Retention</h3>
                            <p>We retain your personal information for as long as necessary to:</p>
                            <ul>
                                <li>Provide our services to you</li>
                                <li>Comply with legal obligations</li>
                                <li>Resolve disputes and enforce agreements</li>
                                <li>Improve our Platform and services</li>
                            </ul>
                            <p>When we no longer need your information, we securely delete or anonymize it.</p>
                        </div>

                        <!-- Children's Privacy -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">8. Children's Privacy</h3>
                            <div class="alert alert-warning">
                                <i class="fas fa-child me-2"></i>
                                <strong>Age Restriction:</strong> Our Platform is not intended for children under 18 years of age. We do not knowingly collect personal information from children under 18.
                            </div>
                            <p>If you believe we have collected information from a child under 18, please contact us immediately so we can delete such information.</p>
                        </div>

                        <!-- International Transfers -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">9. International Data Transfers</h3>
                            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place:</p>
                            <ul>
                                <li>Adequate data protection laws in the destination country</li>
                                <li>Contractual protections with service providers</li>
                                <li>Your explicit consent for the transfer</li>
                            </ul>
                        </div>

                        <!-- Changes to Privacy Policy -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">10. Changes to This Privacy Policy</h3>
                            <p>We may update this Privacy Policy from time to time. When we make changes:</p>
                            <ul>
                                <li>We will post the updated policy on our Platform</li>
                                <li>We will update the "Last Updated" date</li>
                                <li>For significant changes, we will notify you via email</li>
                                <li>Continued use of our Platform constitutes acceptance of the updated policy</li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-5">
                            <h3 class="text-primary mb-3">11. Contact Us</h3>
                            <p>If you have questions about this Privacy Policy or our data practices, please contact us:</p>
                            <div class="bg-light rounded-3 p-4">
                                <p class="mb-2"><strong>Data Protection Officer</strong></p>
                                <p class="mb-2"><i class="fas fa-envelope me-2"></i>privacy@kalasetugramdb.com</p>
                                <p class="mb-2"><i class="fas fa-phone me-2"></i>+91 9876543210</p>
                                <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Hyderabad, Andhra Pradesh, India</p>
                            </div>
                        </div>

                        <!-- Consent -->
                        <div class="alert alert-primary">
                            <h5><i class="fas fa-handshake me-2"></i>Your Consent</h5>
                            <p class="mb-0">By using KalaSetuGram, you consent to the collection and use of your information as described in this Privacy Policy.</p>
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
