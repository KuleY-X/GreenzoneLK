<?php
require_once 'config.php';

// Verify user is logged in
require_login();

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'Invalid security token.';
    } else {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if ($product_id <= 0) {
            $response['message'] = 'Invalid product.';
        } elseif ($quantity <= 0 || $quantity > 10) {
            $response['message'] = 'Quantity must be between 1 and 10.';
        } else {
            // Check if product exists and is available
            $product = get_product_by_id($product_id);
            
            if (!$product) {
                $response['message'] = 'Product not found.';
            } elseif ($product['stock_quantity'] < $quantity) {
                $response['message'] = 'Insufficient stock. Only ' . $product['stock_quantity'] . ' items available.';
            } else {
                try {
                    // Check if item already exists in cart
                    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$user_id, $product_id]);
                    $existing_item = $stmt->fetch();
                    
                    if ($existing_item) {
                        // Update existing cart item
                        $new_quantity = $existing_item['quantity'] + $quantity;
                        
                        // Check stock availability for new total quantity
                        if ($new_quantity > $product['stock_quantity']) {
                            $response['message'] = 'Cannot add more items. Total would exceed available stock.';
                        } else {
                            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() 
                                                 WHERE user_id = ? AND product_id = ?");
                            if ($stmt->execute([$new_quantity, $user_id, $product_id])) {
                                $response['success'] = true;
                                $response['message'] = 'Cart updated successfully!';
                            } else {
                                $response['message'] = 'Failed to update cart.';
                            }
                        }
                    } else {
                        // Add new item to cart
                        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        if ($stmt->execute([$user_id, $product_id, $quantity])) {
                            $response['success'] = true;
                            $response['message'] = 'Product added to cart successfully!';
                        } else {
                            $response['message'] = 'Failed to add product to cart.';
                        }
                    }
                    
                    // Get updated cart count
                    if ($response['success']) {
                        $response['cart_count'] = get_cart_count($user_id);
                    }
                    
                } catch (PDOException $e) {
                    $response['message'] = 'Database error occurred.';
                    error_log("Add to cart error: " . $e->getMessage());
                }
            }
        }
    }
}

// Handle different response types
if (isset($_POST['ajax'])) {
    // AJAX request - return JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    // Regular form submission - redirect with message
    if ($response['success']) {
        set_alert('success', $response['message']);
    } else {
        set_alert('error', $response['message']);
    }
    
    // Redirect back to the referring page or index
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit();
}
?>
