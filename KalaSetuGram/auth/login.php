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
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            header('Location: ../index.php');
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
    <title>Login - KalaSetuGram</title>
    
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
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <!-- Left Side - Branding -->
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="h-100 p-5 d-flex flex-column justify-content-center" style="background: linear-gradient(135deg, #E4405F 0%, #833AB4 100%); color: white;">
                                <div class="text-center mb-4">
                                    <img src="../images/kalasetugramlogo.png" alt="KalaSetuGram" class="mb-3 rounded-circle bg-white p-2" style="height: 80px; width: 80px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <h2 class="fw-bold" style="display: none;">KalaSetuGram</h2>
                                </div>
                                <h3 class="fw-bold mb-3 text-center">Welcome Back!</h3>
                                <p class="text-center mb-4 opacity-90">Connect with traditional artisans and discover authentic crafts from Andhra Pradesh.</p>
                                
                                <div class="features">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-palette text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <span>Authentic Handmade Crafts</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-users text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <span>Connect with Artisans</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-cube text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <span>AR Craft Experience</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Login Form -->
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h3 fw-bold text-dark mb-2">Sign In to Your Account</h1>
                                    <p class="text-muted">Enter your credentials to access your account</p>
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
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-envelope text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="Enter your email" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Enter your password" required>
                                            <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword()">
                                                <i class="fas fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label text-muted" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                        <a href="forgot-password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </button>
                                </form>
                                
                                <div class="text-center mb-3">
                                    <span class="text-muted">or continue with</span>
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
                                    <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="text-primary text-decoration-none fw-semibold">Sign up here</a></p>
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
    </script>
</body>
</html>
