-- ============================================================
-- CICAFOOD - Migration Update (Kế hoạch Mới)
-- Chạy file này trong phpMyAdmin hoặc qua MySQL CLI
-- ============================================================

USE `cicafood`;

-- 1. Cập nhật bảng Favorites (Yêu thích)
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `restaurant_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_restaurant_idx` (`user_id`, `restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Cập nhật bảng Vouchers & User_Vouchers (Lưu voucher)
ALTER TABLE `vouchers` ADD COLUMN `restaurant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `vouchers` ADD FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `user_vouchers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `voucher_id` INT UNSIGNED NOT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_voucher_idx` (`user_id`, `voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cập nhật 63 Tỉnh Thành
ALTER TABLE `users` ADD COLUMN `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh' AFTER `address`;
ALTER TABLE `restaurants` ADD COLUMN `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh' AFTER `address`;

-- Bổ sung cho bảng merchant_applications (để biết họ nộp ở đâu)
-- Kiểm tra xem bảng này có tồn tại không trước khi ALTER. Dù sao thì ignore error nếu cần.
ALTER TABLE `merchant_applications` ADD COLUMN `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh' AFTER `restaurant_address`;
