## SRS – Module Search (Tìm kiếm)

### 1. Mô tả

Cho phép người dùng tìm kiếm món ăn theo tên hoặc danh mục.

### 2. Chức năng

* Tìm kiếm món ăn theo từ khóa
* Lọc theo danh mục
* Hiển thị kết quả tìm kiếm

### 3. Actor

* User

### 4. Luồng chính

1. User nhập từ khóa vào ô tìm kiếm
2. Hệ thống xử lý và truy vấn dữ liệu
3. Hiển thị danh sách kết quả phù hợp

### 5. Luồng phụ

* Không có kết quả → hiển thị thông báo “Không tìm thấy món ăn”

### 6. Ràng buộc

* Tìm kiếm không phân biệt chữ hoa/thường
* Thời gian phản hồi < 3 giây
