-- VeggieDelight Database Schema
-- Created for a vegetarian food delivery web application

SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS food_delivery;
USE food_delivery;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS food_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    zipcode VARCHAR(20),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create food_items table (renamed from foods)
CREATE TABLE food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    category VARCHAR(100),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL,
    user_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_food (user_id, food_id)
);

-- Create cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE
);

-- Create user_preferences table
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_notifications BOOLEAN DEFAULT FALSE,
    sms_notifications BOOLEAN DEFAULT FALSE,
    push_notifications BOOLEAN DEFAULT FALSE,
    newsletter BOOLEAN DEFAULT FALSE,
    profile_visible BOOLEAN DEFAULT TRUE,
    order_history BOOLEAN DEFAULT TRUE,
    analytics BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    delivery_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE
);

-- Insert new vegetarian categories
INSERT INTO categories (name, description) VALUES
('Gujarati Thali', 'A complete meal with a variety of traditional Gujarati dishes, served on a single platter.'),
('Curries & Sabzis', 'Aromatic and flavorful vegetable curries and dry preparations.'),
('Breads', 'Traditional Indian breads, perfect for scooping up curries.'),
('Street Food', 'Popular and savory snacks, inspired by the vibrant streets of India.'),
('Sweets', 'Delicious and decadent Indian sweets to complete your meal.'),
('Beverages', 'Refreshing drinks to accompany your food.');

-- Insert new vegetarian food items
INSERT INTO food_items (category_id, title, description, price, image_path, category) VALUES
(1, 'Special Gujarati Thali', 'A grand platter featuring 3 sabzis, dal, kadhi, rotis, rice, farsan, a sweet, and buttermilk. A feast for one!', 15.99, 'gujarati-thali.jpg', 'Gujarati Thali'),
(1, 'Kathiyawadi Thali', 'A spicy and rustic thali with sev tameta, lasaniya bateta, bajra no rotlo, rice, and jaggery.', 16.99, 'kathiyawadi-thali.jpg', 'Gujarati Thali'),
(2, 'Undhiyu', 'A classic Gujarati mixed vegetable dish, slow-cooked to perfection with a blend of spices. A winter specialty.', 12.99, 'undhiyu.jpg', 'Curries & Sabzis'),
(2, 'Paneer Butter Masala', 'Soft paneer cubes cooked in a rich and creamy tomato-based gravy. A crowd favorite.', 11.99, 'paneer-butter-masala.jpg', 'Curries & Sabzis'),
(3, 'Thepla (5 pcs)', 'Soft and flavorful fenugreek flatbread, a staple in Gujarati households. Perfect for any meal.', 5.99, 'thepla.jpg', 'Breads'),
(3, 'Puran Poli', 'Sweet flatbread stuffed with a delicious mixture of chana dal, jaggery, and cardamom.', 6.99, 'puran-poli.jpg', 'Breads'),
(4, 'Pani Puri', 'Crispy hollow puris filled with a spicy and tangy mint-flavored water, potatoes, and chickpeas.', 4.99, 'pani-puri.jpg', 'Street Food'),
(4, 'Dabeli', 'A sweet and spicy potato mixture stuffed in a pav (bun), garnished with pomegranate and roasted peanuts.', 3.99, 'dabeli.jpg', 'Street Food'),
(5, 'Mohanthal', 'A rich and fudgy sweet made from gram flour, ghee, sugar, and nuts.', 7.99, 'mohanthal.jpg', 'Sweets'),
(5, 'Gulab Jamun (2 pcs)', 'Soft, spongy balls made of milk solids, deep-fried and soaked in a light sugar syrup.', 4.49, 'gulab-jamun.jpg', 'Sweets'),
(6, 'Masala Chaas', 'Spiced buttermilk, a refreshing and digestive drink.', 2.99, 'masala-chaas.jpg', 'Beverages'),
(6, 'Mango Lassi', 'A creamy and delicious yogurt-based drink, blended with sweet mango pulp.', 3.99, 'mango-lassi.jpg', 'Beverages');

-- Create admin user (password: admin123)
INSERT INTO users (name, email, password, address, phone) VALUES
('Admin User', 'admin@veggiedelight.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Address', '1234567890');

-- Insert sample reviews for a few dishes
INSERT INTO reviews (food_id, user_id, user_name, rating, comment, created_at) VALUES
(1, 1, 'Admin User', 5, 'This thali is absolutely amazing! It feels like a home-cooked meal. So many varieties and everything was delicious.', NOW() - INTERVAL 1 DAY),
(1, 1, 'Admin User', 4, 'Really good, but a bit too much food for one person. Great value for money though!', NOW()),
(4, 1, 'Admin User', 5, 'The best Paneer Butter Masala I have had in a long time. The gravy was rich and creamy. Highly recommended!', NOW() - INTERVAL 2 DAY);

SET FOREIGN_KEY_CHECKS=1; 