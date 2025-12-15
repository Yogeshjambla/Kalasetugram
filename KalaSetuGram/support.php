<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Handle support ticket submission
if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $category = sanitize($_POST['category']);
    $priority = sanitize($_POST['priority']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Generate ticket ID
    $ticket_id = 'TKT' . date('Ymd') . rand(1000, 9999);
    
    // Here you would typically save to database
    $success_message = "Support ticket #{$ticket_id} has been created successfully. We'll respond within 24 hours.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support - KalaSetuGram</title>
    
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
    
    <!-- Support Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Customer Support</h1>
                    <p class="lead text-muted">We're here to help! Get assistance with your orders, account, or any questions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Quick Support Options -->
            <div class="row g-4 mb-5">
                <div class="col-12 text-center mb-4">
                    <h3 class="text-dark">How Can We Help You?</h3>
                    <p class="text-muted">Choose the best way to get support</p>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="support-card bg-white rounded-3 shadow-lg p-4 text-center h-100">
                        <div class="support-icon mb-3">
                            <i class="fas fa-comments text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-dark">Live Chat</h5>
                        <p class="text-muted small">Get instant help from our support team</p>
                        <button class="btn btn-primary btn-sm" onclick="startLiveChat()">Start Chat</button>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="support-card bg-white rounded-3 shadow-lg p-4 text-center h-100">
                        <div class="support-icon mb-3">
                            <i class="fas fa-phone text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-dark">Phone Support</h5>
                        <p class="text-muted small">Speak directly with our experts</p>
                        <a href="tel:+919876543210" class="btn btn-success btn-sm">Call Now</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="support-card bg-white rounded-3 shadow-lg p-4 text-center h-100">
                        <div class="support-icon mb-3">
                            <i class="fas fa-envelope text-info" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-dark">Email Support</h5>
                        <p class="text-muted small">Send us detailed questions</p>
                        <a href="mailto:support@kalasetugramdb.com" class="btn btn-info btn-sm">Send Email</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="support-card bg-white rounded-3 shadow-lg p-4 text-center h-100">
                        <div class="support-icon mb-3">
                            <i class="fab fa-whatsapp text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-dark">WhatsApp</h5>
                        <p class="text-muted small">Quick support via WhatsApp</p>
                        <a href="https://wa.me/919876543210" class="btn btn-success btn-sm" target="_blank">Message Us</a>
                    </div>
                </div>
            </div>

            <!-- Support Ticket Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        <h3 class="text-dark mb-4 text-center">Create Support Ticket</h3>
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="order">Order Issues</option>
                                        <option value="payment">Payment Problems</option>
                                        <option value="shipping">Shipping & Delivery</option>
                                        <option value="returns">Returns & Exchanges</option>
                                        <option value="account">Account Issues</option>
                                        <option value="technical">Technical Support</option>
                                        <option value="artisan">Artisan Support</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Describe Your Issue</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Please provide as much detail as possible to help us assist you better..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-ticket-alt me-2"></i>Create Ticket
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-white rounded-3 shadow-lg p-5">
                        <h3 class="text-dark mb-4 text-center">Quick Answers</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="faq-item">
                                    <h6 class="text-primary"><i class="fas fa-question-circle me-2"></i>How do I track my order?</h6>
                                    <p class="text-muted small">Go to 'My Orders' in your account and click on the order number to see tracking details.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="faq-item">
                                    <h6 class="text-primary"><i class="fas fa-question-circle me-2"></i>How do I return an item?</h6>
                                    <p class="text-muted small">Visit 'My Orders', select the item, and click 'Return'. We'll arrange free pickup.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="faq-item">
                                    <h6 class="text-primary"><i class="fas fa-question-circle me-2"></i>When will I get my refund?</h6>
                                    <p class="text-muted small">Refunds are processed within 3-5 business days after we receive the returned item.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="faq-item">
                                    <h6 class="text-primary"><i class="fas fa-question-circle me-2"></i>Can I change my order?</h6>
                                    <p class="text-muted small">Orders can be modified within 2 hours of placement. Contact support immediately.</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <a href="faq.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View All FAQs
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Hours -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-primary text-white rounded-3 p-4 text-center">
                        <h4 class="mb-3">Support Hours</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <i class="fas fa-clock fs-3 mb-2"></i>
                                <p class="mb-0"><strong>Phone & Chat</strong><br>Mon-Sat: 9 AM - 8 PM</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-envelope fs-3 mb-2"></i>
                                <p class="mb-0"><strong>Email Support</strong><br>24/7 - Response within 24 hours</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fab fa-whatsapp fs-3 mb-2"></i>
                                <p class="mb-0"><strong>WhatsApp</strong><br>Mon-Sat: 9 AM - 6 PM</p>
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
    
    <script>
        function startLiveChat() {
            alert('Live chat feature will be available soon! Please use phone or email support for immediate assistance.');
        }
    </script>
</body>
</html>
