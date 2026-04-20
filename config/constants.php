<?php
// ============================================================
// Cicafood - Site Constants
// ============================================================

// Site Information
define('SITE_NAME', 'Cicafood');
define('SITE_URL', 'http://localhost/foodbooking');
define('SITE_EMAIL', 'support@cicafood.vn');

// Brand Colors
define('BRAND_COLOR', '#ee2624');
define('BRAND_COLOR_DARK', '#c41e1c');
define('BRAND_COLOR_LIGHT', '#ff4542');

// Business Settings
define('SERVICE_FEE_PERCENT', 2); // 2% phí dịch vụ
define('MIN_ORDER_AMOUNT', 0); // Đơn tối thiểu (VND)
define('DEFAULT_DELIVERY_FEE', 15000); // Phí ship mặc định
define('DEFAULT_DELIVERY_TIME', 30); // Thời gian giao hàng mặc định (phút)

// Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/');

// Session Settings
define('SESSION_LIFETIME', 24 * 3600); // 24 giờ
define('REMEMBER_ME_LIFETIME', 30 * 24 * 3600); // 30 ngày

// Pagination
define('ITEMS_PER_PAGE', 12);
define('RESTAURANTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Default Values
define('DEFAULT_AVATAR', 'default_avatar.png');
define('DEFAULT_RESTAURANT_IMAGE', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80');
define('DEFAULT_MENU_ITEM_IMAGE', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80');

// Payment Methods
define('PAYMENT_METHODS', [
    'cod' => 'Thanh toán khi nhận hàng',
    'momo' => 'Ví MoMo',
    'vnpay' => 'VNPay',
    'bank' => 'Chuyển khoản ngân hàng'
]);

// Order Status
define('ORDER_STATUS', [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'preparing' => 'Đang chuẩn bị',
    'delivering' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
]);

// User Roles
define('USER_ROLES', [
    'customer' => 'Khách hàng',
    'merchant' => 'Chủ quán',
    'admin' => 'Quản trị viên'
]);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error Reporting (Development)
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
