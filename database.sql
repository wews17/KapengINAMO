-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 03:52 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kapenginamo`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Coffee', 'Hot and cold coffee beverages', '2026-10-27 01:54:55'),
(2, 'Tea', 'Various tea selections', '2026-10-27 01:54:55'),
(3, 'Pastries', 'Fresh baked goods', '2026-10-27 01:54:55'),
(4, 'Sandwiches', 'Light meals and sandwiches', '2026-10-27 01:54:55'),
(5, 'Desserts', 'Sweet treats and desserts', '2026-10-27 01:54:55');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Espresso', 'Single shot of rich espresso', 50.00, 'uploads/menu/d1ba0eb252e92be936b12c2772cdc811.webp', 1, '2026-10-27 01:54:55', '2026-10-27 02:06:04'),
(2, 1, 'Americano', 'Espresso with hot water', 99.00, 'uploads/menu/df4054b67eb48dcf2d7db7e773edb579.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:04:48'),
(3, 1, 'Cappuccino', 'Espresso with steamed milk foam', 130.00, 'uploads/menu/4613fae88ac1ef219c8da49de6ee0948.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:05:37'),
(4, 1, 'Latte', 'Espresso with steamed milk', 150.00, 'uploads/menu/cc2f420cb495ca5808e9719ed66510b5.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:07:26'),
(5, 1, 'Mocha', 'Chocolate flavored latte', 160.00, 'uploads/menu/255d6f141bc8d25f6c3186ce11825071.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:07:48'),
(6, 2, 'Green Tea', 'Premium Japanese green tea', 140.00, 'uploads/menu/50dd6e20aacf3757c3194db2f81adc5a.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:12:19'),
(7, 2, 'Chai Latte', 'Spiced tea with milk', 130.00, 'uploads/menu/e07918dd4f28f0852465c81283ac315b.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:11:50'),
(8, 3, 'Croissant', 'Buttery French pastry', 99.00, 'uploads/menu/b88afa1d86f5bdf0182d7de451c147e3.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:09:05'),
(9, 3, 'Muffin', 'Fresh baked muffin', 99.00, 'uploads/menu/c51571555e4d31056174ebe52ebc6600.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:10:07'),
(10, 3, 'Danish', 'Fruit filled pastry', 99.00, 'uploads/menu/1ac3b544231e46c0ed8fa9a23c826162.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:09:32'),
(11, 4, 'Club Sandwich', 'Classic triple-decker sandwich', 79.00, 'uploads/menu/91bd17e5c6986a431e86087ff60eb6de.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:10:42'),
(12, 4, 'Grilled Cheese', 'Melted cheese on toasted bread', 70.00, 'uploads/menu/35f9de0fcc597912850847ae1901317f.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:11:00'),
(13, 5, 'Chocolate Cake', 'Rich chocolate layer cake', 99.00, 'uploads/menu/6dbdf9e0d4ace9d510a0f661f50f988a.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:08:30'),
(14, 5, 'Cheesecake', 'New York style cheesecake', 99.00, 'uploads/menu/6225c7a5fbb2425ff4334a7a2cb31e2a.jpg', 1, '2026-10-27 01:54:55', '2026-10-27 02:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `order_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 1, 'order_created', 'Your order #ORD202610275796 has been placed successfully. Status: Pending', 1, '2026-10-27 02:36:41'),
(2, 3, 2, 'order_created', 'Your order #ORD202610276647 has been placed successfully. Status: Pending', 1, '2026-10-27 02:37:21'),
(3, 3, 3, 'order_created', 'Your order #ORD202610273006 has been placed successfully. Status: Pending', 1, '2026-10-27 02:38:00'),
(4, 3, 4, 'order_created', 'Your order #ORD202610270600 has been placed successfully. Status: Pending', 1, '2026-10-27 02:38:40'),
(5, 3, 5, 'order_created', 'Your order #ORD202610275817 has been placed successfully. Status: Pending', 1, '2026-10-27 02:39:19'),
(6, 3, 6, 'order_created', 'Your order #ORD202610270785 has been placed successfully. Status: Pending', 1, '2026-10-27 02:40:15'),
(7, 3, 5, 'order_status', 'Your order #ORD202610275817 has been completed. Thank you!', 1, '2026-10-27 02:44:17'),
(8, 3, 2, 'order_status', 'Your order #ORD202610276647 has been completed. Thank you!', 1, '2026-10-27 02:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'ORD202610275796', 230.00, 'cancelled', 'cash', 'Customer: Customer\nNotes: Pabili netong Kape ng INA MOCHA', '2026-10-27 02:36:41', '2026-10-27 02:43:33'),
(2, 3, 'ORD202610276647', 379.00, 'completed', 'gcash', 'Customer: Customer\nNotes: Sarap mo Bess', '2026-10-27 02:37:21', '2026-10-27 02:44:29'),
(3, 3, 'ORD202610273006', 199.00, 'pending', 'cash', 'Customer: Customer\nNotes: 3 and 1, tatlong lalaki sa isang babae', '2026-10-27 02:38:00', '2026-10-27 02:38:00'),
(4, 3, 'ORD202610270600', 140.00, 'cancelled', 'maya', 'Customer: Customer\nNotes: Sabi masarap daw match, matchahin ko pag mumuka mo king ina mo', '2026-10-27 02:38:40', '2026-10-27 02:43:29'),
(5, 3, 'ORD202610275817', 99.00, 'completed', 'cash', 'Customer: Customer\nNotes: Yung pastries, unang kagat nilabasan ako... ng chocolate', '2026-10-27 02:39:19', '2026-10-27 02:44:17'),
(6, 3, 'ORD202610270785', 630.00, 'pending', 'cash', 'Customer: Customer\nNotes: Ohhh SHIIII i just got CHEESED ON', '2026-10-27 02:40:15', '2026-10-27 02:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `order_feedback`
--

CREATE TABLE `order_feedback` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_feedback`
--

INSERT INTO `order_feedback` (`id`, `order_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 3, 5, 'sarap ng talga ng INAMO', '2026-10-27 02:48:30');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 5, 1, 160.00, 160.00),
(2, 1, 12, 1, 70.00, 70.00),
(3, 2, 3, 1, 130.00, 130.00),
(4, 2, 4, 1, 150.00, 150.00),
(5, 2, 9, 1, 99.00, 99.00),
(6, 3, 1, 2, 50.00, 100.00),
(7, 3, 14, 1, 99.00, 99.00),
(8, 4, 6, 1, 140.00, 140.00),
(9, 5, 13, 1, 99.00, 99.00),
(10, 6, 12, 9, 70.00, 630.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `sale_date` date NOT NULL,
  `sale_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `order_id`, `amount`, `sale_date`, `sale_time`, `created_at`) VALUES
(1, 5, 99.00, '2026-10-27', '10:44:17', '2026-10-27 02:44:17'),
(2, 2, 379.00, '2026-10-27', '10:44:29', '2026-10-27 02:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@kapenginamo.com', '$2y$10$b/Np2rHl.KTNGx0FA2ogA.G8Jy8A6MjbmaLD9zoTxmhWNWMgEVzw.', 'System Administrator', 'admin', NULL, NULL, '2026-10-27 01:54:55', '2026-10-27 01:54:55'),
(2, 'Staff', 'Staff@gmail.com', '$2y$12$mNMkkcy0GkrcgQ2Wu9OuqeXfsaN1FOANc4mBRO0Cm7NJPK/YXfP9a', 'Staff', 'staff', '09876543212', 'Gedli', '2026-10-27 02:03:29', '2026-10-27 02:03:29'),
(3, 'Customer1', 'Customer@gmail.com', '$2y$12$Cr149BXLZ.VvSRlXUlUV.uEQWw.3ADQ/1ybZ4edCIE3FjwRNWYofG', 'Customer', 'customer', '09612345689', 'gedi', '2026-10-27 02:16:59', '2026-10-27 02:16:59'),
(8, 'Staff1', 'staff1@kapenginamo.com', '$2y$10$placeholder_hash_run_generate_sample_passwords_php', 'John Staff', 'staff', '09123456789', '123 Staff Street, Manila', '2026-10-27 02:35:13', '2026-10-27 02:35:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_feedback`
--
ALTER TABLE `order_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_feedback`
--
ALTER TABLE `order_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_feedback`
--
ALTER TABLE `order_feedback`
  ADD CONSTRAINT `order_feedback_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
