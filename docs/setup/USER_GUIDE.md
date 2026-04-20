# 📖 HƯỚNG DẪN SỬ DỤNG CICAFOOD

**Phiên bản**: 2.0  
**Ngày cập nhật**: 20/04/2026  
**Dành cho**: Người dùng cuối (Customer, Merchant)

---

## 📋 MỤC LỤC

1. [Truy cập trang web](#1-truy-cập-trang-web)
2. [Đăng ký tài khoản](#2-đăng-ký-tài-khoản)
3. [Đăng nhập](#3-đăng-nhập)
4. [Tìm kiếm quán ăn](#4-tìm-kiếm-quán-ăn)
5. [Đặt món](#5-đặt-món)
6. [Thanh toán](#6-thanh-toán)
7. [Quản lý đơn hàng](#7-quản-lý-đơn-hàng)
8. [Đánh giá quán](#8-đánh-giá-quán)
9. [Quản lý voucher](#9-quản-lý-voucher)
10. [Hướng dẫn Merchant](#10-hướng-dẫn-merchant)

---

## 1. TRUY CẬP TRANG WEB

### 1.1. URL Truy cập

#### Môi trường Local (Development)
```
http://localhost/foodbooking
```

#### Môi trường Production
```
https://cicafood.vn
```

### 1.2. Yêu cầu trình duyệt
- **Chrome** 90+ (Khuyến nghị)
- **Firefox** 88+
- **Safari** 14+
- **Edge** 90+

### 1.3. Trang chủ

Khi truy cập, bạn sẽ thấy:
- 🔍 Thanh tìm kiếm quán ăn
- 📍 Chọn tỉnh thành
- 🏪 Danh sách quán nổi bật
- 🎫 Banner voucher khuyến mãi
- 🍽️ Món ăn bán chạy

---

## 2. ĐĂNG KÝ TÀI KHOẢN

### 2.1. Truy cập trang đăng ký

**Cách 1**: Click nút "Đăng ký" ở góc phải header

**Cách 2**: Truy cập trực tiếp
```
http://localhost/foodbooking/views/auth/register.php
```

### 2.2. Điền thông tin

| Trường | Bắt buộc | Ghi chú |
|--------|----------|---------|
| **Họ tên đầy đủ** | ✅ | Tối thiểu 3 ký tự |
| **Email** | ✅ | Email hợp lệ, chưa được sử dụng |
| **Số điện thoại** | ❌ | 10-11 số |
| **Mật khẩu** | ✅ | Tối thiểu 6 ký tự |
| **Xác nhận mật khẩu** | ✅ | Phải khớp với mật khẩu |
| **Địa chỉ** | ❌ | Địa chỉ giao hàng |

### 2.3. Hoàn tất đăng ký

1. Click nút **"Đăng ký"**
2. Hệ thống tự động đăng nhập
3. Chuyển về trang chủ

### 2.4. Lưu ý
- ⚠️ Email phải duy nhất trong hệ thống
- ⚠️ Mật khẩu nên có chữ hoa, chữ thường, số
- ⚠️ Không chia sẻ mật khẩu cho người khác

---

## 3. ĐĂNG NHẬP

### 3.1. Truy cập trang đăng nhập

**Cách 1**: Click nút "Đăng nhập" ở header

**Cách 2**: Truy cập trực tiếp
```
http://localhost/foodbooking/views/auth/login.php
```

### 3.2. Nhập thông tin

- **Email hoặc Số điện thoại**: Nhập email hoặc SĐT đã đăng ký
- **Mật khẩu**: Nhập mật khẩu
- **Ghi nhớ đăng nhập**: ✅ Tick nếu muốn lưu đăng nhập 30 ngày

### 3.3. Quên mật khẩu

#### Bước 1: Click "Quên mật khẩu?"
- Modal hiện ra

#### Bước 2: Xác minh email
1. Nhập email đã đăng ký
2. Click **"Xác minh"**
3. Hệ thống kiểm tra email

#### Bước 3: Đặt mật khẩu mới
1. Nhập mật khẩu mới (≥ 6 ký tự)
2. Nhập lại để xác nhận
3. Click **"Lưu mật khẩu"**
4. Đăng nhập với mật khẩu mới

### 3.4. Demo Accounts

#### Khách hàng (Customer)
```
Email: an@gmail.com
Password: Password123
```

#### Chủ quán (Merchant)
```
Email: admin@cicafood.vn
Password: Password123
```

---

## 4. TÌM KIẾM QUÁN ĂN

### 4.1. Tìm kiếm cơ bản

#### Từ trang chủ:
1. Nhập tên quán hoặc món ăn vào ô tìm kiếm
2. Chọn tỉnh thành (nếu muốn)
3. Click **"Tìm kiếm"**

#### Ví dụ:
```
Tìm kiếm: "Phở"
Tỉnh thành: "Hà Nội"
→ Hiển thị tất cả quán phở ở Hà Nội
```

### 4.2. Lọc nâng cao

Truy cập trang danh sách quán:
```
http://localhost/foodbooking/views/restaurant/list.php
```

#### Lọc theo:
- 📂 **Danh mục**: Cơm, Phở, Burger, Pizza, Sushi, Trà sữa...
- ⭐ **Đánh giá**: 4.5+, 4.0+, 3.5+
- 💰 **Giá**: Dưới 50k, 50k-100k, 100k-200k, Trên 200k
- 🚚 **Giao hàng**: Freeship, Giao nhanh
- 🏷️ **Khuyến mãi**: Có deal, Có voucher

### 4.3. Sắp xếp

- **Nổi bật**: Quán được đề xuất
- **Đánh giá cao nhất**: Sắp xếp theo rating
- **Gần nhất**: Sắp xếp theo khoảng cách
- **Giao nhanh nhất**: Sắp xếp theo thời gian giao

### 4.4. Xem chi tiết quán

Click vào quán → Xem:
- 📸 Ảnh quán và món ăn
- ⭐ Đánh giá và review
- 🍽️ Menu đầy đủ
- ⏰ Giờ mở cửa
- 📍 Địa chỉ và khoảng cách
- 🎫 Voucher của quán

---

## 5. ĐẶT MÓN

### 5.1. Thêm món vào giỏ hàng

#### Từ trang chi tiết quán:
1. Chọn món ăn
2. Click nút **"+"** hoặc **"Thêm vào giỏ"**
3. Chọn số lượng
4. Món được thêm vào giỏ hàng

### 5.2. Quản lý giỏ hàng

#### Xem giỏ hàng:
- Click icon giỏ hàng ở header
- Hoặc truy cập: `/views/order/checkout.php`

#### Trong giỏ hàng có thể:
- ➕ Tăng số lượng món
- ➖ Giảm số lượng món
- 🗑️ Xóa món khỏi giỏ
- 🎫 Áp dụng voucher

### 5.3. Áp dụng voucher

#### Cách 1: Nhập mã voucher
1. Nhập mã vào ô "Mã giảm giá"
2. Click **"Áp dụng"**
3. Giảm giá được tính tự động

#### Cách 2: Chọn từ ví voucher
1. Click **"Chọn voucher"**
2. Chọn voucher từ danh sách
3. Click **"Sử dụng"**

#### Loại voucher:
- 💰 **Giảm giá**: Giảm % hoặc số tiền cố định
- 🚚 **Freeship**: Miễn phí vận chuyển
- 🎁 **Combo**: Ưu đãi combo món

### 5.4. Lưu ý khi đặt món

- ⚠️ Đơn tối thiểu: Kiểm tra giá trị đơn tối thiểu của quán
- ⚠️ Giờ mở cửa: Chỉ đặt trong giờ mở cửa
- ⚠️ Khu vực giao: Kiểm tra quán có giao đến địa chỉ của bạn không

---

## 6. THANH TOÁN

### 6.1. Truy cập trang thanh toán

Từ giỏ hàng, click **"Thanh toán"**
```
http://localhost/foodbooking/views/order/checkout.php
```

### 6.2. Điền thông tin giao hàng

| Trường | Bắt buộc | Ghi chú |
|--------|----------|---------|
| **Người nhận** | ✅ | Họ tên người nhận |
| **Số điện thoại** | ✅ | SĐT liên hệ |
| **Địa chỉ giao hàng** | ✅ | Địa chỉ chi tiết |
| **Ghi chú** | ❌ | Yêu cầu đặc biệt |

### 6.3. Chọn phương thức thanh toán

#### 💵 COD (Thanh toán khi nhận hàng)
- Thanh toán bằng tiền mặt
- Trả tiền cho shipper khi nhận hàng
- **Phí**: Miễn phí

#### 💳 MoMo
- Thanh toán qua ví MoMo
- Quét QR hoặc nhập SĐT
- **Phí**: 0đ

#### 🏦 VNPay
- Thanh toán qua thẻ ATM/Visa/Mastercard
- Chuyển hướng sang VNPay
- **Phí**: 0đ

#### 🏧 Chuyển khoản ngân hàng
- Chuyển khoản trực tiếp
- Xác nhận sau khi chuyển
- **Phí**: 0đ

### 6.4. Xác nhận đơn hàng

#### Kiểm tra lại:
- ✅ Món ăn và số lượng
- ✅ Địa chỉ giao hàng
- ✅ Phương thức thanh toán
- ✅ Tổng tiền

#### Click "Đặt hàng"

### 6.5. Sau khi đặt hàng

1. **Nhận mã đơn hàng**: VD: `CF123456`
2. **Email xác nhận**: Gửi về email đã đăng ký
3. **Theo dõi đơn**: Vào "Đơn hàng của tôi"

---

## 7. QUẢN LÝ ĐƠN HÀNG

### 7.1. Xem danh sách đơn hàng

Truy cập:
```
http://localhost/foodbooking/views/order/history.php
```

Hoặc: Click **"Đơn hàng của tôi"** trong menu user

### 7.2. Trạng thái đơn hàng

| Trạng thái | Mô tả | Hành động |
|------------|-------|-----------|
| 🕐 **Chờ xác nhận** | Đơn mới tạo, chờ quán xác nhận | Có thể hủy |
| ✅ **Đã xác nhận** | Quán đã nhận đơn | Không thể hủy |
| 🔥 **Đang chuẩn bị** | Quán đang nấu món | Không thể hủy |
| 🚚 **Đang giao** | Shipper đang giao | Không thể hủy |
| 🎉 **Hoàn thành** | Đã giao thành công | Có thể đánh giá |
| ❌ **Đã hủy** | Đơn bị hủy | - |

### 7.3. Hủy đơn hàng

#### Điều kiện:
- Chỉ hủy được khi trạng thái **"Chờ xác nhận"**
- Sau khi quán xác nhận không thể hủy

#### Cách hủy:
1. Vào chi tiết đơn hàng
2. Click **"Hủy đơn"**
3. Chọn lý do hủy
4. Xác nhận hủy

### 7.4. Theo dõi đơn hàng

#### Xem chi tiết:
- 📦 Danh sách món đã đặt
- 💰 Chi tiết thanh toán
- 📍 Địa chỉ giao hàng
- ⏰ Thời gian dự kiến
- 📞 Liên hệ quán/shipper

---

## 8. ĐÁNH GIÁ QUÁN

### 8.1. Khi nào có thể đánh giá?

- ✅ Sau khi đơn hàng **"Hoàn thành"**
- ✅ Mỗi đơn chỉ đánh giá 1 lần

### 8.2. Cách đánh giá

#### Bước 1: Vào đơn hàng đã hoàn thành
```
Đơn hàng của tôi → Chọn đơn → Click "Đánh giá"
```

#### Bước 2: Cho điểm
- ⭐⭐⭐⭐⭐ 5 sao: Tuyệt vời
- ⭐⭐⭐⭐ 4 sao: Tốt
- ⭐⭐⭐ 3 sao: Bình thường
- ⭐⭐ 2 sao: Tệ
- ⭐ 1 sao: Rất tệ

#### Bước 3: Viết nhận xét
- Mô tả trải nghiệm
- Chất lượng món ăn
- Thái độ phục vụ
- Tốc độ giao hàng

#### Bước 4: Upload ảnh (tùy chọn)
- Tối đa 5 ảnh
- Định dạng: JPG, PNG, WEBP
- Dung lượng: Tối đa 5MB/ảnh

#### Bước 5: Gửi đánh giá
Click **"Gửi đánh giá"**

### 8.3. Chỉnh sửa đánh giá

- Có thể chỉnh sửa trong vòng 24 giờ
- Sau 24 giờ không thể sửa

---

## 9. QUẢN LÝ VOUCHER

### 9.1. Lấy voucher

#### Cách 1: Từ trang chủ
1. Xem banner voucher
2. Click **"🎁 Lấy mã"**
3. Voucher lưu vào ví

#### Cách 2: Từ trang quán
1. Vào chi tiết quán
2. Xem voucher của quán
3. Click **"Lấy mã"**

#### Cách 3: Nhập mã thủ công
1. Vào **"Ví voucher"**
2. Click **"Nhập mã"**
3. Nhập mã voucher
4. Click **"Lưu"**

### 9.2. Xem ví voucher

Truy cập:
```
http://localhost/foodbooking/views/user/vouchers.php
```

#### 3 Tab:
- **Khả dụng**: Voucher còn hạn, chưa dùng
- **Đã dùng**: Voucher đã sử dụng
- **Hết hạn**: Voucher quá hạn

### 9.3. Sử dụng voucher

#### Khi checkout:
1. Chọn voucher từ danh sách
2. Hoặc nhập mã voucher
3. Click **"Áp dụng"**
4. Giảm giá tự động

#### Lưu ý:
- ⚠️ Mỗi đơn chỉ dùng 1 voucher giảm giá
- ⚠️ Có thể dùng thêm 1 voucher freeship
- ⚠️ Kiểm tra điều kiện áp dụng

---

## 10. HƯỚNG DẪN MERCHANT

### 10.1. Đăng ký Merchant

#### Yêu cầu:
- Đã có tài khoản customer
- Có giấy phép kinh doanh
- Có địa chỉ quán cụ thể

#### Cách đăng ký:
1. Đăng nhập tài khoản
2. Vào **"Đăng ký Merchant"**
3. Điền thông tin quán
4. Upload giấy tờ
5. Chờ admin duyệt (1-3 ngày)

### 10.2. Truy cập Merchant Dashboard

```
http://localhost/foodbooking/merchant/index.php
```

Hoặc: Click **"Quản lý quán"** trong menu

### 10.3. Quản lý thông tin quán

#### Vào: Merchant → Cài đặt
```
http://localhost/foodbooking/merchant/settings.php
```

Cập nhật:
- 📸 Ảnh logo và ảnh bìa
- 📝 Tên quán, mô tả
- 📍 Địa chỉ, tỉnh thành
- 📞 Số điện thoại
- ⏰ Giờ mở cửa/đóng cửa
- 💰 Phí giao hàng, đơn tối thiểu

### 10.4. Quản lý Menu

#### Vào: Merchant → Menu
```
http://localhost/foodbooking/merchant/menu.php
```

##### Thêm món mới:
1. Click **"Thêm món"**
2. Điền thông tin:
   - Tên món
   - Mô tả
   - Giá
   - Danh mục
   - Upload ảnh
3. Click **"Lưu"**

##### Sửa món:
1. Click icon **"✏️"** trên món
2. Chỉnh sửa thông tin
3. Click **"Cập nhật"**

##### Xóa món:
1. Click icon **"🗑️"** trên món
2. Xác nhận xóa

##### Bật/Tắt món:
- Toggle switch để bật/tắt món
- Món tắt sẽ không hiển thị cho khách

### 10.5. Quản lý Đơn hàng

#### Vào: Merchant → Đơn hàng
```
http://localhost/foodbooking/merchant/orders.php
```

##### Đơn mới:
1. Nhận thông báo đơn mới
2. Xem chi tiết đơn
3. Click **"Xác nhận"** hoặc **"Từ chối"**

##### Cập nhật trạng thái:
1. Chọn đơn hàng
2. Chọn trạng thái mới:
   - Đang chuẩn bị
   - Đang giao
   - Hoàn thành
3. Click **"Cập nhật"**

##### Lọc đơn:
- Theo trạng thái
- Theo ngày
- Theo mã đơn

### 10.6. Tạo Voucher

#### Vào: Merchant → Voucher
```
http://localhost/foodbooking/merchant/vouchers.php
```

##### Tạo voucher mới:
1. Click **"Thêm voucher"**
2. Điền thông tin:
   - **Mã code**: VD: SUMMER20
   - **Tên**: Giảm 20% mùa hè
   - **Loại**: Giảm %, Giảm tiền, Freeship
   - **Giá trị**: 20% hoặc 50.000đ
   - **Đơn tối thiểu**: 100.000đ
   - **Lượt sử dụng**: 100 lượt
   - **Ngày hết hạn**: 31/12/2026
3. Click **"Lưu mã"**

##### Quản lý voucher:
- Xem thống kê đã dùng/tổng lượt
- Sửa voucher
- Xóa voucher
- Bật/Tắt voucher

### 10.7. Xem Thống kê

#### Vào: Merchant → Dashboard
```
http://localhost/foodbooking/merchant/index.php
```

Xem:
- 💰 Doanh thu hôm nay/tuần/tháng
- 📦 Số đơn hàng
- ⭐ Đánh giá trung bình
- 📊 Biểu đồ doanh thu
- 🍽️ Món bán chạy nhất

---

## 📞 HỖ TRỢ

### Liên hệ

- **Email**: support@cicafood.vn
- **Hotline**: 1900-xxxx
- **Facebook**: fb.com/cicafood
- **Zalo**: 0901234567

### Giờ làm việc

- **Thứ 2 - Thứ 6**: 8:00 - 18:00
- **Thứ 7**: 8:00 - 12:00
- **Chủ nhật**: Nghỉ

### FAQ

**Q: Tôi quên mật khẩu, làm sao?**  
A: Click "Quên mật khẩu?" ở trang đăng nhập, làm theo hướng dẫn.

**Q: Tôi muốn hủy đơn hàng?**  
A: Chỉ hủy được khi đơn ở trạng thái "Chờ xác nhận".

**Q: Voucher không áp dụng được?**  
A: Kiểm tra điều kiện: đơn tối thiểu, hạn sử dụng, số lượt còn lại.

**Q: Làm sao để trở thành Merchant?**  
A: Vào menu → "Đăng ký Merchant" → Điền form → Chờ duyệt.

**Q: Phí dịch vụ là bao nhiêu?**  
A: 2% trên tổng giá trị đơn hàng.

---

## 🎯 TIPS & TRICKS

### Cho Khách hàng

1. **Lưu voucher trước**: Lưu voucher vào ví trước khi hết lượt
2. **Đặt combo**: Đặt combo thường rẻ hơn đặt lẻ
3. **Đánh giá để nhận điểm**: Đánh giá sau mỗi đơn để tích điểm
4. **Theo dõi deal**: Check trang chủ mỗi ngày để không bỏ lỡ deal
5. **Đặt trước giờ cao điểm**: Đặt trước 11h hoặc 18h để tránh chờ lâu

### Cho Merchant

1. **Cập nhật menu thường xuyên**: Thêm món mới để thu hút khách
2. **Tạo voucher hấp dẫn**: Voucher giúp tăng đơn hàng
3. **Phản hồi review**: Reply review để tăng uy tín
4. **Cập nhật trạng thái nhanh**: Khách hàng thích được cập nhật
5. **Chụp ảnh món đẹp**: Ảnh đẹp tăng tỷ lệ đặt hàng

---

**Chúc bạn có trải nghiệm tuyệt vời với Cicafood! 🍔🍕🍜**

---

**Cập nhật**: 20/04/2026  
**Phiên bản**: 2.0  
**Tác giả**: Cicafood Team
