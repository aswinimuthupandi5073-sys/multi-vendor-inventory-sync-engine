-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 06:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `multi_vendor_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `channel_inventory`
--

CREATE TABLE `channel_inventory` (
  `id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `last_synced_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `channel_inventory`
--

INSERT INTO `channel_inventory` (`id`, `channel_id`, `product_id`, `quantity`, `last_synced_at`) VALUES
(1, 1, 1, 2, '2026-09-28 03:42:05'),
(2, 1, 2, 30, '2026-09-28 03:40:42'),
(3, 1, 3, 75, '2026-09-28 03:40:43'),
(4, 1, 4, 20, '2026-09-28 03:40:44'),
(5, 2, 1, 48, '2026-09-28 03:40:44'),
(6, 2, 2, 30, '2026-09-28 03:40:44'),
(7, 2, 3, 75, '2026-09-28 03:40:45'),
(8, 2, 4, 20, '2026-09-28 03:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `warehouse_id`, `quantity`, `updated_at`) VALUES
(1, 1, 1, 48, '2026-09-28 03:34:58'),
(2, 2, 1, 30, '2026-09-28 02:42:19'),
(3, 3, 1, 75, '2026-09-28 02:42:19'),
(4, 4, 3, 20, '2026-09-28 03:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_id`, `warehouse_id`, `type`, `quantity`, `reference`, `created_at`) VALUES
(1, 1, 1, 'adjustment', 0, 'Manual inventory update', '2026-09-28 03:28:47'),
(2, 4, 3, 'stock_in', 20, 'Initial stock', '2026-09-28 03:29:29'),
(3, 1, 1, 'stock_out', 2, 'ORD-20260928053458', '2026-09-28 03:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `customer_name` varchar(150) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `vendor_id`, `product_id`, `quantity`, `customer_name`, `total_amount`, `status`, `created_at`) VALUES
(1, 'ORD-20260928053458', 1, 1, 2, 'Test customer', 2998.00, 'confirmed', '2026-09-28 03:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `vendor_id`, `product_name`, `sku`, `price`, `status`, `created_at`) VALUES
(1, 1, 'Wireless Headphones', 'WH-1001', 1499.00, 'active', '2026-09-28 02:42:19'),
(2, 1, 'Smart Watch', 'SW-2001', 2499.00, 'active', '2026-09-28 02:42:19'),
(3, 1, 'Bluetooth Speaker', 'BS-3001', 999.00, 'active', '2026-09-28 02:42:19'),
(4, 1, 'Gaming Mouse', 'GM-4001', 1299.00, 'active', '2026-09-28 03:20:56');

-- --------------------------------------------------------

--
-- Table structure for table `sync_channels`
--

CREATE TABLE `sync_channels` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `channel_name` varchar(150) NOT NULL,
  `channel_type` varchar(100) DEFAULT 'E-Commerce',
  `status` enum('connected','disconnected') DEFAULT 'connected',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sync_channels`
--

INSERT INTO `sync_channels` (`id`, `vendor_id`, `channel_name`, `channel_type`, `status`, `created_at`) VALUES
(1, 1, 'Demo E-Commerce Store', 'Online Store', 'connected', '2026-09-28 02:42:19'),
(2, 1, 'Vendor Marketplace', 'Marketplace', 'connected', '2026-09-28 02:42:19');

-- --------------------------------------------------------

--
-- Table structure for table `sync_logs`
--

CREATE TABLE `sync_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `old_quantity` int(11) DEFAULT 0,
  `new_quantity` int(11) DEFAULT 0,
  `status` enum('success','failed','mismatch') DEFAULT 'success',
  `message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sync_logs`
--

INSERT INTO `sync_logs` (`id`, `product_id`, `channel_id`, `old_quantity`, `new_quantity`, `status`, `message`, `created_at`) VALUES
(1, 1, 1, 0, 48, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:42'),
(2, 2, 1, 0, 30, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:42'),
(3, 3, 1, 0, 75, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:43'),
(4, 4, 1, 0, 20, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:44'),
(5, 1, 2, 0, 48, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:44'),
(6, 2, 2, 0, 30, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:44'),
(7, 3, 2, 0, 75, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:45'),
(8, 4, 2, 0, 20, 'success', 'Stock synchronized successfully', '2026-09-28 03:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','vendor') NOT NULL DEFAULT 'vendor',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'System Administrator', 'admin@example.com', 'admin123', 'admin', 'active', '2026-09-28 02:42:15'),
(2, 'Demo Vendor', 'vendor@example.com', 'vendor123', 'vendor', 'active', '2026-09-28 02:42:15'),
(3, 'ABC store', 'abc@gmail.com', 'vendor123', 'vendor', 'active', '2026-09-28 03:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vendor_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `user_id`, `vendor_name`, `email`, `phone`, `status`, `created_at`) VALUES
(1, 2, 'Demo Vendor Store', 'vendor@example.com', '9876543210', 'active', '2026-09-28 02:42:19'),
(2, 3, 'ABC store', 'abc@gmail.com', '9876543210', 'active', '2026-09-28 03:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `warehouse_name` varchar(150) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `warehouse_name`, `location`, `status`, `created_at`) VALUES
(1, 'Main Warehouse', 'Coimbatore', 'active', '2026-09-28 02:42:19'),
(2, 'Secondary Warehouse', 'Chennai', 'active', '2026-09-28 02:42:19'),
(3, 'Bangalore warehouse', 'Bangalore', 'active', '2026-09-28 03:24:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `channel_inventory`
--
ALTER TABLE `channel_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `channel_id` (`channel_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`warehouse_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `fk_orders_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `sync_channels`
--
ALTER TABLE `sync_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `channel_id` (`channel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `channel_inventory`
--
ALTER TABLE `channel_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sync_channels`
--
ALTER TABLE `sync_channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sync_logs`
--
ALTER TABLE `sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `channel_inventory`
--
ALTER TABLE `channel_inventory`
  ADD CONSTRAINT `channel_inventory_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `sync_channels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `channel_inventory_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sync_channels`
--
ALTER TABLE `sync_channels`
  ADD CONSTRAINT `sync_channels_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD CONSTRAINT `sync_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sync_logs_ibfk_2` FOREIGN KEY (`channel_id`) REFERENCES `sync_channels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
