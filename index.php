<?php
require_once 'config.php';

// Get categories for filter
$categories = get_categories();

// Get selected category
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get search query
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Get products based on filters
$products = [];
if ($search) {
    // Search products
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ?)
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $search_term = "%{$search}%";
    $stmt->execute([$search_term, $search_term]);
    $products = $stmt->fetchAll();
} else {
    $products = get_products($selected_category);
}

// Get cart count for logged-in users
$cart_count = 0;
if (is_logged_in()) {
    $cart_count = get_cart_count($_SESSION['user_id']);
}

// Get alert message
$alert = get_alert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Eco-Friendly Plants & Gardening Products</title>
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
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
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

        .search-bar {
            display: flex;
            flex: 1;
            max-width: 400px;
            margin: 0 2rem;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 25px 0 0 25px;
            font-size: 16px;
        }

        .search-bar button {
            background: #90c665;
            border: none;
            padding: 10px 20px;
            border-radius: 0 25px 25px 0;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        .search-bar button:hover {
            background: #7ab84f;
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

        .cart-link {
            position: relative;
            background: #90c665;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none !important;
            color: white !important;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Filters Section */
        .filters {
            background: white;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 600;
            color: #2d5016;
        }

        .category-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid #90c665;
            background: white;
            color: #90c665;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .category-btn:hover, .category-btn.active {
            background: #90c665;
            color: white;
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

        /* Products Section */
        .products-section {
            padding: 2rem 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            color: #2d5016;
            font-weight: 600;
        }

        .product-count {
            color: #666;
            font-size: 1rem;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            height: 200px;
            background: linear-gradient(45deg, #90c665, #7ab84f);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 80px;
            height: 80px;
            filter: brightness(0) invert(1);
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-category {
            color: #90c665;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-name {
            font-size: 1.2rem;
            color: #2d5016;
            margin: 0.5rem 0;
            font-weight: 600;
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d5016;
        }

        .add-to-cart-btn {
            background: #90c665;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-to-cart-btn:hover {
            background: #7ab84f;
            transform: scale(1.05);
        }

        .add-to-cart-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 0;
            color: #666;
        }

        .empty-state img {
            width: 100px;
            height: 100px;
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        /* Footer */
        .footer {
            background: #2d5016;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer p {
            color: #e8f5e8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                text-align: center;
            }

            .search-bar {
                max-width: 100%;
                margin: 1rem 0;
            }

            .filter-container {
                justify-content: center;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .logo h1 {
                font-size: 1.5rem;
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
                <h1>GreenzoneLk</h1>
            </div>
            
            <form class="search-bar" method="GET" action="">
                <?php if ($selected_category): ?>
                    <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            
            <nav class="nav-menu">
                <?php if (is_logged_in()): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                    <a href="cart.php" class="cart-link">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Filters -->
    <section class="filters">
        <div class="container">
            <div class="filter-container">
                <span class="filter-label">Categories:</span>
                <div class="category-filters">
                    <a href="index.php" class="category-btn <?php echo !$selected_category ? 'active' : ''; ?>">
                        All Products
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?php echo $category['id']; ?>" 
                           class="category-btn <?php echo $selected_category == $category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <?php if ($alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?>">
                <?php echo htmlspecialchars($alert['message']); ?>
            </div>
        <?php endif; ?>

        <section class="products-section">
            <div class="section-header">
                <h2 class="section-title">
                    <?php 
                    if ($search) {
                        echo 'Search Results for "' . htmlspecialchars($search) . '"';
                    } elseif ($selected_category) {
                        $category_name = '';
                        foreach ($categories as $cat) {
                            if ($cat['id'] == $selected_category) {
                                $category_name = $cat['name'];
                                break;
                            }
                        }
                        echo htmlspecialchars($category_name);
                    } else {
                        echo 'All Products';
                    }
                    ?>
                </h2>
                <span class="product-count"><?php echo count($products); ?> products found</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <img src="<?php echo get_product_image('img/core-img/pot.png'); ?>" alt="No products">
                    <h3>No products found</h3>
                    <p>Try adjusting your search or browse our categories.</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo get_product_image($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="stock-badge">
                                    <?php echo $product['stock_quantity']; ?> in stock
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="product-footer">
                                    <span class="product-price"><?php echo format_currency($product['price']); ?></span>
                                    <?php if (is_logged_in()): ?>
                                        <form method="POST" action="add_to_cart.php" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <button type="submit" class="add-to-cart-btn" 
                                                    <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                                <?php echo $product['stock_quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="add-to-cart-btn" style="text-decoration: none; display: inline-block;">
                                            Login to Buy
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 GreenzoneLk. All rights reserved. | Eco-friendly plants and gardening solutions for a greener Sri Lanka.</p>
        </div>
    </footer>

    <script>
        // Auto-submit search form on category change
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.href.includes('category=')) {
                    // If there's a search query, preserve it
                    const searchInput = document.querySelector('input[name="search"]');
                    if (searchInput && searchInput.value) {
                        const url = new URL(this.href);
                        url.searchParams.set('search', searchInput.value);
                        this.href = url.toString();
                    }
                }
            });
        });

        // Handle add to cart with AJAX (optional enhancement)
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            if (btn.type === 'submit') {
                btn.addEventListener('click', function(e) {
                    this.textContent = 'Adding...';
                    this.disabled = true;
                });
            }
        });
    </script>
</body>
</html>
