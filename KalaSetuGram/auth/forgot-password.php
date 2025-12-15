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
$step = 'email'; // email, otp, reset

if (isset($_GET['step'])) {
    $step = $_GET['step'];
}

if ($_POST) {
    if ($step === 'email') {
        $email = sanitizeInput($_POST['email']);
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if email exists
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate OTP for password reset
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $otpExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = ? WHERE email = ?");
                if ($stmt->execute([$otp, $otpExpires, $email])) {
                    sendOTPEmail($email, $otp, $user['name']);
                    header("Location: forgot-password.php?step=otp&email=" . urlencode($email));
                    exit;
                } else {
                    $error = 'Failed to send reset code. Please try again.';
                }
            } else {
                $error = 'No account found with this email address';
            }
        }
    } elseif ($step === 'otp') {
        $email = $_GET['email'] ?? '';
        $otp = sanitizeInput($_POST['otp']);
        
        if (empty($otp)) {
            $error = 'Please enter the verification code';
        } else {
            $result = verifyOTP($email, $otp);
            if ($result['success']) {
                header("Location: forgot-password.php?step=reset&email=" . urlencode($email) . "&token=" . urlencode($otp));
                exit;
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($step === 'reset') {
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Update password
            $pdo = getConnection();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires = NULL WHERE email = ?");
            if ($stmt->execute([$hashedPassword, $email])) {
                $success = 'Password reset successfully! You can now login with your new password.';
                header("refresh:3;url=login.php");
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KalaSetuGram</title>
    
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
        
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .forgot-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2rem;
        }
        
        .forgot-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .forgot-subtitle {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-floating .form-control {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 1rem 0.75rem;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-forgot {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }
        
        .otp-input {
            width: 50px;
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
            outline: none;
        }
        
        .steps-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 10px;
            position: relative;
        }
        
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
        }
        
        .step.completed:not(:last-child)::after {
            background: #28a745;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 576px) {
            .forgot-card {
                padding: 40px 30px;
            }
            
            .otp-inputs {
                gap: 8px;
            }
            
            .otp-input {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="forgot-card">
            <!-- Steps Indicator -->
            <div class="steps-indicator">
                <div class="step <?php echo $step === 'email' ? 'active' : ($step !== 'email' ? 'completed' : ''); ?>">1</div>
                <div class="step <?php echo $step === 'otp' ? 'active' : ($step === 'reset' ? 'completed' : ''); ?>">2</div>
                <div class="step <?php echo $step === 'reset' ? 'active' : ''; ?>">3</div>
            </div>
            
            <?php if ($step === 'email'): ?>
                <!-- Step 1: Email Input -->
                <div class="forgot-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                
                <h2 class="forgot-title">Forgot Password?</h2>
                <p class="forgot-subtitle">
                    No worries! Enter your email address and we'll send you a verification code to reset your password.
                </p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-forgot">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send Verification Code
                    </button>
                </form>
                
            <?php elseif ($step === 'otp'): ?>
                <!-- Step 2: OTP Verification -->
                <div class="forgot-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                
                <h2 class="forgot-title">Enter Verification Code</h2>
                <p class="forgot-subtitle">
                    We've sent a 6-digit verification code to<br>
                    <strong><?php echo htmlspecialchars($_GET['email'] ?? ''); ?></strong>
                </p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="otpForm">
                    <div class="otp-inputs">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    </div>
                    
                    <input type="hidden" name="otp" id="otpValue">
                    
                    <button type="submit" class="btn btn-primary btn-forgot" id="verifyBtn" disabled>
                        <i class="fas fa-check me-2"></i>
                        Verify Code
                    </button>
                </form>
                
            <?php elseif ($step === 'reset'): ?>
                <!-- Step 3: Password Reset -->
                <div class="forgot-icon">
                    <i class="fas fa-key"></i>
                </div>
                
                <h2 class="forgot-title">Reset Password</h2>
                <p class="forgot-subtitle">
                    Enter your new password below. Make sure it's strong and secure.
                </p>
                
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
                <?php else: ?>
                    <form method="POST" action="" id="resetForm">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-forgot">
                            <i class="fas fa-save me-2"></i>
                            Reset Password
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="mt-3">
                <a href="login.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        <?php if ($step === 'otp'): ?>
        // OTP Input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValue = document.getElementById('otpValue');
        const verifyBtn = document.getElementById('verifyBtn');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                
                updateOTPValue();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
        
        function updateOTPValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpValue.value = otp;
            verifyBtn.disabled = otp.length !== 6;
        }
        
        otpInputs[0].focus();
        <?php endif; ?>
        
        <?php if ($step === 'reset'): ?>
        // Password reset form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
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
        });
        <?php endif; ?>
    </script>
</body>
</html>
