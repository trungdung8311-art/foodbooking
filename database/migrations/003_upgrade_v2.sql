-- ============================================================
-- CICAFOOD - Upgrade Migration V2
-- Chạy file này trong phpMyAdmin (tab SQL)
-- Thứ tự quan trọng: chạy từng block từ trên xuống
-- ============================================================

USE `cicafood`;

-- ============================================================
-- 1. Thêm owner_id vào bảng restaurants (liên kết merchant → quán)
-- ============================================================
ALTER TABLE `restaurants`
    ADD COLUMN IF NOT EXISTS `owner_id` INT UNSIGNED DEFAULT NULL AFTER `id`;

-- Update FK nếu column đã được thêm
ALTER TABLE `restaurants`
    ADD CONSTRAINT `fk_restaurant_owner`
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL;

-- ============================================================
-- 2. Thêm cột province vào restaurants (nếu chưa có)
-- ============================================================
ALTER TABLE `restaurants`
    ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh' AFTER `address`;

-- ============================================================
-- 3. Thêm cột province vào users (nếu chưa có)
-- ============================================================
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh' AFTER `address`;

-- ============================================================
-- 4. Thêm restaurant_id vào vouchers (nếu chưa có)
-- ============================================================
ALTER TABLE `vouchers`
    ADD COLUMN IF NOT EXISTS `restaurant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;

-- Thêm FK vouchers → restaurants
ALTER TABLE `vouchers`
    ADD CONSTRAINT `fk_voucher_restaurant`
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`)
    ON DELETE CASCADE;

-- ============================================================
-- 5. Tạo bảng favorites (nếu chưa có)
-- ============================================================
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `restaurant_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_restaurant_idx` (`user_id`, `restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. Tạo bảng user_vouchers (nếu chưa có)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_vouchers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `voucher_id` INT UNSIGNED NOT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_voucher_idx` (`user_id`, `voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. Nâng cấp bảng reviews (thêm reply + is_visible)
-- ============================================================
ALTER TABLE `reviews`
    ADD COLUMN IF NOT EXISTS `is_visible` TINYINT(1) DEFAULT 1 AFTER `created_at`,
    ADD COLUMN IF NOT EXISTS `reply` TEXT DEFAULT NULL AFTER `comment`;

-- Thêm UNIQUE constraint review (1 user 1 order 1 review)
ALTER TABLE `reviews`
    ADD UNIQUE KEY IF NOT EXISTS `unique_review` (`user_id`, `restaurant_id`, `order_id`);

-- ============================================================
-- 8. Thêm role merchant vào bảng users
-- ============================================================
ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('customer', 'merchant', 'admin') NOT NULL DEFAULT 'customer';

-- ============================================================
-- 9. Tạo bảng merchant_applications (đơn đăng ký merchant)
-- ============================================================
CREATE TABLE IF NOT EXISTS `merchant_applications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `restaurant_name` VARCHAR(200) NOT NULL,
    `restaurant_address` TEXT NOT NULL,
    `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh',
    `phone` VARCHAR(20),
    `category_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `note` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. Tạo uploads directory marker
-- ============================================================
-- Thư mục uploads/ sẽ được tạo bởi PHP khi upload ảnh lần đầu

-- ============================================================
-- 11. Cập nhật sample data: Set province cho restaurants
-- ============================================================
UPDATE `restaurants` SET `province` = 'TP. Hồ Chí Minh' WHERE `province` IS NULL OR `province` = '';

-- Set một số quán nổi bật có province
-- (Giúp filter hoạt động ngay với dữ liệu mẫu)
UPDATE `restaurants` SET `is_featured` = 1 WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8);

-- ============================================================
-- 12. Thêm reset_token cho users (dùng cho quên mật khẩu)
-- ============================================================
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(64) DEFAULT NULL AFTER `password_hash`,
    ADD COLUMN IF NOT EXISTS `reset_token_expires` TIMESTAMP NULL DEFAULT NULL AFTER `reset_token`;

-- ============================================================
-- KIỂM TRA KẾT QUẢ
-- ============================================================
-- SELECT * FROM restaurants LIMIT 3;
-- SELECT * FROM users LIMIT 3;
-- DESCRIBE vouchers;
-- DESCRIBE reviews;
