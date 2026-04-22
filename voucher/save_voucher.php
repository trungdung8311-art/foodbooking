<?php
// api/voucher/save_voucher.php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu voucher']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$voucher_id = isset($data['voucher_id']) ? (int)$data['voucher_id'] : 0;

if (!$voucher_id) {
    echo json_encode(['success' => false, 'message' => 'Missing voucher_id']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra xem đã lưu chưa
$stmt = $conn->prepare("SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
$stmt->execute([$user_id, $voucher_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã lưu voucher này rồi.']);
    exit;
}

// Check if voucher exists and is valid
$stmtV = $conn->prepare("SELECT id, usage_limit FROM vouchers WHERE id = ? AND end_date >= CURDATE()");
$stmtV->execute([$voucher_id]);
$v = $stmtV->fetch();

if (!$v) {
    echo json_encode(['success' => false, 'message' => 'Voucher không tồn tại hoặc đã hết hạn.']);
    exit;
}

if ($v['usage_limit'] > 0) {
    $stmtUsed = $conn->prepare("SELECT COUNT(*) FROM user_vouchers WHERE voucher_id = ? AND is_used = 1");
    $stmtUsed->execute([$voucher_id]);
    if ($stmtUsed->fetchColumn() >= $v['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'Voucher đã hết lượt sử dụng.']);
        exit;
    }
}

// Luu voucher
$stmtIns = $conn->prepare("INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
$stmtIns->execute([$user_id, $voucher_id]);

echo json_encode(['success' => true, 'message' => 'Đã lưu voucher vào Ví!']);
