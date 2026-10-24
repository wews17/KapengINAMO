-- Coffee Shop Management System Database Schema
-- Database: kapenginamo

CREATE DATABASE IF NOT EXISTS kapenginamo;
USE kapenginamo;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menu Categories Table
CREATE TABLE menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Sales Table (for tracking daily sales)
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    sale_date DATE NOT NULL,
    sale_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Order Feedback Table
CREATE TABLE order_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: Admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('Admin', 'admin@kapenginamo.com', '$2y$10$b/Np2rHl.KTNGx0FA2ogA.G8Jy8A6MjbmaLD9zoTxmhWNWMgEVzw.', 'System Administrator', 'admin');

-- Insert default menu categories
INSERT INTO menu_categories (name, description) VALUES
('Coffee', 'Hot and cold coffee beverages'),
('Tea', 'Various tea selections'),
('Pastries', 'Fresh baked goods'),
('Sandwiches', 'Light meals and sandwiches'),
('Desserts', 'Sweet treats and desserts');

-- Insert sample menu items
INSERT INTO menu_items (category_id, name, description, price, is_available) VALUES
(1, 'Espresso', 'Single shot of rich espresso', 3.50, TRUE),
(1, 'Americano', 'Espresso with hot water', 4.00, TRUE),
(1, 'Cappuccino', 'Espresso with steamed milk foam', 5.00, TRUE),
(1, 'Latte', 'Espresso with steamed milk', 5.50, TRUE),
(1, 'Mocha', 'Chocolate flavored latte', 6.00, TRUE),
(2, 'Green Tea', 'Premium Japanese green tea', 4.00, TRUE),
(2, 'Chai Latte', 'Spiced tea with milk', 5.00, TRUE),
(3, 'Croissant', 'Buttery French pastry', 3.50, TRUE),
(3, 'Muffin', 'Fresh baked muffin', 3.00, TRUE),
(3, 'Danish', 'Fruit filled pastry', 4.00, TRUE),
(4, 'Club Sandwich', 'Classic triple-decker sandwich', 8.00, TRUE),
(4, 'Grilled Cheese', 'Melted cheese on toasted bread', 6.00, TRUE),
(5, 'Chocolate Cake', 'Rich chocolate layer cake', 6.50, TRUE),
(5, 'Cheesecake', 'New York style cheesecake', 7.00, TRUE),
(5, 'Ice Cream', 'Premium ice cream scoop', 4.50, TRUE);
