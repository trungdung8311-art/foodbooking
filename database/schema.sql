-- ============================================================
-- CICAFOOD DATABASE - ShopeeFood Clone
-- Tác giả: Bùi Trung Dũng
-- Ngày: 2026
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tạo database nếu chưa có
CREATE DATABASE IF NOT EXISTS `cicafood` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cicafood`;

-- ============================================================
-- TABLE: users
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default_avatar.png',
  `address` TEXT DEFAULT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories (Danh mục loại hình nhà hàng)
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(100) DEFAULT 'fa-utensils',
  `color` VARCHAR(20) DEFAULT '#ee2624',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: restaurants
-- ============================================================
DROP TABLE IF EXISTS `restaurants`;
CREATE TABLE `restaurants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT UNSIGNED DEFAULT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `description` TEXT,
  `address` TEXT NOT NULL,
  `province` VARCHAR(100) DEFAULT NULL COMMENT 'Tỉnh/thành phố (63 tỉnh)',
  `phone` VARCHAR(20),
  `image` VARCHAR(255) DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(2,1) DEFAULT 4.5,
  `total_reviews` INT DEFAULT 0,
  `min_order` INT DEFAULT 0 COMMENT 'Đơn tối thiểu (VND)',
  `delivery_fee` INT DEFAULT 15000 COMMENT 'Phí giao hàng (VND)',
  `delivery_time` INT DEFAULT 30 COMMENT 'Thời gian giao dự kiến (phút)',
  `distance` DECIMAL(5,2) DEFAULT 1.5 COMMENT 'Khoảng cách (km)',
  `is_open` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `has_freeship` TINYINT(1) DEFAULT 0,
  `has_deal` TINYINT(1) DEFAULT 0,
  `open_time` TIME DEFAULT '07:00:00',
  `close_time` TIME DEFAULT '22:00:00',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_restaurant_owner` FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: menu_categories (Nhóm menu trong nhà hàng: Combo, Món chính...)
-- ============================================================
DROP TABLE IF EXISTS `menu_categories`;
CREATE TABLE `menu_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: menu_items (Món ăn)
-- ============================================================
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `menu_category_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `price` INT NOT NULL COMMENT 'Giá gốc (VND)',
  `original_price` INT DEFAULT NULL COMMENT 'Giá gốc trước giảm',
  `image` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `is_best_seller` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_category_id`) REFERENCES `menu_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: orders
-- ============================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_phone` VARCHAR(20) NOT NULL,
  `note` TEXT DEFAULT NULL,
  `subtotal` INT NOT NULL DEFAULT 0 COMMENT 'Tổng tiền món ăn',
  `delivery_fee` INT NOT NULL DEFAULT 0 COMMENT 'Phí giao hàng',
  `service_fee` INT NOT NULL DEFAULT 0 COMMENT 'Phí dịch vụ',
  `discount_amount` INT NOT NULL DEFAULT 0 COMMENT 'Giảm giá từ voucher',
  `shipping_discount` INT NOT NULL DEFAULT 0 COMMENT 'Giảm phí ship từ voucher',
  `total_amount` INT NOT NULL DEFAULT 0 COMMENT 'Tổng thanh toán cuối cùng',
  `voucher_code` VARCHAR(50) DEFAULT NULL,
  `voucher_ship_code` VARCHAR(50) DEFAULT NULL,
  `payment_method` ENUM('cod', 'vnpay', 'momo', 'zalopay', 'bank') DEFAULT 'cod',
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `payment_transaction_id` VARCHAR(100) DEFAULT NULL,
  `payment_date` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'preparing', 'delivering', 'completed', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payment_logs (Lịch sử thanh toán)
-- ============================================================
DROP TABLE IF EXISTS `payment_logs`;
CREATE TABLE `payment_logs` (
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

-- ============================================================
-- TABLE: order_items (Chi tiết đơn hàng)
-- ============================================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `menu_item_id` INT UNSIGNED NOT NULL,
  `item_name` VARCHAR(200) NOT NULL,
  `item_price` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: vouchers
-- ============================================================
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `type` ENUM('percent', 'fixed', 'freeship') NOT NULL DEFAULT 'percent',
  `value` INT NOT NULL DEFAULT 0 COMMENT 'Giá trị: % hoặc VND',
  `max_discount` INT DEFAULT NULL COMMENT 'Giảm tối đa (VND)',
  `min_order` INT DEFAULT 0 COMMENT 'Đơn tối thiểu',
  `usage_limit` INT DEFAULT 100,
  `used_count` INT DEFAULT 0,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `restaurant_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL=toàn platform; có giá trị=voucher riêng nhà hàng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reviews
-- ============================================================
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED DEFAULT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: user_vouchers (Ví voucher của từng user)
-- ============================================================
DROP TABLE IF EXISTS `user_vouchers`;
CREATE TABLE `user_vouchers` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `voucher_id` INT UNSIGNED NOT NULL,
  `is_used`    TINYINT(1) DEFAULT 0,
  `used_at`    TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_voucher` (`user_id`, `voucher_id`),
  INDEX `idx_user_id`    (`user_id`),
  INDEX `idx_voucher_id` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: favorites (Nhà hàng yêu thích)
-- ============================================================
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT UNSIGNED NOT NULL,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)       REFERENCES `users`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_favorite` (`user_id`, `restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: merchant_applications (Đơn đăng ký đối tác)
-- ============================================================
DROP TABLE IF EXISTS `merchant_applications`;
CREATE TABLE `merchant_applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `restaurant_name` VARCHAR(200) NOT NULL,
  `restaurant_address` TEXT NOT NULL,
  `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh',
  `business_type` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20),
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- ============================================================
-- USERS TEST - Mật khẩu: Password123
-- ============================================================
-- Hash bcrypt đúng của "Password123" (tạo bằng password_hash('Password123', PASSWORD_BCRYPT))
-- QUAN TRỌNG: Hash cũ ($2y$10$92IX...) là hash của chuỗi "password", KHÔNG PHẢI "Password123"
--
-- 2 TÀI KHOẢN TEST:
-- 1. Admin (Merchant): admin@cicafood.vn / Password123
-- 2. Customer:         an@gmail.com     / Password123
-- ============================================================
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `address`, `role`) VALUES
('Bùi Trung Dũng', 'admin@cicafood.vn', '0901234567', '$2y$10$OmCVxtpfQHc2ja1yo5ZMzek.7xzl9OzHieKSUUEsPGOHymdTyHH5a', '123 Nguyễn Huệ, Q1, TP.HCM', 'admin'),
('Nguyễn Văn An', 'an@gmail.com', '0912345678', '$2y$10$YNO25cjz6hvI/HF7bsjeyeQNQkp1QiIWEwP3S4LKQ3a1gKpbsFyjW', '456 Lê Lợi, Q1, TP.HCM', 'customer');

-- Categories
INSERT INTO `categories` (`name`, `slug`, `icon`, `color`, `sort_order`) VALUES
('Cơm', 'com', 'fa-bowl-rice', '#f97316', 1),
('Phở & Bún', 'pho-bun', 'fa-fire-flame-curved', '#ee2624', 2),
('Burger', 'burger', 'fa-burger', '#eab308', 3),
('Pizza', 'pizza', 'fa-pizza-slice', '#f97316', 4),
('Sushi', 'sushi', 'fa-fish', '#06b6d4', 5),
('Trà Sữa', 'tra-sua', 'fa-mug-hot', '#8b5cf6', 6),
('Bánh Mì', 'banh-mi', 'fa-bread-slice', '#d97706', 7),
('Lẩu', 'lau', 'fa-pot-food', '#ef4444', 8);

-- Restaurants
INSERT INTO `restaurants` (`category_id`, `name`, `slug`, `description`, `address`, `province`, `phone`, `image`, `cover_image`, `rating`, `total_reviews`, `min_order`, `delivery_fee`, `delivery_time`, `distance`, `is_open`, `is_featured`, `has_freeship`, `has_deal`) VALUES
(2, 'Phở Gánh Hà Nội', 'pho-ganh-ha-noi', 'Phở bò truyền thống Hà Nội với nước dùng hầm 12 tiếng, thịt mềm tan trong miệng.', '12 Võ Văn Tần, Q3, TP.HCM', 'TP. Hồ Chí Minh', '0901111222', 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80', 4.9, 1250, 50000, 0, 15, 0.8, 1, 1, 1, 0),
(1, 'Cơm Tấm Ba Ghiền', 'com-tam-ba-ghien', 'Cơm tấm sườn bì chả chuẩn vị Sài Gòn, thịt nướng thơm lừng, bì dai giòn.', '45 Đinh Tiên Hoàng, Q Bình Thạnh, TP.HCM', 'TP. Hồ Chí Minh', '0902222333', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80', 4.8, 980, 40000, 12000, 20, 1.2, 1, 1, 0, 1),
(3, 'Burger Bò Wagyu House', 'burger-bo-wagyu', 'Burger bò Wagyu nhập khẩu, pho mát chảy, bánh mì thủ công nướng lò. Premium taste!', '78 Nguyễn Đình Chiểu, Q3, TP.HCM', 'TP. Hồ Chí Minh', '0903333444', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1550317138-10000687a72b?auto=format&fit=crop&w=1200&q=80', 4.6, 760, 80000, 15000, 25, 2.1, 1, 0, 0, 1),
(5, 'Sushi Hokkaido Authentic', 'sushi-hokkaido', 'Sushi tươi sống nhập khẩu từ Nhật, cá hồi, cá ngừ béo ngậy. Trải nghiệm ẩm thực Nhật đích thực.', '156 Hai Bà Trưng, Q1, TP.HCM', 'TP. Hồ Chí Minh', '0904444555', 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1617196034183-421b4040ed20?auto=format&fit=crop&w=1200&q=80', 4.9, 1680, 100000, 20000, 30, 3.5, 1, 1, 1, 0),
(6, 'Phúc Long Tea & Coffee', 'phuc-long-tea-coffee', 'Trà sữa, trà đào, cà phê và các thức uống đặc sắc từ thương hiệu trà nổi tiếng Việt Nam.', '23 Lê Văn Sỹ, Q Phú Nhuận, TP.HCM', 'TP. Hồ Chí Minh', '0905555666', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1453614512568-c4024d13c247?auto=format&fit=crop&w=1200&q=80', 4.7, 2100, 30000, 10000, 18, 1.5, 1, 0, 1, 1),
(4, 'Pizza Napoli Express', 'pizza-napoli', 'Pizza kiểu Napoli truyền thống, lò củi 900°C, đế mỏng giòn, topping phong phú.', '67 Cách Mạng Tháng 8, Q10, TP.HCM', 'TP. Hồ Chí Minh', '0906666777', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?auto=format&fit=crop&w=1200&q=80', 4.5, 520, 60000, 15000, 35, 4.2, 1, 0, 0, 0),
(2, 'Bún Chả Hà Nội Cô Lan', 'bun-cha-ha-noi', 'Bún chả đặc sản Hà Nội, chả nướng than hoa thơm lừng, nước chấm chua ngọt đậm đà.', '34 Phan Xích Long, Q Phú Nhuận, TP.HCM', 'TP. Hồ Chí Minh', '0907777888', 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=1200&q=80', 4.7, 890, 45000, 12000, 22, 1.8, 1, 1, 0, 1),
(8, 'Lẩu Thái KungFu', 'lau-thai-kungfu', 'Lẩu Thái chua cay nồng nàn, hải sản tươi sống, rau đủ loại. Phục vụ từ 2 người trở lên.', '89 Võ Thị Sáu, Q3, TP.HCM', 'TP. Hồ Chí Minh', '0908888999', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80', 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?auto=format&fit=crop&w=1200&q=80', 4.8, 1450, 150000, 0, 40, 2.7, 1, 0, 1, 0);

-- Menu Categories cho từng nhà hàng
INSERT INTO `menu_categories` (`restaurant_id`, `name`, `sort_order`) VALUES
-- Phở Gánh (id=1)
(1, 'Combo Tiết Kiệm', 1), (1, 'Phở Bò', 2), (1, 'Phở Gà', 3), (1, 'Thức Uống', 4),
-- Cơm Tấm (id=2)
(2, 'Combo Đầy Đủ', 1), (2, 'Cơm Tấm', 2), (2, 'Món Phụ', 3), (2, 'Nước Uống', 4),
-- Burger (id=3)
(3, 'Combo Burger', 1), (3, 'Single Burger', 2), (3, 'Phần Phụ', 3), (3, 'Thức Uống', 4),
-- Sushi (id=4)
(4, 'Set Sushi', 1), (4, 'Sashimi', 2), (4, 'Rolls', 3), (4, 'Thức Uống Nhật', 4),
-- Phúc Long (id=5)
(5, 'Trà Sữa', 1), (5, 'Trà Trái Cây', 2), (5, 'Cà Phê', 3), (5, 'Bánh Ngọt', 4),
-- Pizza (id=6)
(6, 'Pizza Set', 1), (6, 'Pizza Đơn', 2), (6, 'Mì Ý & Khai Vị', 3),
-- Bún Chả (id=7)
(7, 'Combo', 1), (7, 'Bún Chả', 2), (7, 'Nem Rán', 3),
-- Lẩu Thái (id=8)
(8, 'Lẩu Set', 1), (8, 'Hải Sản Thêm', 2), (8, 'Rau & Đồ Ăn Kèm', 3);

-- Menu Items - Nhà hàng 1: Phở Gánh
INSERT INTO `menu_items` (`restaurant_id`, `menu_category_id`, `name`, `description`, `price`, `original_price`, `image`, `is_best_seller`) VALUES
(1, 1, 'Combo Phở Bò Đặc Biệt + Nước', 'Phở bò tái chín đặc biệt + 1 Trà đá', 75000, 90000, 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=400&q=80', 1),
(1, 1, 'Combo 2 Tô Phở Gà', '2 tô phở gà + 2 trà đá', 130000, 150000, 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?auto=format&fit=crop&w=400&q=80', 0),
(1, 2, 'Phở Bò Tái Chín Đặc Biệt', 'Tô lớn đầy ắp: tái, chín, gầu, nạm, gân', 75000, NULL, 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=400&q=80', 1),
(1, 2, 'Phở Bò Tái', 'Thịt tái mỏng hồng, mềm mượt', 65000, NULL, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=400&q=80', 0),
(1, 2, 'Phở Bò Viên', 'Bò viên dai giòn sần sật', 60000, NULL, 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=400&q=80', 0),
(1, 3, 'Phở Gà Ta Vàng', 'Gà ta nấu nước dùng trong vắt thơm ngon', 65000, NULL, 'https://images.unsplash.com/photo-1604152135912-04a022e23696?auto=format&fit=crop&w=400&q=80', 0),
(1, 4, 'Trà Đá', 'Trà xanh pha lạnh giải khát', 10000, NULL, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80', 0),
(1, 4, 'Nước Chanh', 'Chanh tươi vắt pha đường', 20000, NULL, 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?auto=format&fit=crop&w=400&q=80', 0);

-- Menu Items - Nhà hàng 2: Cơm Tấm Ba Ghiền
INSERT INTO `menu_items` (`restaurant_id`, `menu_category_id`, `name`, `description`, `price`, `original_price`, `image`, `is_best_seller`) VALUES
(2, 5, 'Combo Cơm Tấm Sườn Bì Chả', 'Cơm + Sườn nướng + Bì + Chả Trứng + Súp', 68000, 80000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80', 1),
(2, 5, 'Combo Đôi Vị', '2 tô cơm tấm đặc biệt', 130000, 150000, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80', 0),
(2, 6, 'Cơm Tấm Sườn Bì Chả', 'Sườn cốt lết nướng lửa than, bì da heo sợi giòn', 65000, NULL, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80', 1),
(2, 6, 'Cơm Tấm Chỉ Sườn', 'Sườn nướng to bản, đậm vị kho lạc', 55000, NULL, 'https://images.unsplash.com/photo-1621252179027-94459d278660?auto=format&fit=crop&w=400&q=80', 0),
(2, 6, 'Cơm Tấm Bì Chả', 'Bì da và chả trứng', 50000, NULL, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=400&q=80', 0),
(2, 7, 'Chả Trứng Hấp', 'Chả hấp thơm ngon', 25000, NULL, 'https://images.unsplash.com/photo-1599974579688-8dbdd335c77f?auto=format&fit=crop&w=400&q=80', 0),
(2, 8, 'Trà Đá Quán', 'Trà xanh mát lạnh', 10000, NULL, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80', 0);

-- Menu Items - Nhà hàng 3: Burger
INSERT INTO `menu_items` (`restaurant_id`, `menu_category_id`, `name`, `description`, `price`, `original_price`, `image`, `is_best_seller`) VALUES
(3, 9, 'Combo Wagyu Premium', 'Burger bò Wagyu + Khoai chiên + Nước ngọt', 180000, 210000, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80', 1),
(3, 9, 'Combo Gà Giòn', 'Burger gà crispy + Khoai chiên + Pepsi', 120000, 140000, 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?auto=format&fit=crop&w=400&q=80', 0),
(3, 10, 'Wagyu Smash Burger', 'Thịt bò Wagyu dày 200g, pho mát Cheddar chảy', 165000, NULL, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80', 1),
(3, 10, 'Gà Crispy Spicy', 'Ức gà giòn sốt cay Hàn Quốc', 105000, NULL, 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?auto=format&fit=crop&w=400&q=80', 0),
(3, 11, 'Khoai Tây Chiên', 'Khoai tây vàng giòn, muối hạt', 35000, NULL, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=400&q=80', 0),
(3, 12, 'Pepsi Lon', 'Pepsi lạnh 330ml', 25000, NULL, 'https://images.unsplash.com/photo-1527960471264-932f39eb5846?auto=format&fit=crop&w=400&q=80', 0);

-- Menu Items - Nhà hàng 5: Phúc Long
INSERT INTO `menu_items` (`restaurant_id`, `menu_category_id`, `name`, `description`, `price`, `original_price`, `image`, `is_best_seller`) VALUES
(5, 17, 'Trà Sữa Oolong Nướng', 'Trà Oolong nướng thơm quyến rũ với trân châu đen', 55000, NULL, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=400&q=80', 1),
(5, 17, 'Trà Sữa Matcha Hồng Trà', 'Matcha Nhật Bản kết hợp hồng trà thượng hạng', 60000, NULL, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80', 1),
(5, 17, 'Trà Sữa Phúc Long Classic', 'Hồng trà sữa truyền thống Phúc Long', 50000, NULL, 'https://images.unsplash.com/photo-1527960471264-932f39eb5846?auto=format&fit=crop&w=400&q=80', 0),
(5, 18, 'Trà Đào Cam Sả', 'Đào tươi + Cam sành + Sả thơm mát', 55000, NULL, 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?auto=format&fit=crop&w=400&q=80', 1),
(5, 18, 'Trà Vải Lài Sả', 'Vải thiều + Hoa Lài + Sả', 52000, NULL, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80', 0),
(5, 19, 'Cà Phê Sữa Nóng', 'Cà phê Phúc Long pha sữa đặc', 40000, NULL, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80', 0),
(5, 20, 'Bánh Su Kem', 'Su kem nhân phô mai mềm mịn', 25000, NULL, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=400&q=80', 0);

-- Vouchers
INSERT INTO `vouchers` (`code`, `name`, `description`, `type`, `value`, `max_discount`, `min_order`, `usage_limit`, `end_date`) VALUES
('CICA20', 'Chào bạn mới - Giảm 20%', 'Giảm 20% cho đơn hàng đầu tiên, tối đa 50.000đ', 'percent', 20, 50000, 50000, 500, '2026-12-31'),
('FREESHIP', 'Miễn phí vận chuyển', 'Freeship cho mọi đơn từ 50.000đ', 'freeship', 100, 30000, 50000, 1000, '2026-12-31'),
('CICASAVE50', 'Giảm 50.000đ', 'Giảm thẳng 50.000đ cho đơn từ 150.000đ', 'fixed', 50000, 50000, 150000, 200, '2026-12-31'),
('SHIP0', 'Freeship không giới hạn', 'Miễn toàn bộ phí ship cho đơn từ 100.000đ', 'freeship', 100, 50000, 100000, 300, '2026-12-31'),
('WEEKEND30', 'Cuối tuần vui - Giảm 30%', 'Giảm 30% mỗi cuối tuần, tối đa 70.000đ', 'percent', 30, 70000, 80000, 150, '2026-12-31');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Kiểm tra: SELECT * FROM restaurants; SELECT * FROM vouchers;
-- ============================================================
