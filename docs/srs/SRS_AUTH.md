# SRS-AUTH-01: Xác Thực Người Dùng

**Mã chức năng**: AUTH-01  
**Tên chức năng**: Xác thực người dùng  
**Phiên bản**: 2.0  
**Ngày cập nhật**: 20/04/2026  
**Trạng thái**: ✅ Hoàn thành

---

## 1. MÔ TẢ TỔNG QUAN

Module xác thực người dùng cung cấp các chức năng đăng ký, đăng nhập, đăng xuất và quên mật khẩu cho hệ thống Cicafood.

### 1.1. Mục tiêu
- Xác thực danh tính người dùng an toàn
- Quản lý phiên đăng nhập (session)
- Hỗ trợ đặt lại mật khẩu trực tiếp
- Phân quyền người dùng (customer, merchant, admin)

### 1.2. Phạm vi
- Đăng ký tài khoản mới
- Đăng nhập hệ thống
- Đăng xuất
- Quên mật khẩu (direct reset)
- Ghi nhớ đăng nhập (Remember me)

---

## 2. ACTORS (Người dùng)

| Actor | Mô tả | Quyền hạn |
|-------|-------|-----------|
| **Guest** | Khách chưa đăng nhập | Xem trang chủ, đăng ký, đăng nhập |
| **Customer** | Khách hàng | Đặt món, đánh giá, quản lý tài khoản |
| **Merchant** | Chủ quán | Quản lý quán, menu, đơn hàng |
| **Admin** | Quản trị viên | Quản lý toàn hệ thống |

---

## 3. CHỨC NĂNG CHI TIẾT

### 3.1. Đăng Ký (Register)

#### 3.1.1. Mô tả
Cho phép người dùng tạo tài khoản mới trong hệ thống.

#### 3.1.2. Luồng chính
1. User truy cập trang đăng ký (`Register.php`)
2. Nhập thông tin:
   - Họ tên đầy đủ
   - Email
   - Số điện thoại (optional)
   - Mật khẩu
   - Xác nhận mật khẩu
   - Địa chỉ (optional)
3. Hệ thống validate:
   - Email chưa tồn tại
   - Mật khẩu >= 6 ký tự
   - Mật khẩu khớp với xác nhận
4. Hash mật khẩu bằng `password_hash()`
5. Lưu vào database (bảng `users`)
6. Tự động đăng nhập
7. Redirect về trang chủ

#### 3.1.3. Luồng phụ
- **Email đã tồn tại**: Hiển thị lỗi "Email đã được sử dụng"
- **Mật khẩu không khớp**: Hiển thị lỗi "Mật khẩu xác nhận không khớp"
- **Validation fail**: Hiển thị lỗi tương ứng

#### 3.1.4. Validation Rules
```php
- full_name: required, min:3, max:100
- email: required, email, unique:users
- phone: optional, regex:/^[0-9]{10,11}$/
- password: required, min:6
- confirm_password: required, same:password
- address: optional, max:500
```

#### 3.1.5. Database
```sql
INSERT INTO users (
    full_name, email, phone, password_hash, 
    address, role, is_active, created_at
) VALUES (?, ?, ?, ?, ?, 'customer', 1, NOW())
```

#### 3.1.6. Files liên quan
- `Register.php` - View đăng ký
- `api/auth/register.php` - API xử lý đăng ký (future)

---

### 3.2. Đăng Nhập (Login)

#### 3.2.1. Mô tả
Cho phép người dùng đăng nhập vào hệ thống bằng email/phone và mật khẩu.

#### 3.2.2. Luồng chính
1. User truy cập trang đăng nhập (`login.php`)
2. Nhập thông tin:
   - Email hoặc Số điện thoại
   - Mật khẩu
   - Remember me (optional)
3. Hệ thống kiểm tra:
   - Tìm user theo email hoặc phone
   - Verify mật khẩu bằng `password_verify()`
   - Kiểm tra `is_active = 1`
4. Tạo session:
   ```php
   $_SESSION['user_id'] = $user['id'];
   $_SESSION['user_name'] = $user['full_name'];
   $_SESSION['user_email'] = $user['email'];
   $_SESSION['user_role'] = $user['role'];
   $_SESSION['user_avatar'] = $user['avatar'];
   ```
5. Nếu chọn "Remember me":
   - Tạo cookie `cicafood_remember` (30 ngày)
6. Redirect về trang trước đó hoặc homepage

#### 3.2.3. Luồng phụ
- **Sai email/phone**: Hiển thị "Email/SĐT hoặc mật khẩu không đúng"
- **Sai mật khẩu**: Hiển thị "Email/SĐT hoặc mật khẩu không đúng"
- **Tài khoản bị khóa**: Hiển thị "Tài khoản đã bị khóa"

#### 3.2.4. Security
- Không hiển thị chi tiết lỗi (email không tồn tại hay sai mật khẩu)
- Hash mật khẩu bằng bcrypt
- Session timeout: 24 giờ
- Remember me cookie: 30 ngày

#### 3.2.5. Database Query
```sql
SELECT * FROM users 
WHERE (email = ? OR phone = ?) 
  AND is_active = 1 
LIMIT 1
```

#### 3.2.6. Files liên quan
- `login.php` - View đăng nhập
- `login_process.php` - Xử lý đăng nhập (legacy)
- `api/auth/login.php` - API đăng nhập (future)

---

### 3.3. Quên Mật Khẩu (Forgot Password)

#### 3.3.1. Mô tả
Cho phép người dùng đặt lại mật khẩu trực tiếp mà không cần link email.

#### 3.3.2. Luồng chính - Step 1: Xác minh Email
1. User click "Quên mật khẩu?" trong trang login
2. Modal hiển thị
3. Nhập email
4. Click "Xác minh"
5. Hệ thống kiểm tra:
   - Email tồn tại trong database
   - Tài khoản active
6. Tạo token ngẫu nhiên:
   ```php
   $token = bin2hex(random_bytes(16));
   $expires = date('Y-m-d H:i:s', time() + 900); // 15 phút
   ```
7. Lưu token vào database:
   ```sql
   UPDATE users 
   SET reset_token = ?, reset_token_expires = ? 
   WHERE id = ?
   ```
8. Trả về token cho frontend
9. Tự động chuyển sang Step 2

#### 3.3.3. Luồng chính - Step 2: Đặt Mật Khẩu Mới
1. Nhập mật khẩu mới (>= 6 ký tự)
2. Nhập xác nhận mật khẩu
3. Click "Lưu mật khẩu"
4. Hệ thống kiểm tra:
   - Token hợp lệ và chưa hết hạn
   - Mật khẩu >= 6 ký tự
   - Mật khẩu khớp với xác nhận
5. Hash mật khẩu mới
6. Cập nhật database:
   ```sql
   UPDATE users 
   SET password_hash = ?, 
       reset_token = NULL, 
       reset_token_expires = NULL 
   WHERE id = ?
   ```
7. Hiển thị thông báo thành công
8. Tự động điền email vào form đăng nhập

#### 3.3.4. Luồng phụ
- **Email không tồn tại**: "Email này chưa được đăng ký"
- **Token hết hạn**: "Phiên xác minh đã hết hạn. Vui lòng thử lại"
- **Mật khẩu quá ngắn**: "Mật khẩu phải có ít nhất 6 ký tự"
- **Mật khẩu không khớp**: "Mật khẩu xác nhận không khớp"

#### 3.3.5. Security
- Token hết hạn sau 15 phút
- Token chỉ dùng 1 lần
- Token bị xóa sau khi reset thành công
- Không gửi email (direct reset)

#### 3.3.6. API Endpoints
```
POST /api/reset_password.php
Body: {
    "step": "verify",
    "email": "user@example.com"
}
Response: {
    "success": true,
    "token": "abc123...",
    "email": "user@example.com"
}

POST /api/reset_password.php
Body: {
    "step": "reset",
    "email": "user@example.com",
    "token": "abc123...",
    "new_password": "newpass123",
    "confirm_password": "newpass123"
}
Response: {
    "success": true,
    "message": "Đổi mật khẩu thành công!"
}
```

#### 3.3.7. Files liên quan
- `login.php` - Modal quên mật khẩu
- `api/reset_password.php` - API xử lý reset

---

### 3.4. Đăng Xuất (Logout)

#### 3.4.1. Mô tả
Cho phép người dùng đăng xuất khỏi hệ thống.

#### 3.4.2. Luồng chính
1. User click "Đăng xuất"
2. Hệ thống:
   - Xóa session: `session_destroy()`
   - Xóa cookie remember me (nếu có)
3. Redirect về trang chủ

#### 3.4.3. Files liên quan
- `logout.php` - Xử lý đăng xuất

---

## 4. DATABASE SCHEMA

### 4.1. Bảng `users`

```sql
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default_avatar.png',
  `address` TEXT DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT 'TP. Hồ Chí Minh',
  `role` ENUM('customer', 'merchant', 'admin') NOT NULL DEFAULT 'customer',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `reset_token` VARCHAR(64) DEFAULT NULL,
  `reset_token_expires` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. API ENDPOINTS

| Method | Endpoint | Mô tả | Auth Required |
|--------|----------|-------|---------------|
| POST | `/api/auth/register.php` | Đăng ký tài khoản | No |
| POST | `/api/auth/login.php` | Đăng nhập | No |
| POST | `/api/auth/logout.php` | Đăng xuất | Yes |
| POST | `/api/auth/reset_password.php` | Quên mật khẩu | No |

---

## 6. HELPER FUNCTIONS

### 6.1. `isLoggedIn()`
```php
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
```

### 6.2. `requireLogin()`
```php
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: {$redirect}");
        exit;
    }
}
```

### 6.3. `getCurrentUser()`
```php
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'] ?? 'Người dùng',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer',
        'avatar' => $_SESSION['user_avatar'] ?? null
    ];
}
```

---

## 7. UI/UX

### 7.1. Trang Đăng Nhập
- Split-screen design
- Left: Gradient background với logo và slogan
- Right: Form đăng nhập
- Modal quên mật khẩu 2 bước
- Toast notification

### 7.2. Trang Đăng Ký
- Form đầy đủ thông tin
- Validation real-time
- Password strength indicator
- Redirect tự động sau đăng ký

---

## 8. SECURITY

### 8.1. Password Hashing
```php
// Hash khi đăng ký/đổi mật khẩu
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify khi đăng nhập
if (password_verify($password, $user['password_hash'])) {
    // Đăng nhập thành công
}
```

### 8.2. Session Security
- Session timeout: 24 giờ
- Regenerate session ID sau login
- HttpOnly cookie
- Secure cookie (HTTPS)

### 8.3. CSRF Protection
- Token trong form
- Validate token khi submit

---

## 9. TESTING

### 9.1. Test Cases

| ID | Test Case | Expected Result |
|----|-----------|-----------------|
| AUTH-T01 | Đăng ký với email hợp lệ | Thành công, tự động đăng nhập |
| AUTH-T02 | Đăng ký với email đã tồn tại | Lỗi "Email đã được sử dụng" |
| AUTH-T03 | Đăng nhập với thông tin đúng | Thành công, redirect về homepage |
| AUTH-T04 | Đăng nhập với mật khẩu sai | Lỗi "Email/SĐT hoặc mật khẩu không đúng" |
| AUTH-T05 | Quên mật khẩu - email hợp lệ | Chuyển sang step 2 |
| AUTH-T06 | Quên mật khẩu - email không tồn tại | Lỗi "Email chưa được đăng ký" |
| AUTH-T07 | Đặt mật khẩu mới thành công | Thành công, có thể đăng nhập |
| AUTH-T08 | Token hết hạn (>15 phút) | Lỗi "Phiên xác minh đã hết hạn" |

---

## 10. CHANGELOG

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-03-01 | Initial version |
| 2.0 | 2026-04-20 | Thêm quên mật khẩu direct reset, cập nhật SRS |

---

## 11. REFERENCES

- [SRS_SEARCH.md](./SRS_SEARCH.md) - Tìm kiếm quán ăn
- [SRS_ORDER.md](./SRS_ORDER.md) - Đặt món
- [SRS_USER.md](./SRS_USER.md) - Quản lý người dùng

---

**Người viết**: AI Assistant  
**Ngày tạo**: 20/04/2026  
**Trạng thái**: ✅ Hoàn thành
