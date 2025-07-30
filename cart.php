<?php
require_once 'config.php';

// Verify user is logged in
require_login();

$user_id = $_SESSION['user_id'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_alert('error', 'Invalid security token.');
    } else {
        $action = $_POST['action'] ?? '';
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        
        if ($action === 'update_quantity') {
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if ($quantity <= 0) {
                set_alert('error', 'Invalid quantity.');
            } elseif ($quantity > 10) {
                set_alert('error', 'Maximum quantity per item is 10.');
            } else {
                // Check if cart item belongs to current user and get product info
                $stmt = $pdo->prepare("SELECT c.*, p.stock_quantity, p.name 
                                     FROM cart c 
                                     JOIN products p ON c.product_id = p.id 
                                     WHERE c.id = ? AND c.user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
                $cart_item = $stmt->fetch();
                
                if (!$cart_item) {
                    set_alert('error', 'Cart item not found.');
                } elseif ($quantity > $cart_item['stock_quantity']) {
                    set_alert('error', 'Insufficient stock. Only ' . $cart_item['stock_quantity'] . ' items available.');
                } else {
                    // Update quantity
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
                    if ($stmt->execute([$quantity, $cart_id])) {
                        set_alert('success', 'Cart updated successfully!');
                    } else {
                        set_alert('error', 'Failed to update cart.');
                    }
                }
            }
        } elseif ($action === 'remove_item') {
            // Remove item from cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$cart_id, $user_id])) {
                set_alert('success', 'Item removed from cart.');
            } else {
                set_alert('error', 'Failed to remove item.');
            }
        } elseif ($action === 'clear_cart') {
            // Clear entire cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            if ($stmt->execute([$user_id])) {
                set_alert('success', 'Cart cleared successfully.');
            } else {
                set_alert('error', 'Failed to clear cart.');
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit();
}

// Get cart items
$cart_items = get_cart_items($user_id);
$cart_total = get_cart_total($user_id);
$cart_count = get_cart_count($user_id);

// Get alert message
$alert = get_alert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2d5016 0%, #4a7c2a 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 40px;
            height: 40px;
        }

        .logo h1 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: #90c665;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
        }

        .page-title {
            font-size: 2.5rem;
            color: #2d5016;
            margin-bottom: 2rem;
            text-align: center;
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cart-info {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .cart-stat {
            text-align: center;
        }

        .cart-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d5016;
        }

        .cart-stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cart-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #90c665;
            color: white;
        }

        .btn-primary:hover {
            background: #7ab84f;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #90c665;
            color: #90c665;
        }

        .btn-outline:hover {
            background: #90c665;
            color: white;
        }

        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 80px 1fr auto auto auto;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #90c665, #7ab84f);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-image img {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .item-details h3 {
            color: #2d5016;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .item-price {
            color: #666;
            font-weight: 600;
        }

        .stock-info {
            font-size: 0.8rem;
            color: #999;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .quantity-btn:hover {
            background: #f8f9fa;
            border-color: #90c665;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }

        .item-subtotal {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d5016;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .empty-cart img {
            width: 120px;
            height: 120px;
            opacity: 0.5;
            margin-bottom: 2rem;
        }

        .empty-cart h2 {
            color: #2d5016;
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 2rem;
        }

        /* Checkout Section */
        .checkout-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .checkout-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .total-label {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d5016;
        }

        .total-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #2d5016;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-summary {
                flex-direction: column;
                text-align: center;
            }

            .cart-item {
                grid-template-columns: 60px 1fr;
                gap: 1rem;
            }

            .quantity-controls,
            .item-subtotal,
            .remove-btn {
                grid-column: 1 / -1;
                justify-self: center;
                margin-top: 1rem;
            }

            .cart-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .page-title {
                font-size: 2rem;
            }

            .cart-item {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="<?php echo get_product_image('img/core-img/leaf.png'); ?>" alt="GreenzoneLk Logo">
                <a href="index.php" style="text-decoration: none;">
                    <h1>GreenzoneLk</h1>
                </a>
            </div>
            
            <nav class="nav-menu">
                <a href="index.php">Continue Shopping</a>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="main-content">
            <h1 class="page-title">Shopping Cart</h1>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo htmlspecialchars($alert['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <img src="<?php echo get_product_image('img/core-img/pot.png'); ?>" alt="Empty Cart">
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any products to your cart yet.</p>
                    <a href="index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="cart-info">
                        <div class="cart-stat">
                            <div class="cart-stat-number"><?php echo count($cart_items); ?></div>
                            <div class="cart-stat-label">Items</div>
                        </div>
                        <div class="cart-stat">
                            <div class="cart-stat-number"><?php echo $cart_count; ?></div>
                            <div class="cart-stat-label">Quantity</div>
                        </div>
                        <div class="cart-stat">
                            <div class="cart-stat-number"><?php echo format_currency($cart_total); ?></div>
                            <div class="cart-stat-label">Total</div>
                        </div>
                    </div>
                    
                    <div class="cart-actions">
                        <form method="POST" action="" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to clear your cart?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="clear_cart">
                            <button type="submit" class="btn btn-danger">Clear Cart</button>
                        </form>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo get_product_image($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="item-price"><?php echo format_currency($item['price']); ?> each</div>
                                <div class="stock-info"><?php echo $item['stock_quantity']; ?> in stock</div>
                            </div>
                            
                            <form method="POST" action="" class="quantity-controls">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="update_quantity">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">-</button>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo min(10, $item['stock_quantity']); ?>" 
                                       class="quantity-input" onchange="this.form.submit()">
                                <button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>
                            </form>
                            
                            <div class="item-subtotal">
                                <?php echo format_currency($item['subtotal']); ?>
                            </div>
                            
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="remove-btn" 
                                        onclick="return confirm('Remove this item from cart?')" title="Remove item">×</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Checkout Section -->
                <div class="checkout-section">
                    <div class="checkout-total">
                        <span class="total-label">Total Amount:</span>
                        <span class="total-amount"><?php echo format_currency($cart_total); ?></span>
                    </div>
                    
                    <div style="text-align: center;">
                        <button class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;" 
                                onclick="alert('Checkout functionality will be implemented in the next phase!')">
                            Proceed to Checkout
                        </button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="index.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function increaseQuantity(btn) {
            const input = btn.previousElementSibling;
            const max = parseInt(input.getAttribute('max'));
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
                input.form.submit();
            }
        }

        function decreaseQuantity(btn) {
            const input = btn.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                input.form.submit();
            }
        }

        // Auto-submit form when quantity changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > parseInt(this.getAttribute('max'))) {
                    this.value = this.getAttribute('max');
                }
            });
        });
    </script>
</body>
</html>
