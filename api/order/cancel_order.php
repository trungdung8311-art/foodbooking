<?php
// api/order/cancel_order.php - AJAX: Huỷ đơn hàng
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Chỉ hủy được đơn đang pending
$stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    exit;
}
if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Chỉ có thể huỷ đơn hàng đang chờ xác nhận']);
    exit;
}

$stmtUpdate = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
$stmtUpdate->execute([$orderId, $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được huỷ thành công']);
?>
