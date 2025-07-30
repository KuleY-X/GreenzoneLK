-- GreenzoneLk eCommerce Database Structure
-- Run this SQL script to create the database and tables

CREATE DATABASE IF NOT EXISTS greenzone_ecommerce;
USE greenzone_ecommerce;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    newsletter TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Insert sample categories
INSERT INTO categories (name, description, image) VALUES
('Indoor Plants', 'Beautiful plants perfect for indoor spaces', 'img/categories/indoor.jpg'),
('Outdoor Plants', 'Hardy plants for gardens and outdoor areas', 'img/categories/outdoor.jpg'),
('Fertilizers', 'Organic and eco-friendly fertilizers', 'img/categories/fertilizers.jpg'),
('Pots & Containers', 'Eco-friendly pots and plant containers', 'img/categories/pots.jpg'),
('Gardening Tools', 'Essential tools for gardening enthusiasts', 'img/categories/tools.jpg');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, image, stock_quantity) VALUES
('Aloe Vera Plant', 'Natural healing properties and air purification. Perfect for indoor spaces.', 1500.00, 1, 'img/products/aloe-vera.jpg', 25),
('Snake Plant', 'Low maintenance indoor plant that purifies air effectively.', 1200.00, 1, 'img/products/snake-plant.jpg', 30),
('Monstera Deliciosa', 'Popular indoor plant with unique split leaves.', 2500.00, 1, 'img/products/monstera.jpg', 15),
('Hibiscus Plant', 'Beautiful flowering plant perfect for gardens.', 800.00, 2, 'img/products/hibiscus.jpg', 20),
('Rose Plant', 'Classic garden rose with fragrant blooms.', 1000.00, 2, 'img/products/rose.jpg', 18),
('Organic Compost', '100% natural compost for healthy plant growth.', 850.00, 3, 'img/products/compost.jpg', 50),
('Liquid Fertilizer', 'Organic liquid fertilizer for all plant types.', 650.00, 3, 'img/products/liquid-fertilizer.jpg', 40),
('Ceramic Pot Medium', 'Beautiful ceramic pot for medium-sized plants.', 750.00, 4, 'img/products/ceramic-pot.jpg', 35),
('Biodegradable Pot Set', 'Set of 5 eco-friendly biodegradable pots.', 450.00, 4, 'img/products/bio-pots.jpg', 60),
('Garden Trowel', 'High-quality stainless steel garden trowel.', 350.00, 5, 'img/products/trowel.jpg', 25),
('Watering Can', 'Durable plastic watering can with long spout.', 600.00, 5, 'img/products/watering-can.jpg', 20);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_categories_status ON categories(status);
