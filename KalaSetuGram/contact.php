<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Handle form submission
if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Here you would typically send an email or save to database
    $success_message = "Thank you for contacting us! We'll get back to you within 24 hours.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KalaSetuGram</title>
    
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
    
    <!-- Contact Hero Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-dark mb-3">Contact Us</h1>
                    <p class="lead text-muted">We'd love to hear from you. Get in touch with our team.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="bg-white rounded-3 shadow-lg p-4 p-md-5">
                        <h3 class="text-dark mb-4">Send us a Message</h3>
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
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4">
                    <div class="bg-white rounded-3 shadow-lg p-4 p-md-5">
                        <h3 class="text-dark mb-4">Get in Touch</h3>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-map-marker-alt text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark">Address</h6>
                                    <p class="text-muted mb-0">Hyderabad, Andhra Pradesh<br>India - 500001</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark">Phone</h6>
                                    <p class="text-muted mb-0">+91 9876543210</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark">Email</h6>
                                    <p class="text-muted mb-0">info@kalasetugramdb.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-clock text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark">Business Hours</h6>
                                    <p class="text-muted mb-0">Mon - Sat: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="social-links mt-4">
                            <h6 class="fw-bold text-dark mb-3">Follow Us</h6>
                            <a href="#" class="text-primary me-3 fs-4"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-primary me-3 fs-4"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary me-3 fs-4"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-primary fs-4"><i class="fab fa-youtube"></i></a>
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
