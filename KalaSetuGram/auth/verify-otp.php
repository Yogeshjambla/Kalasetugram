<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$email = $_GET['email'] ?? '';
if (empty($email)) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $otp = sanitizeInput($_POST['otp']);
    
    if (empty($otp)) {
        $error = 'Please enter the OTP';
    } elseif (strlen($otp) !== 6) {
        $error = 'OTP must be 6 digits';
    } else {
        $result = verifyOTP($email, $otp);
        if ($result['success']) {
            $success = $result['message'];
            // Auto redirect to login after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $error = $result['message'];
        }
    }
}

// Resend OTP functionality
if (isset($_POST['resend_otp'])) {
    // Generate new OTP and update database
    $pdo = getConnection();
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $otpExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = ? WHERE email = ?");
    if ($stmt->execute([$otp, $otpExpires, $email])) {
        sendOTPEmail($email, $otp, '');
        $success = 'New OTP sent to your email address';
    } else {
        $error = 'Failed to resend OTP. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - KalaSetuGram</title>
    
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
        
        .otp-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .otp-icon {
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
        
        .otp-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .otp-subtitle {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
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
        
        .otp-input.filled {
            border-color: var(--primary-color);
            background: var(--accent-color);
        }
        
        .btn-verify {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .resend-section {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 2rem;
        }
        
        .countdown {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 576px) {
            .otp-card {
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
        <div class="otp-card">
            <div class="otp-icon">
                <i class="fas fa-envelope-open"></i>
            </div>
            
            <h2 class="otp-title">Verify Your Email</h2>
            <p class="otp-subtitle">
                We've sent a 6-digit verification code to<br>
                <strong><?php echo htmlspecialchars($email); ?></strong>
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
                    <div class="mt-2">
                        <small>Redirecting to login page...</small>
                    </div>
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
                
                <button type="submit" class="btn btn-primary btn-verify" id="verifyBtn" disabled>
                    <i class="fas fa-check me-2"></i>
                    Verify Email
                </button>
            </form>
            
            <div class="resend-section">
                <p class="mb-2">Didn't receive the code?</p>
                <div id="resendTimer" class="countdown mb-3">
                    Resend available in <span id="countdown">60</span> seconds
                </div>
                <form method="POST" action="" id="resendForm" style="display: none;">
                    <button type="submit" name="resend_otp" class="btn btn-outline-primary">
                        <i class="fas fa-redo me-2"></i>
                        Resend OTP
                    </button>
                </form>
            </div>
            
            <div class="mt-3">
                <a href="register.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Registration
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // OTP Input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValue = document.getElementById('otpValue');
        const verifyBtn = document.getElementById('verifyBtn');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value) {
                    this.classList.add('filled');
                    
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }
                
                updateOTPValue();
            });
            
            input.addEventListener('keydown', function(e) {
                // Handle backspace
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].value = '';
                    otpInputs[index - 1].classList.remove('filled');
                    updateOTPValue();
                }
                
                // Handle paste
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    navigator.clipboard.readText().then(text => {
                        const digits = text.replace(/[^0-9]/g, '').slice(0, 6);
                        fillOTP(digits);
                    });
                }
            });
        });
        
        function updateOTPValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpValue.value = otp;
            
            // Enable/disable verify button
            if (otp.length === 6) {
                verifyBtn.disabled = false;
                verifyBtn.classList.remove('btn-secondary');
                verifyBtn.classList.add('btn-primary');
            } else {
                verifyBtn.disabled = true;
                verifyBtn.classList.remove('btn-primary');
                verifyBtn.classList.add('btn-secondary');
            }
        }
        
        function fillOTP(digits) {
            otpInputs.forEach((input, index) => {
                if (digits[index]) {
                    input.value = digits[index];
                    input.classList.add('filled');
                } else {
                    input.value = '';
                    input.classList.remove('filled');
                }
            });
            updateOTPValue();
        }
        
        // Countdown timer for resend
        let countdown = 60;
        const countdownElement = document.getElementById('countdown');
        const resendTimer = document.getElementById('resendTimer');
        const resendForm = document.getElementById('resendForm');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                resendTimer.style.display = 'none';
                resendForm.style.display = 'block';
            }
        }, 1000);
        
        // Form submission
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const otp = otpValue.value;
            
            if (otp.length !== 6) {
                e.preventDefault();
                alert('Please enter the complete 6-digit OTP');
                return;
            }
        });
        
        // Auto-focus first input
        otpInputs[0].focus();
        
        // Resend form handling
        document.getElementById('resendForm').addEventListener('submit', function() {
            // Reset countdown
            countdown = 60;
            countdownElement.textContent = countdown;
            resendTimer.style.display = 'block';
            resendForm.style.display = 'none';
            
            // Restart timer
            const newTimer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(newTimer);
                    resendTimer.style.display = 'none';
                    resendForm.style.display = 'block';
                }
            }, 1000);
        });
    </script>
</body>
</html>
