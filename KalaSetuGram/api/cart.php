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

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

if ($method === 'POST') {
    // Add item to cart or update quantity
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'add') {
        $craftId = intval($input['craft_id'] ?? 0);
        $quantity = intval($input['quantity'] ?? 1);
        
        if (!$craftId) {
            echo json_encode(['success' => false, 'message' => 'Invalid craft ID']);
            exit;
        }
        
        // Check if craft exists and is available
        $craft = getCraftById($craftId);
        if (!$craft || $craft['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Craft not available']);
            exit;
        }
        
        // Check stock
        if ($quantity > $craft['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        $result = addToCart($userId, $craftId, $quantity);
        echo json_encode($result);
        
    } elseif ($action === 'update') {
        $cartId = intval($input['cart_id'] ?? 0);
        $quantity = intval($input['quantity'] ?? 1);
        
        if (!$cartId) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
            exit;
        }
        
        $success = updateCartQuantity($userId, $cartId, $quantity);
        echo json_encode(['success' => $success]);
        
    } elseif ($action === 'remove') {
        $cartId = intval($input['cart_id'] ?? 0);
        
        if (!$cartId) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
            exit;
        }
        
        $success = removeFromCart($userId, $cartId);
        echo json_encode(['success' => $success]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'list') {
        // Get cart items
        $cartItems = getCartItems($userId);
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $taxAmount = $subtotal * 0.18; // 18% GST
        $total = $subtotal + $taxAmount;
        
        echo json_encode([
            'success' => true,
            'items' => $cartItems,
            'count' => count($cartItems),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total
        ]);
        
    } elseif ($action === 'count') {
        // Get cart count only
        $cartItems = getCartItems($userId);
        echo json_encode([
            'success' => true,
            'count' => count($cartItems)
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
