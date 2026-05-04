-- POS System Database Schema[cite: 1]
CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
                                          id INT AUTO_INCREMENT PRIMARY KEY,
                                          name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- Products table
CREATE TABLE IF NOT EXISTS products (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        category_id INT,
                                        name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    low_stock_alert INT DEFAULT 10,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    );

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         name VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    total_purchases DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Sales / Transactions table
-- Updated: Restricts payment_method to 'cash' as per requirements[cite: 1]
CREATE TABLE IF NOT EXISTS sales (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    user_id INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    change_amount DECIMAL(12,2) DEFAULT 0.00,
    payment_method ENUM('cash') NOT NULL DEFAULT 'cash',
    status ENUM('completed', 'voided', 'refunded') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
    );

-- Sale items table
-- Created to support multiple items per order[cite: 1]
CREATE TABLE IF NOT EXISTS sale_items (
                                          id INT AUTO_INCREMENT PRIMARY KEY,
                                          sale_id INT NOT NULL,
                                          product_id INT NOT NULL,
                                          product_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
    );

-- Seed default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
                                                    ('Admin', 'admin@pos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                                                    ('Cashier', 'cashier@pos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier');

-- Seed categories
INSERT INTO categories (name, description) VALUES
                                               ('Beverages', 'Drinks and juices'),
                                               ('Snacks', 'Chips, cookies, and light snacks'),
                                               ('Food', 'Meals and cooked food'),
                                               ('Personal Care', 'Hygiene and beauty products'),
                                               ('Electronics', 'Gadgets and accessories');

-- Seed products
INSERT INTO products (category_id, name, sku, price, cost, stock, low_stock_alert) VALUES
                                                                                       (1, 'Bottled Water 500ml', 'BEV-001', 15.00, 8.00, 100, 20),
                                                                                       (1, 'Coca-Cola 330ml', 'BEV-002', 35.00, 22.00, 80, 15),
                                                                                       (1, 'Orange Juice 1L', 'BEV-003', 65.00, 45.00, 50, 10),
                                                                                       (2, 'Lays Classic Chips', 'SNK-001', 45.00, 30.00, 60, 10),
                                                                                       (2, 'Oreo Cookies', 'SNK-002', 55.00, 38.00, 40, 10),
                                                                                       (3, 'Fried Rice', 'FOD-001', 85.00, 50.00, 30, 5),
                                                                                       (3, 'Burger', 'FOD-002', 120.00, 75.00, 25, 5),
                                                                                       (4, 'Toothpaste', 'PC-001', 75.00, 50.00, 45, 10),
                                                                                       (4, 'Shampoo 200ml', 'PC-002', 120.00, 80.00, 30, 8),
                                                                                       (5, 'USB Cable', 'ELC-001', 150.00, 90.00, 20, 5);

-- Seed customers
INSERT INTO customers (name, email, phone) VALUES
                                               ('Walk-in Customer', NULL, NULL),
                                               ('Juan Dela Cruz', 'juan@email.com', '09171234567'),
                                               ('Maria Santos', 'maria@email.com', '09281234567');