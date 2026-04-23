-- ============================================================
-- FIX: Cập nhật mật khẩu đúng cho tài khoản test
-- Chạy file này nếu đã import schema.sql phiên bản cũ
-- (phiên bản cũ dùng hash sai, không khớp với "Password123")
--
-- Mật khẩu mới sau khi chạy script này: Password123
-- ============================================================

USE `cicafood`;

-- Cập nhật hash cho admin@cicafood.vn → Password123
UPDATE `users`
SET `password_hash` = '$2y$10$OmCVxtpfQHc2ja1yo5ZMzek.7xzl9OzHieKSUUEsPGOHymdTyHH5a'
WHERE `email` = 'admin@cicafood.vn';

-- Cập nhật hash cho an@gmail.com → Password123
UPDATE `users`
SET `password_hash` = '$2y$10$YNO25cjz6hvI/HF7bsjeyeQNQkp1QiIWEwP3S4LKQ3a1gKpbsFyjW'
WHERE `email` = 'an@gmail.com';

-- Kiểm tra kết quả
SELECT id, full_name, email, role, is_active,
       LEFT(password_hash, 30) AS hash_preview
FROM `users`
WHERE email IN ('admin@cicafood.vn', 'an@gmail.com');

-- ============================================================
-- SAU KHI CHẠY: Đăng nhập với:
-- Email: admin@cicafood.vn  | Mật khẩu: Password123
-- Email: an@gmail.com       | Mật khẩu: Password123
-- ============================================================
