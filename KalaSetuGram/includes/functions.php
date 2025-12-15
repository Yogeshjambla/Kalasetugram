<?php
require_once __DIR__ . '/../config/database.php';

// User Authentication Functions
function registerUser($name, $email, $password, $role = 'buyer', $phone = null, $location = null) {
    $pdo = getConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Skip OTP for now - directly activate user
    $emailVerified = 1; // Set user as verified
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, location, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role, $phone, $location, $emailVerified]);
        
        $userId = $pdo->lastInsertId();
        
        // Auto-login the user after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        
        return ['success' => true, 'user_id' => $userId, 'message' => 'Registration successful! You are now logged in.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

function loginUser($email, $password) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Skip email verification check for now
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

function verifyOTP($email, $otp) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND otp = ? AND otp_expires > NOW()");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Mark email as verified and clear OTP
        $stmt = $pdo->prepare("UPDATE users SET email_verified = TRUE, otp = NULL, otp_expires = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return ['success' => true, 'message' => 'Email verified successfully'];
    }
    
    return ['success' => false, 'message' => 'Invalid or expired OTP'];
}

function getUserById($userId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: auth/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function requireArtisan() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'artisan') {
        header('Location: auth/login.php');
        exit;
    }
}

function isArtisan() {
    return isLoggedIn() && $_SESSION['user_role'] === 'artisan';
}

// Craft Functions
function getAllCrafts($limit = null, $offset = 0, $category = null, $search = null, $sortBy = 'created_at', $sortOrder = 'DESC') {
    $pdo = getConnection();
    
    $sql = "SELECT c.*, cc.name as category_name, cc.gi_tagged, a.user_id, u.name as artisan_name, 
            (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
            FROM crafts c 
            JOIN craft_categories cc ON c.category_id = cc.id 
            JOIN artisans a ON c.artisan_id = a.id 
            JOIN users u ON a.user_id = u.id 
            WHERE c.status = 'active'";
    
    $params = [];
    
    if ($category) {
        $sql .= " AND cc.name = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR cc.name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY c.$sortBy $sortOrder";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCraftById($craftId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT c.*, cc.name as category_name, cc.gi_tagged, a.user_id, u.name as artisan_name, u.location as artisan_location,
               a.craft_type, a.district, a.experience_years, a.bio as artisan_bio
        FROM crafts c 
        JOIN craft_categories cc ON c.category_id = cc.id 
        JOIN artisans a ON c.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$craftId]);
    return $stmt->fetch();
}

function getCraftImages($craftId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM craft_images WHERE craft_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$craftId]);
    return $stmt->fetchAll();
}

function getAllCategories() {
    $pdo = getConnection();
    
    $stmt = $pdo->query("SELECT * FROM craft_categories ORDER BY name");
    return $stmt->fetchAll();
}

function getAllArtisans() {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT a.*, u.name as artisan_name, u.location as artisan_location
                            FROM artisans a 
                            JOIN users u ON a.user_id = u.id 
                            ORDER BY a.id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getFeaturedCrafts($limit = 6) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT c.*, cc.name as category_name, u.name as artisan_name,
               (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
        FROM crafts c 
        JOIN craft_categories cc ON c.category_id = cc.id 
        JOIN artisans a ON c.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE c.status = 'featured' 
        ORDER BY c.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Cart Functions
function addToCart($userId, $craftId, $quantity = 1) {
    $pdo = getConnection();
    
    try {
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND craft_id = ?");
        $stmt->execute([$userId, $craftId]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, craft_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $craftId, $quantity]);
        }
        
        return ['success' => true, 'message' => 'Item added to cart'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to add item to cart'];
    }
}

function getCartItems($userId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT cart.*, c.title, c.price, cc.name as category_name, u.name as artisan_name,
               (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
        FROM cart 
        JOIN crafts c ON cart.craft_id = c.id 
        JOIN craft_categories cc ON c.category_id = cc.id 
        JOIN artisans a ON c.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE cart.user_id = ?
        ORDER BY cart.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function removeFromCart($userId, $cartId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $userId]);
    
    return $stmt->rowCount() > 0;
}

function updateCartQuantity($userId, $cartId, $quantity) {
    $pdo = getConnection();
    
    if ($quantity <= 0) {
        return removeFromCart($userId, $cartId);
    }
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cartId, $userId]);
    
    return $stmt->rowCount() > 0;
}

// Order Functions
function createOrder($userId, $cartItems, $shippingAddress, $paymentMethod, $couponCode = null) {
    $pdo = getConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $taxAmount = $subtotal * 0.18; // 18% GST
        $discountAmount = 0;
        
        // Apply coupon if provided
        if ($couponCode) {
            $coupon = validateCoupon($couponCode, $subtotal);
            if ($coupon) {
                if ($coupon['discount_type'] === 'flat') {
                    $discountAmount = $coupon['discount_value'];
                } else {
                    $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
                }
            }
        }
        
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        
        // Generate order number
        $orderNumber = 'KSG' . date('Ymd') . sprintf('%06d', mt_rand(1, 999999));
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, tax_amount, discount_amount, 
                              shipping_address, payment_method, coupon_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $orderNumber, $totalAmount, $taxAmount, $discountAmount, 
                       $shippingAddress, $paymentMethod, $couponCode]);
        
        $orderId = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, craft_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $stmt->execute([$orderId, $item['craft_id'], $item['quantity'], $item['price'], $itemTotal]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Update coupon usage if applicable
        if ($couponCode && isset($coupon)) {
            $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
            $stmt->execute([$couponCode]);
        }
        
        $pdo->commit();
        
        return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()];
    }
}

// Utility Functions
function sendOTPEmail($email, $otp, $name) {
    // Implement email sending logic here
    // For now, we'll just log it (in production, use PHPMailer or similar)
    error_log("OTP for $email ($name): $otp");
    return true;
}

function validateCoupon($code, $orderAmount) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = TRUE 
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (usage_limit IS NULL OR used_count < usage_limit)
        AND minimum_amount <= ?
    ");
    $stmt->execute([$code, $orderAmount]);
    return $stmt->fetch();
}

function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}
?>
