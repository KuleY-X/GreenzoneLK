<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

// Check if user is logged in
if (!is_logged_in()) {
    $response['message'] = 'You must be logged in to modify cart';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid security token';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_to_cart':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0) {
                $response['message'] = 'Invalid product ID';
                break;
            }
            
            if ($quantity <= 0 || $quantity > 10) {
                $response['message'] = 'Invalid quantity (1-10 allowed)';
                break;
            }
            
            // Check if product exists and has sufficient stock
            $stmt = $pdo->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $response['message'] = 'Product not found';
                break;
            }
            
            if ($quantity > $product['stock_quantity']) {
                $response['message'] = 'Insufficient stock. Only ' . $product['stock_quantity'] . ' items available.';
                break;
            }
            
            // Check if item already exists in cart
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing_item = $stmt->fetch();
            
            if ($existing_item) {
                // Update existing item
                $new_quantity = $existing_item['quantity'] + $quantity;
                if ($new_quantity > $product['stock_quantity']) {
                    $response['message'] = 'Cannot add more items. Stock limit reached.';
                    break;
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
            
            $response['success'] = true;
            $response['message'] = $product['name'] . ' added to cart successfully!';
            break;
            
        case 'update_quantity':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if ($cart_id <= 0) {
                $response['message'] = 'Invalid cart item ID';
                break;
            }
            
            if ($quantity <= 0 || $quantity > 10) {
                $response['message'] = 'Invalid quantity (1-10 allowed)';
                break;
            }
            
            // Check if cart item belongs to current user and get product info
            $stmt = $pdo->prepare("
                SELECT c.*, p.stock_quantity, p.name 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$cart_id, $user_id]);
            $cart_item = $stmt->fetch();
            
            if (!$cart_item) {
                $response['message'] = 'Cart item not found';
                break;
            }
            
            if ($quantity > $cart_item['stock_quantity']) {
                $response['message'] = 'Insufficient stock. Only ' . $cart_item['stock_quantity'] . ' items available.';
                break;
            }
            
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$quantity, $cart_id]);
            
            $response['success'] = true;
            $response['message'] = 'Cart updated successfully!';
            break;
            
        case 'remove_item':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            
            if ($cart_id <= 0) {
                $response['message'] = 'Invalid cart item ID';
                break;
            }
            
            // Remove item from cart (only if it belongs to current user)
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$cart_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Item removed from cart successfully!';
            } else {
                $response['message'] = 'Cart item not found or unauthorized';
            }
            break;
            
        case 'clear_cart':
            // Clear all items from user's cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully!';
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error processing request: ' . $e->getMessage();
    error_log('Cart action error: ' . $e->getMessage());
}

echo json_encode($response);
?>
