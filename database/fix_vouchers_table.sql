-- ============================================================
-- Fix Vouchers & User Vouchers Tables
-- Chạy file này nếu trang vouchers bị lỗi 500
-- ============================================================

USE `cicafood`;

-- Tạo bảng user_vouchers nếu chưa có
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

-- Kiểm tra và thêm cột restaurant_id vào vouchers
SET @dbname = DATABASE();
SET @tablename = 'vouchers';
SET @columnname = 'restaurant_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED DEFAULT NULL AFTER id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Thêm một số voucher demo để test
INSERT IGNORE INTO `vouchers` (`code`, `name`, `description`, `type`, `value`, `max_discount`, `min_order`, `start_date`, `end_date`, `usage_limit`, `is_active`, `restaurant_id`) VALUES
('CICA20', 'Giảm 20% đơn hàng', 'Giảm 20% tối đa 50K cho đơn từ 100K', 'percent', 20, 50000, 100000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 1, NULL),
('FREESHIP', 'Miễn phí vận chuyển', 'Miễn phí ship cho đơn từ 0đ', 'freeship', 30000, 30000, 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 1, NULL),
('SAVE50K', 'Giảm 50K', 'Giảm 50K cho đơn từ 200K', 'fixed', 50000, 50000, 200000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 50, 1, NULL);

-- Thêm voucher vào ví user (user_id = 1 để test)
-- Thay đổi user_id nếu cần
INSERT IGNORE INTO `user_vouchers` (`user_id`, `voucher_id`, `is_used`) 
SELECT 1, id, 0 FROM `vouchers` WHERE `code` IN ('CICA20', 'FREESHIP', 'SAVE50K');

-- Kiểm tra kết quả
SELECT 'Vouchers table:' AS info;
SELECT * FROM `vouchers` LIMIT 5;

SELECT 'User vouchers table:' AS info;
SELECT * FROM `user_vouchers` LIMIT 5;

-- ============================================================
-- Hoàn thành
-- ============================================================
