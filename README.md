# 📌 DỰ ÁN: WEBSITE ĐẶT ĐỒ ĂN

## 👥 Thành viên nhóm

* Thành viên 1: Nguyễn Bá Tuấn Anh
* Thành viên 2: Nguyễn Trần Xuân Bắc
* Thành viên 3: Bùi Trung Dũng

---

## 📖 Giới thiệu

Dự án xây dựng website đặt đồ ăn trực tuyến, cho phép người dùng lựa chọn món ăn, đặt hàng và thanh toán online.
Hệ thống gồm 2 vai trò chính: Admin và User.

---

## 📂 Tài liệu SRS

- [SRS User](./sources/SRS_User.md)
- [SRS Admin](./sources/SRS_Admin.md)
- [SRS Login](./sources/SRS_Login.md)
- [SRS Order](./sources/SRS_Order.md)
- [SRS Menu](./sources/SRS_Menu.md)
- [SRS Search](./sources/SRS_Search.md)
- [SRS Banking](./sources/SRS_Banking.md)
---

## ⚙️ Chức năng chính

### 🔹 Admin

* Quản lý người dùng
* Quản lý danh mục món ăn
* Quản lý món ăn
* Quản lý đơn hàng
* Thống kê / báo cáo

### 🔹 User

* Đăng ký / đăng nhập
* Xem món ăn
* Giỏ hàng
* Đặt món
* Thanh toán online
* Theo dõi đơn hàng

---

## Sơ đồ cơ sở dữ liệu (Database Diagram)

```mermaid
erDiagram
    USERS {
        int id
        string name
        string email
        string password
        string role
    }

    CATEGORIES {
        int id
        string name
    }

    FOODS {
        int id
        string name
        float price
        string image
        int category_id
    }

    ORDERS {
        int id
        int user_id
        float total
        string status
        datetime created_at
    }

    ORDER_DETAILS {
        int id
        int order_id
        int food_id
        int quantity
        float price
    }

    PAYMENTS {
        int id
        int order_id
        string method
        string status
        datetime paid_at
    }

    PROMOTIONS {
        int id
        string name
        float discount
    }

    USERS ||--o{ ORDERS : places
    ORDERS ||--o{ ORDER_DETAILS : contains
    FOODS ||--o{ ORDER_DETAILS : included_in
    CATEGORIES ||--o{ FOODS : has
    ORDERS ||--|| PAYMENTS : has

## 🔗 Repository

Link GitHub: https://github.com/tenban/website-dat-do-an
