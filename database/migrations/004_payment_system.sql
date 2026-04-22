-- ============================================================
-- Migration 004: Payment System Enhancement
-- Thêm bảng payment_logs và cập nhật orders table
-- ============================================================

USE `cicafood`;

-- Thêm cột payment_status và payment_transaction_id vào orders
ALTER TABLE `orders` 
ADD COLUMN `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER `payment_method`,
ADD COLUMN `payment_transaction_id` VARCHAR(100) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN `payment_date` TIMESTAMP NULL DEFAULT NULL AFTER `payment_transaction_id`;

-- Tạo bảng payment_logs để lưu lịch sử thanh toán
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `payment_method` ENUM('cod', 'vnpay', 'momo', 'zalopay', 'bank') NOT NULL,
  `amount` INT NOT NULL COMMENT 'Số tiền (VND)',
  `status` ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` VARCHAR(100) DEFAULT NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
  `response_code` VARCHAR(20) DEFAULT NULL COMMENT 'Mã phản hồi',
  `request_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu request (JSON)',
  `response_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu response (JSON)',
  `error_message` TEXT DEFAULT NULL COMMENT 'Thông báo lỗi nếu có',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_transaction_id` (`transaction_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cập nhật user_vouchers table nếu chưa có
CREATE TABLE IF NOT EXISTS `user_vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `voucher_id` INT UNSIGNED NOT NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_voucher` (`user_id`, `voucher_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_voucher_id` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột restaurant_id vào vouchers nếu chưa có
ALTER TABLE `vouchers` 
ADD COLUMN IF NOT EXISTS `restaurant_id` INT UNSIGNED DEFAULT NULL AFTER `id`,
ADD FOREIGN KEY IF NOT EXISTS (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE;

-- Thêm cột is_visible vào reviews nếu chưa có
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `is_visible` TINYINT(1) DEFAULT 1 AFTER `comment`;

-- Cập nhật payment_method trong orders để loại bỏ momo
UPDATE `orders` SET `payment_method` = 'cod' WHERE `payment_method` = 'momo';

-- ============================================================
-- Hoàn thành migration
-- ============================================================
