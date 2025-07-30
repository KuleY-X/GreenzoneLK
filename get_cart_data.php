<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'logged_in' => false,
    'cart_items' => [],
    'message' => ''
];

try {
    // Check if user is logged in
    if (is_logged_in()) {
        $response['logged_in'] = true;
        $user_id = $_SESSION['user_id'];
        
        // Get cart items from database
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.quantity,
                p.id as product_id,
                p.name,
                p.description,
                p.price,
                p.image_url,
                (p.price * c.quantity) as total_price
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();
        
        // Format cart items for frontend
        foreach ($cart_items as &$item) {
            $item['image'] = $item['image_url'] ?: 'img/core-img/pot.png';
            $item['price'] = (float)$item['price'];
            $item['quantity'] = (int)$item['quantity'];
        }
        
        $response['cart_items'] = $cart_items;
        $response['success'] = true;
        $response['message'] = 'Cart loaded successfully';
    } else {
        // User not logged in, return demo data or empty cart
        $response['message'] = 'User not logged in';
        $response['success'] = true; // Still success, just not logged in
    }
} catch (Exception $e) {
    $response['message'] = 'Error loading cart: ' . $e->getMessage();
    error_log('Cart loading error: ' . $e->getMessage());
}

echo json_encode($response);
?>
