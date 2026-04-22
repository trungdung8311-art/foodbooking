<?php
/**
 * API: Tạo URL thanh toán VNPAY
 * Nhận order_id, tạo URL redirect sang VNPAY
 */

require_once __DIR__ . '/../../config/database.php'; // Session đã được khởi tạo trong file này
require_once __DIR__ . '/../../config/vnpay.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$orderId = (int)($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
    exit;
}

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
    exit;
}

// Kiểm tra đơn hàng đã thanh toán chưa
if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng đã được xử lý']);
    exit;
}

// Tạo URL thanh toán
$amount = $order['total_amount'];
$orderInfo = "Thanh toan don hang #{$order['order_code']} - Cicafood";
$ipAddr = $_SERVER['REMOTE_ADDR'];

try {
    $paymentUrl = createVNPayPaymentUrl($orderId, $amount, $orderInfo, $ipAddr);
    
    // Lưu log
    $stmtLog = $conn->prepare("
        INSERT INTO payment_logs (order_id, payment_method, amount, status, request_data, created_at)
        VALUES (?, 'vnpay', ?, 'pending', ?, NOW())
    ");
    $stmtLog->execute([$orderId, $amount, json_encode(['url' => $paymentUrl])]);
    
    echo json_encode([
        'success' => true,
        'payment_url' => $paymentUrl,
        'message' => 'Đang chuyển hướng đến VNPAY...'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi tạo thanh toán: ' . $e->getMessage()
    ]);
}
?>
