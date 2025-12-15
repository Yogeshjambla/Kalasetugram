<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $phone = sanitize($_POST['phone']);
    $location = sanitize($_POST['location']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $result = registerUser($name, $email, $password, $role, $phone, $location);
        if ($result['success']) {
            $success = $result['message'];
            // Redirect to OTP verification
            header("Location: verify-otp.php?email=" . urlencode($email));
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%); min-height: 100vh;">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 p-4">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <!-- Left Side - Branding -->
                        <div class="col-lg-5 d-none d-lg-block">
                            <div class="h-100 p-5 d-flex flex-column justify-content-center" style="background: linear-gradient(135deg, #833AB4 0%, #E4405F 100%); color: white;">
                                <div class="text-center mb-4">
                                    <img src="../images/kalasetugramlogo.png" alt="KalaSetuGram" class="mb-3" style="height: 60px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <h2 class="fw-bold" style="display: none;">KalaSetuGram</h2>
                                </div>
                                <h3 class="fw-bold mb-3 text-center">Join Our Community!</h3>
                                <p class="text-center mb-4 opacity-90">Become part of a vibrant community connecting artisans and craft lovers worldwide.</p>
                                
                                <div class="features">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="fas fa-handshake"></i>
                                        </div>
                                        <span>Support Local Artisans</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="fas fa-shopping-bag"></i>
                                        </div>
                                        <span>Shop Authentic Crafts</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                        <span>Preserve Cultural Heritage</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Registration Form -->
                        <div class="col-lg-7">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h3 fw-bold text-dark mb-2">Create Your Account</h1>
                                    <p class="text-muted">Join KalaSetuGram and start your craft journey</p>
                                </div>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-semibold">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-user text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" placeholder="Enter your full name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-semibold">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-muted"></i>
                                                </span>
                                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="Enter your email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-phone text-muted"></i>
                                                </span>
                                                <input type="tel" class="form-control border-start-0 ps-0" id="phone" name="phone" placeholder="Enter your phone number">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="location" class="form-label fw-semibold">Location</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0 ps-0" id="location" name="location" placeholder="City, State">
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="role" class="form-label fw-semibold">I want to join as</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">Select your role</option>
                                                <option value="buyer">Buyer - I want to purchase crafts</option>
                                                <option value="artisan">Artisan - I want to sell my crafts</option>
                                                <option value="tourist">Tourist - I want to explore heritage</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="password" class="form-label fw-semibold">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Create a password" required>
                                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password', 'toggleIcon1')">
                                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                                <label class="form-check-label text-muted" for="terms">
                                                    I agree to the <a href="../terms-conditions.php" class="text-primary text-decoration-none" target="_blank">Terms & Conditions</a> and <a href="../privacy-policy.php" class="text-primary text-decoration-none" target="_blank">Privacy Policy</a>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mb-3">
                                                <i class="fas fa-user-plus me-2"></i>Create Account
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="text-center mb-3">
                                    <span class="text-muted">or sign up with</span>
                                </div>
                                
                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <button class="btn btn-outline-danger w-100 py-2">
                                            <i class="fab fa-google me-2"></i>Google
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-primary w-100 py-2">
                                            <i class="fab fa-facebook-f me-2"></i>Facebook
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <p class="text-muted mb-0">Already have an account? <a href="login.php" class="text-primary text-decoration-none fw-semibold">Sign in here</a></p>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="../index.php" class="text-muted text-decoration-none">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
            
            if (strengthBar && strengthText) {
                strengthBar.className = `progress-bar bg-${strengthColors[strength]}`;
                strengthBar.style.width = `${(strength / 5) * 100}%`;
                strengthText.textContent = strengthLevels[strength] || '';
                strengthText.className = `small text-${strengthColors[strength]}`;
            }
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
