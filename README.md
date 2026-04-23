# 🍔 Cicafood - Website Đặt Đồ Ăn Trực Tuyến

> **Đồ án / bài tập lớn Lập trình Web**  
> Xây dựng hệ thống website cho phép khách hàng tìm kiếm nhà hàng, đặt món ăn, thanh toán và quản lý đơn hàng trực tuyến.

---

## 👥 Thành viên nhóm

| STT | Họ và tên | MSSV | Vai trò |
| --- | --------- | ---- | ------- |
| 1   | Bùi Trung Dũng | 23810310099 | Nhóm trưởng |
| 2   | Nguyễn Bá Tuấn Anh | 23810310109 | Thành viên |
| 3   | Nguyễn Trần Xuân Bắc | 23810310100 | Thành viên |

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
| Thanh toán | COD (Cash on Delivery), VNPAY Sandbox |
| Bảo mật    | PHP Sessions, bcrypt password hashing, PDO prepared statements |

---

## 🗂️ Cấu trúc thư mục dự án

```text
foodbooking/
├── api/                              # Backend API endpoints
│   ├── auth/
│   │   ├── login_process.php        # API đăng nhập (dự phòng)
│   │   ├── logout.php               # Đăng xuất
│   │   └── reset_password.php       # Reset mật khẩu 2 bước
│   ├── cart/
│   │   ├── add_to_cart.php          # Thêm món vào giỏ
│   │   └── update_cart.php          # Cập nhật giỏ hàng
│   ├── merchant/
│   │   └── upload_image.php         # Upload ảnh merchant
│   ├── order/
│   │   ├── apply_voucher.php        # Áp dụng voucher
│   │   └── cancel_order.php         # Hủy đơn hàng
│   ├── payment/
│   │   ├── vnpay_create.php         # Tạo thanh toán VNPAY
│   │   └── vnpay_return.php         # Callback VNPAY
│   ├── restaurant/
│   │   ├── filter_restaurants.php   # Lọc nhà hàng
│   │   └── toggle_favorite.php      # Yêu thích nhà hàng
│   ├── review/
│   │   └── submit_rating.php        # Gửi đánh giá
│   ├── voucher/
│   │   ├── get_available_vouchers.php
│   │   └── save_voucher.php
│   └── upload_image.php             # Upload ảnh chung
│
├── config/                           # Cấu hình hệ thống
│   ├── constants.php                 # Hằng số hệ thống
│   ├── database.php                  # Kết nối PDO + session_start()
│   ├── provinces.php                 # 63 tỉnh thành
│   └── vnpay.php                     # Cấu hình VNPAY
│
├── core/
│   └── helpers.php                   # Helper functions
│
├── database/
│   ├── migrations/
│   │   ├── 002_merchant.sql
│   │   ├── 003_upgrade_v2.sql
│   │   ├── 004_payment_system.sql
│   │   ├── 005_add_missing_columns.sql
│   │   └── migration_update.sql
│   ├── fix_passwords.sql             # ⚡ Fix mật khẩu tài khoản test
│   ├── fix_vouchers_table.sql        # Fix bảng vouchers
│   ├── install.php                   # Cài đặt database tự động
│   ├── run_all_migrations.php        # 🔄 Chạy tất cả migrations
│   ├── run_migration.php             # Chạy migration lẻ
│   └── schema.sql                    # Schema đầy đủ (kèm dữ liệu mẫu)
│
├── docs/                             # Tài liệu hướng dẫn & SRS
│   ├── setup/USER_GUIDE.md           # Hướng dẫn cài đặt
│   └── srs/SRS_AUTH.md               # Tài liệu SRS Authentication
│
├── includes/                         # Shared PHP includes
│   ├── footer.php
│   ├── functions.php                 # Helper functions (PDO, session, image...)
│   ├── header.php
│   └── provinces.php
│
├── merchant/                         # Merchant Portal
│   ├── auth_check.php                # Middleware xác thực merchant
│   ├── index.php                     # Dashboard
│   ├── layout.php                    # Layout chính
│   ├── layout_footer.php
│   ├── menu.php                      # Quản lý thực đơn
│   ├── menu_categories.php           # Quản lý danh mục
│   ├── orders.php                    # Quản lý đơn hàng
│   ├── settings.php                  # Cài đặt nhà hàng
│   ├── vouchers.php                  # Quản lý voucher
│   └── api_toggle_open.php           # Bật/tắt mở cửa
│
├── public/
│   ├── images/                       # Ảnh tĩnh
│   └── uploads/
│       ├── avatars/                  # Avatar người dùng
│       ├── menu_items/               # Ảnh món ăn
│       ├── restaurants/              # Ảnh nhà hàng
│       └── reviews/                  # Ảnh đánh giá
│
├── sources/                          # Tài liệu SRS gốc
│   ├── SRS_Banking.md
│   ├── SRS_Login.md
│   ├── SRS_Menu.md
│   ├── SRS_Order.md
│   ├── SRS_Search.md
│   └── SRS_User.md
│
├── views/                            # View templates (MVC)
│   ├── auth/
│   │   ├── login.php                 # Trang đăng nhập (logic tích hợp)
│   │   └── register.php             # Trang đăng ký
│   ├── home/
│   │   └── index.php                 # Trang chủ
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
│
├── .htaccess                         # Apache rewrite rules
├── CHANGELOG.md
├── DECUONG.md
├── index.php                         # Entry point
├── README.md                         # File này
└── TEAM_WORK_DIVISION.md
```

📊 **Tổng thống kê:** 50+ PHP files · 14,000+ dòng code · 23 API endpoints · 15 view pages · 10 bảng DB

---

## ✨ Chức năng chính

### 👤 Khách hàng (Customer)
- Đăng ký, đăng nhập, đăng xuất, quên mật khẩu
- Tìm kiếm & lọc nhà hàng (theo từ khóa, danh mục, tỉnh thành)
- Xem chi tiết nhà hàng, menu, đánh giá
- Thêm món vào giỏ hàng, áp dụng voucher
- Đặt hàng (COD / VNPAY), theo dõi trạng thái
- Đánh giá nhà hàng, lưu yêu thích
- Quản lý hồ sơ cá nhân, avatar, ví voucher

### 🏪 Chủ nhà hàng (Merchant)
- Dashboard tổng quan (doanh thu, đơn hàng, đánh giá)
- Quản lý menu, danh mục món ăn, upload ảnh
- Xác nhận & cập nhật trạng thái đơn hàng
- Tạo & quản lý voucher (%, fixed, freeship)
- Cài đặt thông tin nhà hàng, bật/tắt mở cửa

---

## ⚙️ Hướng dẫn cài đặt

### 1️⃣ Yêu cầu môi trường
- **XAMPP** (Apache + MySQL)
- **PHP 8.0+**
- **MySQL 8.0+ / MariaDB 10.x+**

### 2️⃣ Đưa project vào htdocs

```bash
git clone <repo-url> D:\xampp\htdocs\foodbooking
```

### 3️⃣ Khởi động XAMPP
Mở XAMPP Control Panel → Start **Apache** và **MySQL**

### 4️⃣ Tạo Database & Import Schema

**Cách 1: Tự động hoàn toàn (Khuyên dùng)**
1. Tạo database trống `cicafood` trong phpMyAdmin.
2. Truy cập: `http://localhost/foodbooking/database/install.php` để khởi tạo cấu trúc ban đầu.
3. Truy cập: `http://localhost/foodbooking/database/run_all_migrations.php` để cập nhật các tính năng mới nhất.

**Cách 2: Thủ công (phpMyAdmin)**
1. Tạo database: `cicafood` | Collation: `utf8mb4_unicode_ci`
2. Import file `database/schema.sql`.
3. Để cập nhật các thay đổi mới nhất (như cột tỉnh thành, voucher nhà hàng...), hãy import tiếp các file trong thư mục `database/migrations/` hoặc chạy file `database/run_all_migrations.php`.

> ⚡ **Lưu ý mật khẩu:** Nếu không đăng nhập được tài khoản test, hãy chạy file `database/fix_passwords.sql` để reset mật khẩu về `Password123`.

### 5️⃣ Kiểm tra cấu hình

`config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cicafood');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP mặc định: để trống
define('DB_CHARSET', 'utf8mb4');
```

`config/constants.php`:
```php
define('SITE_URL', 'http://localhost/foodbooking');
```

### 6️⃣ Truy cập ứng dụng
- **Trang chủ:** `http://localhost/foodbooking`
- **Merchant Portal:** `http://localhost/foodbooking/merchant/index.php`

---

## 📖 Hướng dẫn sử dụng

### 🛍️ Đối với Khách hàng (Customer)
1. **Tìm kiếm:** Lọc nhà hàng theo **Tỉnh/Thành phố** ngay tại trang chủ.
2. **Đặt món:** Chọn món ăn → Thêm vào giỏ hàng → Kiểm tra giỏ hàng.
3. **Voucher:** Tại trang Checkout, bấm "Chọn Voucher" để áp mã giảm giá hoặc Freeship.
4. **Thanh toán:** Chọn **COD** (Tiền mặt) hoặc **VNPAY** (Online).

### 🏪 Đối với Nhà hàng (Merchant)
1. **Trạng thái:** Bật/Tắt nút **"Mở cửa"** tại Dashboard để khách có thể đặt món.
2. **Menu:** Quản lý danh mục và món ăn, hỗ trợ kéo thả upload ảnh.
3. **Đơn hàng:** Nhận thông báo đơn mới, xác nhận hoặc hủy đơn.
4. **Voucher:** Tự tạo mã giảm giá riêng cho quán của mình.

---

## 🔐 Tài Khoản Test

| Vai trò | Email | Mật khẩu | Ghi chú |
| ------- | ----- | -------- | ------- |
| **Customer/Merchant** | admin@cicafood.vn | `Password123` | Truy cập Merchant Portal |
| **Customer** | an@gmail.com | `Password123` | Khách hàng test |

> ⚠️ **Lưu ý quan trọng:**
> - Mật khẩu phân biệt hoa/thường: `Password123` (**P** viết hoa)
> - Nếu không đăng nhập được → chạy `database/fix_passwords.sql` trong phpMyAdmin
> - Mật khẩu được hash bằng **bcrypt** (`password_hash()` PHP)

---

## 🧪 Hướng Dẫn Test

### 🎯 Test Flow 1: Khách Hàng (Customer)

```
1. Đăng nhập:
   URL: http://localhost/foodbooking/views/auth/login.php
   Email: an@gmail.com | Password: Password123

2. Tìm kiếm nhà hàng → Lọc theo danh mục / tỉnh thành

3. Xem chi tiết nhà hàng → Thêm món vào giỏ hàng

4. Thanh toán:
   → Chọn voucher (CICASAVE50 / SHIP0)
   → Nhập địa chỉ giao hàng
   → Chọn COD hoặc VNPAY

5. Xem lịch sử: http://localhost/foodbooking/views/order/history.php

6. Đánh giá nhà hàng sau khi nhận hàng
```

### 🏪 Test Flow 2: Merchant

```
1. Đăng nhập Merchant Portal:
   URL: http://localhost/foodbooking/merchant/index.php
   Email: admin@cicafood.vn | Password: Password123

2. Dashboard → Xem doanh thu, đơn chờ xử lý

3. Quản lý Menu → Thêm/sửa/xóa món ăn

4. Quản lý Đơn hàng → Xác nhận → Cập nhật trạng thái

5. Quản lý Voucher → Tạo mã giảm giá mới

6. Cài đặt → Cập nhật thông tin nhà hàng
```

### 🔄 Test Flow 3: Quên Mật Khẩu

```
URL: http://localhost/foodbooking/views/auth/login.php
→ Click "Quên mật khẩu?"
→ Nhập email: an@gmail.com
→ Xác minh thành công → Nhập mật khẩu mới (≥8 ký tự, có chữ + số)
→ Đăng nhập lại với mật khẩu mới
```

### 💳 Test VNPAY Sandbox

```
Thẻ test NCB:
  Số thẻ : 9704198526191432198
  Tên    : NGUYEN VAN A
  Phát hành: 07/15
  OTP    : 123456
```

### 🎟️ Voucher Test

| Mã | Loại | Giá trị | Đơn tối thiểu |
| -- | ---- | ------- | ------------- |
| CICA20 | Giảm % | 20% (tối đa 50k) | 50.000đ |
| FREESHIP | Freeship | 100% ship (tối đa 30k) | 50.000đ |
| CICASAVE50 | Giảm tiền | 50.000đ | 150.000đ |
| SHIP0 | Freeship | 100% ship (tối đa 50k) | 100.000đ |
| WEEKEND30 | Giảm % | 30% (tối đa 70k) | 80.000đ |

---

## 🐛 Troubleshooting

### ❌ Không đăng nhập được với tài khoản test

**Nguyên nhân:** Hash bcrypt trong DB không khớp với mật khẩu `Password123`

**Giải pháp:**
```sql
-- Chạy trong phpMyAdmin (Tab SQL):
USE cicafood;
UPDATE users SET password_hash = '$2y$10$OmCVxtpfQHc2ja1yo5ZMzek.7xzl9OzHieKSUUEsPGOHymdTyHH5a'
WHERE email = 'admin@cicafood.vn';
UPDATE users SET password_hash = '$2y$10$YNO25cjz6hvI/HF7bsjeyeQNQkp1QiIWEwP3S4LKQ3a1gKpbsFyjW'
WHERE email = 'an@gmail.com';
```
Hoặc import file `database/fix_passwords.sql`

---

### ❌ Cannot connect to database
```
1. XAMPP → MySQL đã Start?
2. config/database.php → DB_PASS = '' (trống nếu dùng XAMPP mặc định)
3. phpMyAdmin → database 'cicafood' đã tạo?
```

### ❌ 404 Not Found
```
1. Apache đã Start trong XAMPP?
2. URL đúng: http://localhost/foodbooking/
3. .htaccess có tồn tại? RewriteBase /foodbooking đúng chưa?
4. httpd.conf: LoadModule rewrite_module ... đã bật?
```

### ❌ Cannot upload image
```
1. Thư mục public/uploads/ tồn tại và có quyền ghi
2. php.ini: upload_max_filesize = 10M, post_max_size = 10M
3. File < 5MB, định dạng JPG/PNG/WEBP/GIF
```

### ❌ Session không hoạt động
```
1. php.ini: session.save_path = "C:/xampp/tmp"
2. Trình duyệt không block cookies
3. Không dùng chế độ Incognito
```

### ❌ Voucher không hiển thị
```sql
-- Chạy trong phpMyAdmin:
ALTER TABLE user_vouchers
ADD COLUMN IF NOT EXISTS used_at TIMESTAMP NULL DEFAULT NULL AFTER is_used;
```
Hoặc import `database/fix_vouchers_table.sql`

---

## 📝 Changelog

### ✅ [2.1.1] - 2026-04-23

**Database & System Updates:**
- ✅ **Hệ thống Migration tự động:** Tạo `run_all_migrations.php` để cập nhật DB dễ dàng.
- ✅ **Merchant API:** Thêm chức năng Bật/Tắt trạng thái hoạt động của nhà hàng.
- ✅ **Voucher System:** Hỗ trợ tách biệt voucher hệ thống và voucher riêng của nhà hàng.
- ✅ **Database Schema:** Thêm cột `province` cho nhà hàng và `restaurant_id` cho voucher.
- ✅ **Documentation:** Dọn dẹp các file tài liệu dư thừa, tập trung vào README, DECUONG và CHANGELOG.

### ✅ [2.1.0] - 2026-04-22
- ✅ **Tích hợp VNPAY:** Hỗ trợ thanh toán online qua sandbox.
- ✅ **Fix lỗi đăng nhập:** Cập nhật hash bcrypt đúng cho các tài khoản test.
- ✅ **Fix `api/auth/login_process.php`:** Đồng bộ tên cột database.

---

## 📸 Screenshots Đề Xuất (Demo)

| Màn hình | Mô tả |
| -------- | ----- |
| Trang chủ | Danh sách nhà hàng, filter danh mục |
| Chi tiết nhà hàng | Menu, đánh giá, thêm vào giỏ |
| Checkout | Voucher, địa chỉ, VNPAY |
| Merchant Dashboard | Doanh thu, đơn chờ xử lý |
| Quản lý Menu | CRUD món ăn, upload ảnh |

---

## 📄 License

Dự án phát triển cho mục đích học tập và nghiên cứu.

---

## 🙏 Acknowledgments

- TailwindCSS · Font Awesome · Unsplash · PHP Community

---

_Hà Nội, tháng 04 năm 2026_
