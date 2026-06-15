-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2026 at 11:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grocery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(4, 1, 3, 1, '2026-04-19 13:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`) VALUES
(1, 'Fruits & Vegetables', NULL),
(2, 'Dairy & Eggs', NULL),
(3, 'Snacks', NULL),
(4, 'Beverages', NULL),
(5, 'Rice & Grains', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`, `admin_reply`, `replied_at`) VALUES
(1, 'rajesh', 'rajesh1@gmail.com', 'Product Query', 'proble', 1, '2026-05-27 09:20:39', 'cxmvbxjgbv', '2026-05-27 09:31:47'),
(2, 'rajesh', 'rajesh1@gmail.com', 'Payment Problem', 'jgjhfgh', 1, '2026-05-27 09:31:23', 'cvnxjvhvxn', '2026-05-27 09:31:42'),
(3, 'thilak', 'sumanth@gmail.com', 'Payment Problem', 'cx vxmfvhgdfty', 1, '2026-06-02 09:43:32', 'x,mcfnzxkm', '2026-06-02 09:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text NOT NULL,
  `status` enum('pending','processing','delivered','cancelled') DEFAULT 'pending',
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(20) DEFAULT 'cod',
  `card_last4` varchar(4) DEFAULT NULL,
  `upi_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `delivery_address`, `status`, `ordered_at`, `payment_method`, `card_last4`, `upi_id`) VALUES
(1, 2, 45.00, 'Bangalore', 'delivered', '2026-04-19 12:36:11', 'cod', NULL, NULL),
(2, 7, 30.00, 'bengaluru', 'delivered', '2026-04-19 13:07:35', 'cod', NULL, NULL),
(3, 7, 180.00, 'Bengaluru', 'delivered', '2026-05-27 08:14:15', 'cod', NULL, NULL),
(4, 7, 55.00, 'Bengaluru', 'processing', '2026-05-27 08:17:51', 'cod', NULL, NULL),
(5, 11, 30.00, 'bengaluru', 'delivered', '2026-05-27 09:17:32', 'cod', NULL, NULL),
(6, 11, 30.00, 'Bengaluru', 'cancelled', '2026-05-27 09:46:30', 'cod', NULL, NULL),
(7, 7, 270.00, 'Bengaluru', 'delivered', '2026-05-27 09:50:41', 'cod', NULL, NULL),
(8, 7, 60.00, 'Bengaluru', 'processing', '2026-06-02 09:42:19', 'cod', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT 'kg',
  `unit_value` decimal(10,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `unit`, `unit_value`) VALUES
(1, 1, 2, 1, 45.00, 'kg', 1.00),
(2, 2, 1, 1, 30.00, 'kg', 1.00),
(3, 3, 3, 3, 60.00, 'kg', 1.00),
(4, 4, 6, 1, 55.00, 'kg', 1.00),
(5, 5, 1, 1, 30.00, 'kg', 1.00),
(6, 6, 1, 1, 30.00, 'kg', 1.00),
(7, 7, 2, 2, 45.00, 'kg', 1.00),
(8, 7, 3, 3, 60.00, 'kg', 1.00),
(9, 8, 1, 2, 30.00, 'kg', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','upi','cod') NOT NULL,
  `card_name` varchar(100) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `status` enum('paid','pending') DEFAULT 'pending',
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unit` varchar(20) DEFAULT 'kg',
  `unit_value` decimal(10,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `created_at`, `unit`, `unit_value`) VALUES
(1, 1, 'Fresh Tomatoes', 'Farm fresh red tomatoes', 30.00, 95, '1779875761_tomato.jpg', '2026-04-19 12:24:19', 'kg', 1.00),
(2, 1, 'Banana', 'Fresh yellow bananas per dozen', 45.00, 77, '1779875752_banana.jpg', '2026-04-19 12:24:19', 'kg', 1.00),
(3, 2, 'Amul Milk 1L', 'Full cream fresh milk', 60.00, 44, '1779875745_amul milk 1 li.jpg', '2026-04-19 12:24:19', 'litre', 1.00),
(4, 2, 'Eggs (12 pcs)', 'Farm fresh eggs', 90.00, 60, '1779875736_eggs 12.jpg', '2026-04-19 12:24:19', 'piece', 12.00),
(5, 3, 'Lays Classic', 'Salted potato chips', 20.00, 200, '1779875729_lays classic.jpg', '2026-04-19 12:24:19', 'pack', 1.00),
(6, 4, 'Coca Cola 1L', 'Chilled cold drink', 55.00, 149, '1779875720_coco cola.jpg', '2026-04-19 12:24:19', 'litre', 1.00),
(7, 5, 'Basmati Rice 1kg', 'Premium long grain rice', 120.00, 75, '1779875709_basmati rice.jpg', '2026-04-19 12:24:19', 'kg', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@grocery.com', '0192023a7bbd73250516f069df18b500', NULL, NULL, 'admin', '2026-04-19 12:24:19'),
(2, 'Thanu', 'thanu@gmail.com', 'cf1021da64448549d42b440b986ec4f1', '9876543214', 'Bengaluru', 'customer', '2026-04-19 12:30:14'),
(3, 'Kavya K B', 'kavyak@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '07483943459', 'Bengaluru', 'customer', '2026-04-19 12:55:10'),
(4, 'pooja', 'pooja@gmail.com', '9cbb6aebcf5ae14a9248b4c08165212e', '9876543214', 'Bengaluru', 'customer', '2026-04-19 13:00:46'),
(5, 'pooja1', 'pooja1@gmail.com', '3430508677f2ebaff04d971159ceef1a', '7483943459', 'Bengaluru', 'customer', '2026-04-19 13:03:25'),
(6, 'thilak', 'kavyakb58@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '07483943459', 'Bengaluru', 'customer', '2026-04-19 13:04:49'),
(7, 'Thilak K B', 'subba@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '1234567891', 'Majestic', 'customer', '2026-04-19 13:05:45'),
(8, 'Thanushree', 'thaunshree@gmail.com', '857924e5e0841c5dac934a5c04d73692', '9861358243', 'Bengaluru', 'customer', '2026-05-27 07:56:02'),
(9, 'raju', 'admin1@grocery.com', '1844156d4166d94387f1a4ad031ca5fa', '1234567767', 'Bengaluru', 'customer', '2026-05-27 09:10:48'),
(10, 'rajesh', 'rajesh@gmail.com', '99bd974fae48638b5d62ca32f7645637', '1234566789', 'Bengaluru', 'customer', '2026-05-27 09:14:30'),
(11, 'rajesh', 'rajesh1@gmail.com', '65c499a95bd6a03635631c5eea53e031', '1234566789', 'Bengaluru', 'customer', '2026-05-27 09:15:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
