# 🍔 Cicafood - Website Đặt Đồ Ăn Trực Tuyến

> **Đồ án / bài tập lớn Lập trình Web**  
> Xây dựng hệ thống website cho phép khách hàng tìm kiếm nhà hàng, đặt món ăn, thanh toán và quản lý đơn hàng trực tuyến.

---

## 👥 Thành viên nhóm

| STT | Họ và tên | MSSV | Vai trò |
| --- | --------- | ---- | ------- |
| 1   | [Bùi Trung Dũng] | [23810310099] | Nhóm trưởng |
| 2   | [Nguyễn Bá Tuấn Anh] | [23810310109] | Thành viên |
| 3   | [Nguyễn Trần Xuân Bắc] | [23810310100] | Thành viên |

---

## 🎯 Giới thiệu

Cicafood là nền tảng đặt đồ ăn trực tuyến hiện đại, kết nối khách hàng với hàng trăm nhà hàng trên toàn quốc. Hệ thống hỗ trợ 2 vai trò chính:

- **Khách hàng (Customer):** Tìm kiếm nhà hàng, xem menu, đặt món, thanh toán, theo dõi đơn hàng
- **Chủ nhà hàng (Merchant):** Quản lý nhà hàng, menu, đơn hàng, voucher, doanh thu

---

## 🚀 Công nghệ sử dụng

| Thành phần | Công nghệ |
| ---------- | --------- |
| Frontend   | HTML5, CSS3, JavaScript ES6+, TailwindCSS 3.x |
| Backend    | PHP 8.0+ thuần theo mô hình MVC |
| Database   | MySQL 8.0 / MariaDB 10.x |
| Web server | Apache 2.4 (XAMPP) |
| UI Framework | TailwindCSS CDN, Font Awesome 6.5 |
| Session & Auth | PHP Sessions, bcrypt password hashing |

---

## 📋 Tài liệu Đặc tả Yêu cầu Phần mềm (SRS)

Các tài liệu SRS được lưu trong thư mục [`/sources/`](./sources/) và [`/docs/srs/`](./docs/srs/)

| Mã | Chức năng | Tài liệu | Trạng thái |
| -- | --------- | -------- | ---------- |
| AUTH-01 | Xác thực người dùng | [SRS_Login.md](./sources/SRS_Login.md) / [SRS_AUTH.md](./docs/srs/SRS_AUTH.md) | ✅ |
| SEARCH-01 | Tìm kiếm & khám phá | [SRS_Search.md](./sources/SRS_Search.md) | ✅ |
| ORDER-01 | Đặt hàng & thanh toán | [SRS_Order.md](./sources/SRS_Order.md) | ✅ |
| MENU-01 | Quản lý thực đơn | [SRS_Menu.md](./sources/SRS_Menu.md) | ✅ |
| USER-01 | Quản lý người dùng | [SRS_User.md](./sources/SRS_User.md) | ✅ |
| BANK-01 | Thanh toán ngân hàng | [SRS_Banking.md](./sources/SRS_Banking.md) | ✅ |

---

## 🗂️ Cấu trúc thư mục dự án

```text
foodbooking/
├── api/
│   ├── auth/
│   │   ├── login_process.php
│   │   ├── logout.php
│   │   └── reset_password.php
│   ├── cart/
│   │   ├── add_to_cart.php
│   │   └── update_cart.php
│   ├── merchant/
│   │   └── upload_image.php
│   ├── order/
│   │   ├── apply_voucher.php
│   │   └── cancel_order.php
│   ├── restaurant/
│   │   ├── filter_restaurants.php
│   │   └── toggle_favorite.php
│   ├── review/
│   │   └── submit_rating.php
│   ├── voucher/
│   │   └── save_voucher.php
│   └── upload_image.php
├── config/
│   ├── constants.php
│   ├── database.php
│   └── provinces.php
├── core/
│   └── helpers.php
├── database/
│   ├── migrations/
│   │   ├── 002_merchant.sql
│   │   ├── 003_upgrade_v2.sql
│   │   └── migration_update.sql
│   ├── install.php
│   ├── run_migration.php
│   └── schema.sql
├── docs/
│   ├── setup/
│   │   └── USER_GUIDE.md
│   └── srs/
│       └── SRS_AUTH.md
├── image/
│   └── uploads/
├── includes/
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── provinces.php
├── merchant/
│   ├── api_toggle_open.php
│   ├── auth_check.php
│   ├── index.php
│   ├── layout.php
│   ├── layout_footer.php
│   ├── menu.php
│   ├── menu_categories.php
│   ├── orders.php
│   ├── settings.php
│   └── vouchers.php
├── public/
│   ├── images/
│   └── uploads/
│       ├── avatars/
│       ├── menu_items/
│       ├── restaurants/
│       └── reviews/
├── sources/
│   ├── SRS_Admin.md
│   ├── SRS_Banking.md
│   ├── SRS_Login.md
│   ├── SRS_Menu.md
│   ├── SRS_Order.md
│   ├── SRS_Search.md
│   └── SRS_User.md
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── home/
│   │   └── index.php
│   ├── layouts/
│   │   ├── footer.php
│   │   ├── header.php
│   │   └── merchant_layout.php
│   ├── merchant/
│   │   ├── dashboard.php
│   │   ├── menu.php
│   │   ├── orders.php
│   │   ├── settings.php
│   │   └── vouchers.php
│   ├── order/
│   │   ├── checkout.php
│   │   ├── history.php
│   │   └── success.php
│   ├── restaurant/
│   │   ├── detail.php
│   │   └── list.php
│   └── user/
│       ├── favorites.php
│       ├── profile.php
│       └── vouchers.php
├── .htaccess
├── CHANGELOG.md
├── index.php
└── README.md
```

---

## ✨ Chức năng chính của hệ thống

### 1. Khách hàng (Customer)

-Đăng ký, đăng nhập, đăng xuất
-Quên mật khẩu / đặt lại mật khẩu (direct reset)
-Tìm kiếm nhà hàng theo từ khóa, khu vực, danh mục
-Lọc theo tỉnh thành (63 tỉnh thành Việt Nam)
-Xem chi tiết nhà hàng, menu, đánh giá
-Thêm món vào giỏ hàng
-Áp dụng voucher giảm giá
-Đặt hàng và thanh toán
-Xem lịch sử đơn hàng
-Hủy đơn hàng (khi còn hiệu lực)
-Đánh giá & review nhà hàng
-Lưu nhà hàng yêu thích
-Quản lý ví voucher
-Cập nhật thông tin cá nhân & avatar

### 2. Chủ nhà hàng (Merchant)

-Dashboard tổng quan (doanh thu, đơn hàng, đánh giá)
-Quản lý đơn hàng (xác nhận, hoàn thành, hủy)
-Quản lý thực đơn & danh mục món ăn
-Thêm/sửa/xóa món ăn
-Upload ảnh món ăn
-Quản lý voucher của nhà hàng
-Cập nhật thông tin nhà hàng
-Bật/tắt trạng thái mở cửa
-Xem thống kê doanh thu

---

## ⚙️ Hướng dẫn cài đặt và chạy dự án

### 1️⃣ Yêu cầu môi trường

- **XAMPP** (hoặc Apache + MySQL riêng biệt)
- **PHP 8.0+**
- **MySQL 8.0+ / MariaDB 10.x+**
- **Trình duyệt web** (Chrome, Firefox, Edge, Safari)
- **phpMyAdmin** (thường có sẵn trong XAMPP)

### 2️⃣ Đưa project vào htdocs

1. **Clone hoặc copy dự án:**

```bash
# Clone từ Git
git clone <repo-url> D:\xampp\htdocs\foodbooking

# Hoặc copy thủ công vào:
D:\xampp\htdocs\foodbooking
```

### 3️⃣ Khởi động dịch vụ XAMPP

1. Mở **XAMPP Control Panel**
2. Click **Start** cho:
   - ✅ **Apache**
   - ✅ **MySQL**

### 4️⃣ Tạo Database & Import Dữ Liệu

#### **Cách 1: phpMyAdmin (Dễ nhất)**

1. Truy cập: http://localhost/phpmyadmin
2. Click **Databases** → tạo database mới:
   - **Name:** `cicafood`
   - **Collation:** `utf8mb4_unicode_ci`
3. Chọn database vừa tạo
4. Tab **Import** → chọn file `database/schema.sql` → **Import**

#### **Cách 2: MySQL CLI**

```bash
mysql -u root -p
CREATE DATABASE cicafood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cicafood;
SOURCE database/schema.sql;
```

### 5️⃣ Cấu hình Kết nối Database

Mở file `config/database.php` và kiểm tra:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cicafood');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### 6️⃣ Cấu hình Constants

Mở file `config/constants.php` và cập nhật:

```php
<?php
// Site configuration
define('SITE_NAME', 'Cicafood');
define('SITE_URL', 'http://localhost/foodbooking');
define('SITE_EMAIL', 'support@cicafood.vn');

// Upload paths
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/');
```

### 7️⃣ Khởi chạy ứng dụng

1. Mở trình duyệt: **http://localhost/foodbooking**
2. Nếu thấy **404**, kiểm tra:
   - Apache đã start?
   - File `index.php` có tồn tại?
   - `.htaccess` có bị chặn?

### 8️⃣ (Tuỳ chọn) Chạy Migrations

Để cập nhật database lên phiên bản mới nhất:

```bash
# Truy cập:
http://localhost/foodbooking/database/run_migration.php
```

Hoặc import thủ công các file trong `database/migrations/`

---

## 🔐 Tài Khoản Test

| Vai trò | Email | Mật khẩu | Ghi chú |
| ------- | ----- | -------- | ------- |
| Customer | admin@cicafood.vn | Password123 | Khách hàng test |
| Customer | bahieu@gmail.com | 123456 | Khách hàng test |

**⚠️ Lưu ý:** Mật khẩu được hash bằng bcrypt. Đổi mật khẩu sau khi cài đặt để bảo mật!

---

## 🧪 Hướng Dẫn Test Hoàn Chỉnh

### Test Customer Flow (Khách hàng)

**1. Đăng ký tài khoản mới:**
```
URL: http://localhost/foodbooking/views/auth/register.php
→ Nhập thông tin đăng ký
→ Đăng ký thành công, tự động đăng nhập
```

**2. Đăng nhập:**
```
URL: http://localhost/foodbooking/views/auth/login.php
Email: an@gmail.com
Password: Password123
→ Login thành công
```

**3. Quên mật khẩu (Direct Reset):**
```
URL: http://localhost/foodbooking/views/auth/login.php
→ Click "Quên mật khẩu?"
→ Nhập email → Xác minh
→ Nhập mật khẩu mới
→ Reset thành công, đăng nhập lại
```

**4. Tìm kiếm & Đặt món:**
```
URL: http://localhost/foodbooking (Trang chủ)
→ Tìm kiếm nhà hàng (theo tên, danh mục, tỉnh thành)
→ Chọn nhà hàng → Xem menu
→ Thêm món vào giỏ hàng
→ Áp dụng voucher (nếu có)
→ Đặt hàng
```

**5. Quản lý đơn hàng:**
```
URL: http://localhost/foodbooking/views/order/history.php
→ Xem danh sách đơn hàng
→ Xem chi tiết đơn
→ Hủy đơn (nếu cần)
```

**6. Đánh giá nhà hàng:**
```
→ Vào trang chi tiết nhà hàng
→ Viết đánh giá & cho điểm sao
→ Gửi review
```

### Test Merchant Flow (Chủ nhà hàng)

**1. Đăng nhập:**
```
URL: http://localhost/foodbooking/merchant/index.php
→ Đăng nhập với tài khoản merchant
```

**2. Quản lý Menu:**
```
Menu → Quản lý thực đơn
→ Thêm/sửa/xóa món ăn
→ Upload ảnh món ăn
→ Cập nhật giá, mô tả
```

**3. Quản lý Đơn hàng:**
```
Menu → Quản lý đơn hàng
→ Xem danh sách đơn mới
→ Xác nhận đơn hàng
→ Hoàn thành đơn hàng
```

**4. Quản lý Voucher:**
```
Menu → Quản lý voucher
→ Tạo voucher mới
→ Cập nhật voucher
→ Vô hiệu hóa voucher
```

**5. Xem Thống kê:**
```
Dashboard
→ Xem doanh thu
→ Xem số đơn hàng
→ Xem đánh giá
```

### Test Admin Flow (Quản trị viên)

**1. Đăng nhập:**
```
Email: admin@cicafood.vn
Password: Password123
URL: http://localhost/foodbooking/admin/dashboard.php
```

**2. Quản lý Users:**
```
Menu → Quản lý người dùng
→ Xem danh sách users
→ Khóa/mở khóa tài khoản
→ Xóa user (nếu cần)
```

**3. Quản lý Nhà hàng:**
```
Menu → Quản lý nhà hàng
→ Duyệt nhà hàng mới
→ Ẩn/hiện nhà hàng
→ Xóa nhà hàng vi phạm
```

**4. Xem Thống kê:**
```
Dashboard
→ Tổng users, restaurants, orders
→ Doanh thu hệ thống
→ Biểu đồ thống kê
```

---

## 🐛 Troubleshooting

**Lỗi: Cannot connect to database**
```
→ Kiểm tra MySQL đã start
→ Kiểm tra DB_HOST, DB_USER, DB_PASS trong config/database.php
→ Kiểm tra database `cicafood` đã tạo
```

**Lỗi: 404 Not Found**
```
→ Kiểm tra Apache đã start
→ Kiểm tra .htaccess file có tồn tại
→ Kiểm tra URL có đúng (http://localhost/foodbooking/)
→ Kiểm tra RewriteBase trong .htaccess
```

**Lỗi: Cannot upload image**
```
→ Kiểm tra thư mục public/uploads/ có quyền ghi
→ Kiểm tra file size < 5MB
→ Kiểm tra định dạng file (JPG, PNG, WEBP, GIF)
→ Kiểm tra MIME type validation
```

**Lỗi: Session not working**
```
→ Kiểm tra session.save_path trong php.ini
→ Kiểm tra cookies có bị block không
→ Clear browser cache & cookies
```

**Lỗi: Cannot redeclare function**
```
→ Kiểm tra không có duplicate function trong includes/functions.php
→ Kiểm tra file không được require/include nhiều lần
```

---

## 📝 Changelog - Phiên Bản Mới

### ✅ [2.0.1] - 2026-04-20

**Image Upload System Fixes:**
- ✅ Sửa lỗi duplicate function `getImageUrl()` trong `includes/functions.php`
- ✅ Thêm MIME type validation với `finfo_open()` để bảo mật
- ✅ Chuẩn hóa response format: `{success, message, path, url}`
- ✅ Thêm parameter `type` cho merchant API (restaurant, menu_item, review)
- ✅ Upload vào đúng thư mục con: `avatars/`, `restaurants/`, `menu_items/`, `reviews/`
- ✅ Cải thiện error handling và rollback khi lỗi database

### ✅ [2.0.0] - 2026-04-20

**Path Updates (Phase 3):**
- ✅ Cập nhật tất cả paths từ `/cicafood/` → `/foodbooking/`
- ✅ Chuyển từ clean URLs sang direct file paths
- ✅ Sửa 73 HTML links, 17 API calls, 4 JavaScript redirects
- ✅ Cập nhật 22 image paths với helper function `getImageUrl()`
- ✅ Tạo helper function `getImageUrl()` tự động thêm prefix
- ✅ Hỗ trợ relative và absolute paths
- ✅ Fallback images khi ảnh không tồn tại

**Restructure (Phase 1-2):**
- ✅ Tổ chức lại dự án theo mô hình MVC chuẩn
- ✅ Tách biệt cấu hình vào `config/`
- ✅ API structure theo modules
- ✅ View structure rõ ràng
- ✅ Database migrations & seeds
- ✅ Upload path vào `public/uploads/`

---

## 📸 Ảnh chụp màn hình đề xuất khi nộp/demo

### User
- Trang chủ với danh sách nhà hàng
- Trang chi tiết nhà hàng & menu
- Giỏ hàng & checkout
- Lịch sử đơn hàng
- Trang cá nhân & voucher

### Merchant
- Dashboard tổng quan
- Quản lý menu
- Quản lý đơn hàng
- Quản lý voucher
- Cài đặt nhà hàng

### Admin
- Dashboard hệ thống
- Quản lý users
- Quản lý restaurants
- Quản lý orders
- Thống kê & báo cáo

---

## 🧪 Gợi ý kiểm thử nhanh

### User flow
- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập
- ✅ Quên mật khẩu (direct reset)
- ✅ Tìm kiếm nhà hàng
- ✅ Xem chi tiết & menu
- ✅ Thêm vào giỏ hàng
- ✅ Áp dụng voucher
- ✅ Đặt hàng
- ✅ Xem lịch sử đơn
- ✅ Hủy đơn nếu cần
- ✅ Đánh giá nhà hàng

### Merchant flow
- ✅ Đăng nhập merchant
- ✅ Xem dashboard
- ✅ Quản lý menu (thêm/sửa/xóa món)
- ✅ Upload ảnh món ăn
- ✅ Quản lý đơn hàng
- ✅ Cập nhật thông tin nhà hàng
- ✅ Quản lý voucher
- ✅ Xem thống kê doanh thu

### Admin flow
- ✅ Đăng nhập admin
- ✅ Xem dashboard tổng quan
- ✅ Khóa / mở khóa user
- ✅ Ẩn / mở nhà hàng
- ✅ Thêm / sửa / xóa category
- ✅ Quản lý đơn hàng
- ✅ Kiểm duyệt review

---

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

---

## 🙏 Acknowledgments

- TailwindCSS for UI framework
- Font Awesome for icons
- Unsplash for placeholder images
- PHP community for documentation

---

_Hà Nội, tháng 04 năm 2026_
