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
    
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        
        .auth-left {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .auth-right {
            padding: 60px 40px;
        }
        
        .auth-logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .auth-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating .form-control,
        .form-floating .form-select {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 1rem 0.75rem;
        }
        
        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-auth {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .role-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .role-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .role-card:hover {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .role-card.selected {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .role-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .role-card h6 {
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .role-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e0e0e0;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }
        
        .strength-weak { background: #ff4757; width: 25%; }
        .strength-fair { background: #ffa502; width: 50%; }
        .strength-good { background: #2ed573; width: 75%; }
        .strength-strong { background: #1e90ff; width: 100%; }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .auth-left {
                padding: 40px 30px;
            }
            
            .auth-right {
                padding: 40px 30px;
            }
            
            .role-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Left Side - Branding -->
                <div class="col-lg-5">
                    <div class="auth-left">
                        <div>
                            <div class="auth-logo">
                                <i class="fas fa-palette me-2"></i>
                                KalaSetuGram
                            </div>
                            <h3>Join Our Community!</h3>
                            <p class="mb-4">Become part of a vibrant community that celebrates and preserves traditional crafts through modern technology.</p>
                            
                            <div class="features">
                                <div class="feature-item mb-3">
                                    <i class="fas fa-heart me-2"></i>
                                    Support artisan livelihoods
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-globe me-2"></i>
                                    Global marketplace access
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-book-open me-2"></i>
                                    Learn cultural stories
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-handshake me-2"></i>
                                    Direct artisan connection
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Registration Form -->
                <div class="col-lg-7">
                    <div class="auth-right">
                        <div class="text-end mb-3">
                            <a href="../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-1"></i> Back to Home
                            </a>
                        </div>
                        
                        <h2 class="auth-title">Create Account</h2>
                        <p class="auth-subtitle">Fill in your details to get started</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registerForm">
                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">I want to join as:</label>
                                <div class="role-cards">
                                    <div class="role-card" data-role="buyer">
                                        <i class="fas fa-shopping-bag"></i>
                                        <h6>Buyer</h6>
                                        <p>Shop authentic crafts</p>
                                    </div>
                                    <div class="role-card" data-role="artisan">
                                        <i class="fas fa-palette"></i>
                                        <h6>Artisan</h6>
                                        <p>Sell your crafts</p>
                                    </div>
                                    <div class="role-card" data-role="tourist">
                                        <i class="fas fa-camera"></i>
                                        <h6>Tourist</h6>
                                        <p>Explore heritage</p>
                                    </div>
                                </div>
                                <input type="hidden" name="role" id="selectedRole" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                                        <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone">
                                        <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="location" name="location" placeholder="Location">
                                        <label for="location"><i class="fas fa-map-marker-alt me-2"></i>City/District</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <small class="text-muted" id="strengthText">Password strength</small>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="../terms-conditions.php" target="_blank">Terms & Conditions</a> 
                                    and <a href="../privacy-policy.php" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-auth">
                                <i class="fas fa-user-plus me-2"></i>
                                Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-decoration-none fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Role selection
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Set hidden input value
                document.getElementById('selectedRole').value = this.dataset.role;
            });
        });
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = 'Very Weak';
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Remove all strength classes
            strengthFill.className = 'strength-fill';
            
            if (strength <= 2) {
                strengthFill.classList.add('strength-weak');
                text = 'Weak';
            } else if (strength <= 3) {
                strengthFill.classList.add('strength-fair');
                text = 'Fair';
            } else if (strength <= 4) {
                strengthFill.classList.add('strength-good');
                text = 'Good';
            } else {
                strengthFill.classList.add('strength-strong');
                text = 'Strong';
            }
            
            strengthText.textContent = text;
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const role = document.getElementById('selectedRole').value;
            const terms = document.getElementById('terms').checked;
            
            if (!name || !email || !password || !confirmPassword || !role) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Please accept the Terms & Conditions');
                return;
            }
        });
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
