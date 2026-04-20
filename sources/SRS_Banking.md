## SRS – Module Banking (Thanh toán)

### 1. Mô tả

Xử lý thanh toán đơn hàng thông qua các cổng thanh toán online.

### 2. Chức năng

* Chọn phương thức thanh toán
* Kết nối cổng thanh toán (VNPay, MoMo, ZaloPay)
* Xác nhận thanh toán
* Cập nhật trạng thái đơn hàng

### 3. Actor

* User

### 4. Luồng chính

1. User chọn thanh toán khi đặt hàng
2. Chọn phương thức (VNPay/MoMo/...)
3. Hệ thống chuyển đến cổng thanh toán
4. User thực hiện thanh toán
5. Hệ thống nhận kết quả và cập nhật đơn hàng

### 5. Luồng phụ

* Thanh toán thất bại → hiển thị lỗi và cho phép thử lại
* Người dùng hủy thanh toán → quay lại trang đơn hàng

### 6. Ràng buộc

* Phải kết nối internet
* Sử dụng API từ bên thứ ba
* Đảm bảo bảo mật thông tin thanh toán
