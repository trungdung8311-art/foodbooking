<?php
// ============================================================
// Cicafood - Cấu hình Database & Session Trung Tâm
// Tất cả các file PHP đều require file này trước tiên
// ============================================================

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình kết nối Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'cicafood');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Cấu hình Site
define('SITE_NAME', 'Cicafood');
define('SITE_URL', 'http://localhost/foodbooking');
define('BRAND_COLOR', '#ee2624');
define('SERVICE_FEE_PERCENT', 2); // 2% phí dịch vụ

// Kết nối PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Trong production, ghi log thay vì echo
    die(json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối database. Vui lòng thử lại sau.'
    ]));
}

// Hàm helper
require_once __DIR__ . '/../includes/functions.php';
?>