# 💈 Website đặt đồ ăn CicaFood

> **Đề tài thực tập tốt nghiệp** — Lớp LTWNC-D18CNPM2

Dự án xây dựng website đặt đồ ăn trực tuyến, cho phép người dùng lựa chọn món ăn, đặt hàng và thanh toán online.
---

## 👥 Thành viên nhóm

| STT | Họ và tên | MSSV | Vai trò |
|---|---|---|---|
| 1 | Nguyễn Bá Tuấn Anh | 23810310109 | Nhóm trưởng |
| 2 | Nguyễn Bá Tuấn Anh | 23810310100 | Thành viên |
| 3 | Bùi Trung Dũng | 23810310099 | Thành viên |

---

## 🚀 Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Frontend | HTML, CSS, JavaScript, Bootstrap 5 |
| Backend | PHP thuần |
| Database | MySQL |
| Thanh toán | VNPay / ZaloPay / Momo |

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
