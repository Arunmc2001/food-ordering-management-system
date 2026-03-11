-- Create delivery_persons table if it doesn't exist
CREATE TABLE IF NOT EXISTS `delivery_persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('available','busy','offline') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert demo delivery person if not exists
INSERT IGNORE INTO `delivery_persons` (`name`, `email`, `password`, `phone`, `status`) 
VALUES ('John Doe', 'john@example.com', 'password', '1234567890', 'available');

-- Add delivery_id column to users_orders table if it doesn't exist
ALTER TABLE `users_orders` 
ADD COLUMN IF NOT EXISTS `delivery_id` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `delivery_lat` decimal(10,8) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `delivery_lng` decimal(11,8) DEFAULT NULL,
ADD FOREIGN KEY IF NOT EXISTS (`delivery_id`) REFERENCES `delivery_persons`(`id`) ON DELETE SET NULL;

-- Create delivery_locations table if it doesn't exist
CREATE TABLE IF NOT EXISTS `delivery_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `delivered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `delivery_id` (`delivery_id`),
  CONSTRAINT `delivery_locations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `users_orders` (`o_id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_locations_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `delivery_persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create order_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `users_orders` (`o_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 