# ĐỀ CƯƠNG THỰC TẬP TỐT NGHIỆP

## Tên đề tài: Xây Dựng Website Đặt Đồ Ăn Trực Tuyến (Cicafood)

**Lớp:** LTWNC-D18CNPM2  
**Nhóm:** [Số nhóm]  
**Thành viên:**

| STT | Họ và tên | MSSV | Vai trò |
| --- | --------- | ---- | ------- |
| 1   | [Bùi Trung Dũng] | [23810310099] | Nhóm trưởng |
| 2   | [Nguyễn Bá Tuấn Anh] | [23810310109] | Thành viên |
| 3   | [Nguyễn Trần Xuân Bắc] | [23810310100] | Thành viên |

---

## Mô tả tóm tắt

Website cho phép khách hàng tìm kiếm nhà hàng, xem menu, đặt món ăn và thanh toán trực tuyến. Chủ nhà hàng (Merchant) có thể quản lý menu, đơn hàng, voucher và doanh thu. Admin quản lý toàn bộ hệ thống.

---

## Chức năng dự kiến

### 🔐 Admin

- Quản lý tài khoản người dùng (xem, khóa, mở khóa, phân quyền)
- Quản lý danh sách nhà hàng (duyệt đăng ký, ẩn, xóa)
- Quản lý danh mục món ăn (Cơm, Phở, Burger, Pizza, Sushi, Trà sữa, Bánh mì, Lẩu)
- Quản lý đơn hàng toàn hệ thống
- Quản lý đánh giá / review (duyệt, xóa vi phạm)
- Thống kê / Báo cáo (doanh thu, đơn hàng, nhà hàng nổi bật)

### 🍔 Chủ Nhà Hàng (Merchant)

- Đăng ký / Đăng nhập tài khoản merchant
- Quản lý thông tin nhà hàng (tên, địa chỉ, ảnh, giờ mở cửa)
- Quản lý thực đơn & danh mục món ăn (thêm, sửa, xóa món, cập nhật giá)
- Upload ảnh món ăn
- Quản lý đơn hàng (xác nhận, hoàn thành, hủy)
- Quản lý voucher của nhà hàng
- Bật/tắt trạng thái mở cửa
- Xem doanh thu & thống kê theo ngày/tháng
- Xem đánh giá từ khách hàng

### 👤 User (Khách hàng)

- Trang chủ (nhà hàng nổi bật, món ăn hot, tìm kiếm nhanh)
- Tìm kiếm & lọc nhà hàng (theo khu vực, danh mục, đánh giá sao)
- Lọc theo tỉnh thành (63 tỉnh thành Việt Nam)
- Xem chi tiết nhà hàng & menu
- Thêm món vào giỏ hàng
- Áp dụng voucher giảm giá
- Đặt hàng và thanh toán COD
- Xem lịch sử đơn hàng
- Hủy đơn hàng (khi còn hiệu lực)
- Đăng ký / Đăng nhập / Quên mật khẩu (direct reset)
- Quản lý thông tin cá nhân & avatar
- Lưu nhà hàng yêu thích
- Quản lý ví voucher
- Viết review & đánh giá sao sau khi nhận hàng

---

## Công nghệ sử dụng

| Thành phần | Công nghệ |
| ---------- | --------- |
| Frontend   | HTML5, CSS3, JavaScript ES6+, TailwindCSS 3.x |
| Backend    | PHP 8.0+ thuần theo mô hình MVC |
| Database   | MySQL 8.0 / MariaDB 10.x |
| Web server | Apache 2.4 (XAMPP) |
| Thanh toán | COD (Cash on Delivery) - Thanh toán khi nhận hàng |
| Bảo mật    | PHP Sessions, bcrypt password hashing, CSRF protection |

---

## Phân chia công việc

| Thành viên | Phụ trách |
| ---------- | --------- |
| Thành viên 1 | Frontend: Giao diện User (trang chủ, tìm nhà hàng, giỏ hàng, đặt hàng) |
| Thành viên 2 | Frontend: Giao diện Merchant + Admin (quản lý menu, đơn hàng, thống kê) |
| Thành viên 3 | Backend: Database, API xử lý đặt hàng, voucher, upload ảnh |

---

## Kế hoạch thực hiện (1 tuần)

| Ngày | Công việc |
| ---- | --------- |
| Ngày 1 | Thiết kế Database, dựng cấu trúc project MVC, phân chia task |
| Ngày 2 | Làm giao diện User: trang chủ, tìm kiếm, chi tiết nhà hàng |
| Ngày 3 | Làm chức năng giỏ hàng, áp dụng voucher, đặt hàng |
| Ngày 4 | Làm giao diện Merchant: quản lý menu, đơn hàng, voucher |
| Ngày 5 | Làm giao diện Admin + chức năng upload ảnh |
| Ngày 6 | Làm chức năng review, yêu thích, thống kê, báo cáo |
| Ngày 7 | Test toàn bộ, fix bug, hoàn thiện giao diện, viết README |

---

## Tài liệu kỹ thuật

### Database Schema

**Bảng chính:**
- `users` - Người dùng (customer, merchant, admin)
- `categories` - Danh mục loại hình nhà hàng
- `restaurants` - Nhà hàng
- `menu_categories` - Nhóm menu trong nhà hàng
- `menu_items` - Món ăn
- `orders` - Đơn hàng
- `order_items` - Chi tiết đơn hàng
- `vouchers` - Mã giảm giá
- `reviews` - Đánh giá nhà hàng

### API Endpoints

**Auth:**
- `POST /api/auth/login_process.php` - Đăng nhập
- `POST /api/auth/logout.php` - Đăng xuất
- `POST /api/auth/reset_password.php` - Quên mật khẩu

**Cart:**
- `POST /api/cart/add_to_cart.php` - Thêm vào giỏ
- `POST /api/cart/update_cart.php` - Cập nhật giỏ

**Order:**
- `POST /api/order/apply_voucher.php` - Áp dụng voucher
- `POST /api/order/cancel_order.php` - Hủy đơn

**Restaurant:**
- `POST /api/restaurant/filter_restaurants.php` - Lọc nhà hàng
- `POST /api/restaurant/toggle_favorite.php` - Yêu thích

**Review:**
- `POST /api/review/submit_rating.php` - Gửi đánh giá

**Voucher:**
- `POST /api/voucher/save_voucher.php` - Lưu voucher

**Upload:**
- `POST /api/upload_image.php` - Upload avatar user
- `POST /api/merchant/upload_image.php` - Upload ảnh merchant

### Cấu trúc thư mục

```
foodbooking/
├── api/              # API endpoints
├── config/           # Cấu hình database, constants
├── core/             # Helper functions
├── database/         # Schema & migrations
├── docs/             # Tài liệu SRS
├── includes/         # Header, footer, functions
├── merchant/         # Giao diện merchant
├── public/           # Assets & uploads
├── sources/          # Tài liệu SRS gốc
├── views/            # Giao diện theo MVC
└── index.php         # Entry point
```

---

## Tính năng nổi bật

✅ Tìm kiếm & lọc nhà hàng theo 63 tỉnh thành Việt Nam  
✅ Hệ thống voucher giảm giá linh hoạt (%, fixed, freeship)  
✅ Upload ảnh với MIME type validation bảo mật  
✅ Quên mật khẩu direct reset (không cần email)  
✅ Quản lý yêu thích & ví voucher  
✅ Dashboard thống kê cho merchant & admin  
✅ Review & rating 5 sao  
✅ Responsive design với TailwindCSS  

---

## Kết quả mong đợi

- Website hoạt động ổn định, đầy đủ chức năng
- Giao diện thân thiện, dễ sử dụng
- Code sạch, có tài liệu SRS đầy đủ
- Database được thiết kế chuẩn, tối ưu
- Bảo mật tốt (password hashing, CSRF protection, MIME validation)
- Có thể demo trực tiếp trên localhost

---

_Hà Nội, tháng 04 năm 2026_
