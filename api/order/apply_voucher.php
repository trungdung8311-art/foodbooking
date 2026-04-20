<?php
// api/order/apply_voucher.php - AJAX: Áp dụng voucher
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$code     = strtoupper(trim($input['code'] ?? ''));
$subtotal = (int)($input['subtotal'] ?? 0);
$type     = $input['type'] ?? 'discount'; // discount | ship
$restaurantId = (int)($_SESSION['cart_restaurant_id'] ?? 0);

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã voucher']);
    exit;
}

$stmt = $conn->prepare("
    SELECT * FROM vouchers 
    WHERE code = ? 
      AND end_date >= CURDATE()
      AND (restaurant_id IS NULL OR restaurant_id = ?)
");
$stmt->execute([$code, $restaurantId]);
$voucher = $stmt->fetch();

if (!$voucher) {
    echo json_encode(['success' => false, 'message' => 'Mã voucher không hợp lệ hoặc không áp dụng cho quán này']);
    exit;
}

if ($voucher['usage_limit'] > 0) {
    $stmtUsed = $conn->prepare("SELECT COUNT(*) FROM user_vouchers WHERE voucher_id = ? AND is_used = 1");
    $stmtUsed->execute([$voucher['id']]);
    if ($stmtUsed->fetchColumn() >= $voucher['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'Voucher đã hết lượt sử dụng']);
        exit;
    }
}

if ($subtotal < $voucher['min_order']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Đơn hàng tối thiểu ' . formatPrice($voucher['min_order']) . ' để dùng voucher này'
    ]);
    exit;
}

// Kiểm tra loại voucher
if ($type === 'ship' && $voucher['type'] !== 'freeship') {
    echo json_encode(['success' => false, 'message' => 'Mã này không phải voucher freeship']);
    exit;
}

if ($type === 'discount' && $voucher['type'] === 'freeship') {
    // Nếu người dùng nhập freeship vào ô discount, chuyển tự động
    $type = 'ship';
}

// Tính giá trị giảm
$discountAmount = 0;
if ($voucher['type'] === 'percent') {
    $discountAmount = (int)($subtotal * $voucher['value'] / 100);
    if ($voucher['max_discount']) {
        $discountAmount = min($discountAmount, $voucher['max_discount']);
    }
} elseif ($voucher['type'] === 'fixed') {
    $discountAmount = min($voucher['value'], $subtotal);
} elseif ($voucher['type'] === 'freeship') {
    $discountAmount = $voucher['max_discount'] ?? 30000; // Giảm phí ship tối đa
}

echo json_encode([
    'success'          => true,
    'message'          => "Áp dụng mã \"{$code}\" thành công!",
    'voucher'          => $voucher,
    'discount_amount'  => $discountAmount,
    'discount_formatted' => formatPrice($discountAmount),
    'type'             => $voucher['type'],
    'apply_type'       => $type,
]);
?>
