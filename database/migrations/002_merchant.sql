-- ============================================================
-- CICAFOOD - Migration: Thêm Role Merchant
-- Chạy file này trong phpMyAdmin để nâng cấp hệ thống
-- ============================================================

USE `cicafood`;

-- 1. Cập nhật ENUM role của bảng users (thêm 'merchant')
ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('customer', 'merchant', 'admin') NOT NULL DEFAULT 'customer';

-- 2. Thêm cột owner_id vào bảng restaurants
ALTER TABLE `restaurants`
    ADD COLUMN `owner_id` INT UNSIGNED DEFAULT NULL AFTER `id`,
    ADD FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 3. Cập nhật owner_id cho admin user (id=1) với tất cả restaurant hiện có (demo)
UPDATE `restaurants` SET `owner_id` = 1 WHERE `owner_id` IS NULL LIMIT 2;

-- 4. Tạo bảng merchant_applications (log lịch sử đăng ký)
CREATE TABLE IF NOT EXISTS `merchant_applications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `restaurant_name` VARCHAR(200) NOT NULL,
    `restaurant_address` TEXT NOT NULL,
    `business_type` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('approved') NOT NULL DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra kết quả
-- SELECT id, full_name, email, role FROM users;
-- DESCRIBE restaurants;
