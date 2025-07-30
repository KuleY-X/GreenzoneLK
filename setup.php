<?php
// Simple installation/setup script for GreenzoneLk eCommerce
// Run this file once to create the database and tables

require_once 'config.php';

$setup_complete = false;
$errors = [];
$success_messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // Read and execute SQL file
        $sql_file = 'database.sql';
        if (!file_exists($sql_file)) {
            $errors[] = 'database.sql file not found. Please ensure it exists in the same directory.';
        } else {
            $sql_content = file_get_contents($sql_file);
            
            // Split SQL commands
            $sql_commands = array_filter(array_map('trim', explode(';', $sql_content)));
            
            foreach ($sql_commands as $command) {
                if (!empty($command) && !preg_match('/^--/', $command)) {
                    $pdo->exec($command);
                }
            }
            
            $success_messages[] = 'Database and tables created successfully!';
            $success_messages[] = 'Sample data has been inserted.';
            $setup_complete = true;
        }
    } catch (PDOException $e) {
        $errors[] = 'Database setup error: ' . $e->getMessage();
    }
}

// Check if tables already exist
$tables_exist = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tables_exist = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    // Database doesn't exist yet
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - GreenzoneLk eCommerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2d5016 0%, #4a7c2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }

        .setup-header {
            background: linear-gradient(135deg, #90c665 0%, #7ab84f 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .setup-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .setup-content {
            padding: 2rem;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #383;
            border: 1px solid #cfc;
        }

        .setup-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .setup-info h3 {
            color: #2d5016;
            margin-bottom: 1rem;
        }

        .setup-info ul {
            padding-left: 1.5rem;
        }

        .setup-info li {
            margin-bottom: 0.5rem;
            color: #666;
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #90c665 0%, #7ab84f 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(144, 198, 101, 0.3);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .system-check {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-status {
            font-weight: 600;
        }

        .check-ok {
            color: #28a745;
        }

        .check-error {
            color: #dc3545;
        }

        .next-steps {
            background: #e8f5e8;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #90c665;
        }

        .next-steps h3 {
            color: #2d5016;
            margin-bottom: 1rem;
        }

        .next-steps a {
            display: inline-block;
            background: #90c665;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin: 0.5rem 0.5rem 0.5rem 0;
            font-weight: 500;
        }

        .next-steps a:hover {
            background: #7ab84f;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>GreenzoneLk eCommerce Setup</h1>
            <p>Initialize your eco-friendly eCommerce platform</p>
        </div>
        
        <div class="setup-content">
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($success_messages)): ?>
                <?php foreach ($success_messages as $message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($tables_exist && !$setup_complete): ?>
                <div class="alert alert-success">
                    Database tables already exist! Your GreenzoneLk eCommerce platform is ready to use.
                </div>
            <?php endif; ?>

            <div class="system-check">
                <h3>System Requirements Check</h3>
                
                <div class="check-item">
                    <span>PHP Version (>= 7.4)</span>
                    <span class="check-status <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'check-ok' : 'check-error'; ?>">
                        <?php echo PHP_VERSION; ?>
                    </span>
                </div>
                
                <div class="check-item">
                    <span>PDO MySQL Extension</span>
                    <span class="check-status <?php echo extension_loaded('pdo_mysql') ? 'check-ok' : 'check-error'; ?>">
                        <?php echo extension_loaded('pdo_mysql') ? 'Available' : 'Missing'; ?>
                    </span>
                </div>
                
                <div class="check-item">
                    <span>Database Connection</span>
                    <span class="check-status <?php echo isset($pdo) ? 'check-ok' : 'check-error'; ?>">
                        <?php echo isset($pdo) ? 'Connected' : 'Failed'; ?>
                    </span>
                </div>
                
                <div class="check-item">
                    <span>Session Support</span>
                    <span class="check-status check-ok">Available</span>
                </div>
            </div>

            <?php if (!$tables_exist && !$setup_complete): ?>
                <div class="setup-info">
                    <h3>What will be created:</h3>
                    <ul>
                        <li>Database: <strong>greenzone_ecommerce</strong></li>
                        <li>Tables: users, categories, products, cart</li>
                        <li>Sample categories (Indoor Plants, Outdoor Plants, etc.)</li>
                        <li>Sample products with eco-friendly gardening items</li>
                        <li>Proper indexes for optimal performance</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="setup" class="btn" 
                            <?php echo (!extension_loaded('pdo_mysql') || !isset($pdo)) ? 'disabled' : ''; ?>>
                        Initialize Database & Tables
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($setup_complete || $tables_exist): ?>
                <div class="next-steps">
                    <h3>🎉 Setup Complete! Next Steps:</h3>
                    <p>Your GreenzoneLk eCommerce platform is ready. You can now:</p>
                    <br>
                    <a href="register.php">Create Account</a>
                    <a href="login.php">Login</a>
                    <a href="index.php">Browse Products</a>
                </div>

                <div class="setup-info">
                    <h3>Default Features Available:</h3>
                    <ul>
                        <li>✅ User registration and authentication</li>
                        <li>✅ Product browsing with category filters</li>
                        <li>✅ Shopping cart functionality</li>
                        <li>✅ Secure session management</li>
                        <li>✅ CSRF protection</li>
                        <li>✅ Input validation and sanitization</li>
                        <li>✅ Responsive design for all devices</li>
                        <li>✅ Sample eco-friendly products</li>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!extension_loaded('pdo_mysql')): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> PDO MySQL extension is required but not installed. Please install it and restart your web server.
                </div>
            <?php endif; ?>

            <?php if (!isset($pdo)): ?>
                <div class="alert alert-error">
                    <strong>Database Connection Error:</strong> Please check your database configuration in config.php and ensure MySQL is running.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
