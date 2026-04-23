-- ============================================================
-- Migration: Thêm các cột còn thiếu
-- Ngày: 2026-04-23
-- Mô tả:
--   1. restaurants.province  - Lọc nhà hàng theo tỉnh thành
--   2. vouchers.restaurant_id - Phân biệt voucher toàn platform vs nhà hàng
-- ============================================================

USE `cicafood`;

-- 1. Thêm cột province vào bảng restaurants (nếu chưa có)
ALTER TABLE `restaurants`
    ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) DEFAULT NULL
        COMMENT 'Tỉnh/thành phố (ví dụ: TP. Hồ Chí Minh, Hà Nội)' AFTER `address`;

-- 2. Thêm cột restaurant_id vào bảng vouchers (nếu chưa có)
--    NULL = voucher toàn platform; có giá trị = voucher riêng của nhà hàng đó
ALTER TABLE `vouchers`
    ADD COLUMN IF NOT EXISTS `restaurant_id` INT UNSIGNED DEFAULT NULL
        COMMENT 'NULL = toàn platform; khác NULL = voucher của nhà hàng cụ thể' AFTER `is_active`;

-- 3. Cập nhật province cho dữ liệu mẫu hiện có (8 nhà hàng đều ở TP.HCM)
UPDATE `restaurants` SET `province` = 'TP. Hồ Chí Minh' WHERE `province` IS NULL;

-- 4. Kiểm tra kết quả
SELECT id, name, province FROM `restaurants` ORDER BY id;
SELECT id, code, restaurant_id FROM `vouchers` ORDER BY id;
