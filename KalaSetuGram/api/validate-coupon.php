<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$couponCode = trim($input['coupon_code'] ?? '');
$cartTotal = floatval($input['cart_total'] ?? 0);

if (empty($couponCode)) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
    exit;
}

if ($cartTotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart total']);
    exit;
}

// Validate coupon
$coupon = validateCoupon($couponCode, $cartTotal);

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
    exit;
}

// Calculate discount amount
$discountAmount = 0;
if ($coupon['discount_type'] === 'flat') {
    $discountAmount = min($coupon['discount_value'], $cartTotal);
} else {
    $discountAmount = ($cartTotal * $coupon['discount_value']) / 100;
}

// Ensure discount doesn't exceed cart total
$discountAmount = min($discountAmount, $cartTotal);

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully',
    'coupon_code' => $couponCode,
    'discount_type' => $coupon['discount_type'],
    'discount_value' => $coupon['discount_value'],
    'discount_amount' => round($discountAmount, 2),
    'description' => $coupon['description']
]);
?>
