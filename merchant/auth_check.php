<?php
// ============================================================
// merchant/ - Hàm kiểm soát quyền Merchant
// Dùng chung cho toàn bộ trang merchant/
// ============================================================

/**
 * Yêu cầu role merchant hoặc admin - redirect nếu không đủ quyền
 */
function requireMerchant(): void {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: /foodbooking/views/auth/login.php');
        exit;
    }
    if (!in_array($_SESSION['user_role'] ?? '', ['merchant', 'admin'])) {
        header('Location: /foodbooking/');
        exit;
    }
}

/**
 * Lấy thông tin restaurant của merchant đang đăng nhập
 */
function getMerchantRestaurant(PDO $conn): ?array {
    $stmt = $conn->prepare("SELECT * FROM restaurants WHERE owner_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
