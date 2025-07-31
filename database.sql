-- GreenzoneLk Database Schema
-- Updated with newsletter and contact submissions support

-- Create database
CREATE DATABASE IF NOT EXISTS greenzonelk;
USE greenzonelk;

-- Users table (updated with newsletter column)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    newsletter BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact submissions table
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved') DEFAULT 'new',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_session (user_id, session_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
('Indoor Plants', 'indoor', 'Beautiful houseplants for indoor decoration', '🏠', 1),
('Outdoor Plants', 'outdoor', 'Hardy plants perfect for gardens and patios', '🌳', 2),
('Flowering Plants', 'flowering', 'Colorful flowering plants to brighten any space', '🌸', 3),
('Succulents', 'succulents', 'Low-maintenance succulent plants', '🌵', 4),
('Herbs', 'herbs', 'Fresh herbs for cooking and aromatherapy', '🌿', 5),
('Garden Tools', 'tools', 'Professional tools for gardening', '🛠️', 6);

-- Insert sample products
INSERT INTO products (name, description, category, price, original_price, stock_quantity, is_featured) VALUES
('Monstera Deliciosa', 'Large, beautiful indoor plant with stunning split leaves. Perfect for bright, indirect light.', 'indoor', 2500.00, 3000.00, 25, TRUE),
('Snake Plant', 'Low maintenance, air purifying plant. Thrives in low light conditions.', 'indoor', 1500.00, NULL, 40, TRUE),
('Peace Lily', 'Elegant flowering houseplant with beautiful white blooms.', 'flowering', 2000.00, NULL, 20, FALSE),
('Fiddle Leaf Fig', 'Popular Instagram plant with large, glossy leaves.', 'indoor', 3500.00, 4000.00, 15, TRUE),
('Rubber Plant', 'Glossy, dark green leaves. Easy to care for and perfect for beginners.', 'indoor', 1800.00, NULL, 30, FALSE),
('Aloe Vera', 'Medicinal succulent plant. Great for skin care and easy to maintain.', 'succulents', 800.00, NULL, 50, FALSE),
('Hibiscus', 'Beautiful flowering outdoor plant with vibrant blooms.', 'flowering', 2200.00, NULL, 25, FALSE),
('Basil Plant', 'Fresh basil for your kitchen garden. Perfect for cooking enthusiasts.', 'herbs', 600.00, NULL, 60, FALSE),
('Garden Pruning Shears', 'Professional quality pruning shears for garden maintenance.', 'tools', 1200.00, 1500.00, 35, FALSE),
('Jade Plant', 'Lucky jade plant, symbol of prosperity. Very low maintenance.', 'succulents', 900.00, NULL, 40, FALSE),
('Bird of Paradise', 'Stunning tropical plant with bird-like orange and blue flowers.', 'outdoor', 4500.00, NULL, 10, TRUE),
('Mint Plant', 'Fresh mint for teas, mojitos, and culinary use. Fast growing.', 'herbs', 500.00, NULL, 45, FALSE);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_contact_status ON contact_submissions(status);
CREATE INDEX idx_contact_date ON contact_submissions(submitted_at);

-- Create a view for featured products
CREATE VIEW featured_products AS
SELECT p.*, c.name as category_name, c.icon as category_icon
FROM products p
LEFT JOIN categories c ON p.category = c.slug
WHERE p.is_featured = TRUE AND p.is_active = TRUE;

-- Create a view for product statistics
CREATE VIEW product_stats AS
SELECT 
    p.id,
    p.name,
    p.category,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(r.id) as review_count,
    p.stock_quantity,
    p.price
FROM products p
LEFT JOIN reviews r ON p.id = r.product_id
WHERE p.is_active = TRUE
GROUP BY p.id;