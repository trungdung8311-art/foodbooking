<?php
// api/cart/update_cart.php - AJAX: Cập nhật số lượng / xoá món
require_once __DIR__ . '/../../config/database.php'; // Session đã được khởi tạo trong file này
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$itemId  = (int)($input['item_id'] ?? 0);
$action  = $input['action'] ?? 'update'; // update | remove | clear
$quantity = (int)($input['quantity'] ?? 0);

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    unset($_SESSION['cart_restaurant_id']);
    echo json_encode(['success' => true, 'cart_count' => 0, 'cart' => [], 'subtotal' => 0]);
    exit;
}

if (!$itemId) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

if ($action === 'remove' || $quantity <= 0) {
    unset($_SESSION['cart'][$itemId]);
    if (empty($_SESSION['cart'])) {
        unset($_SESSION['cart_restaurant_id']);
    }
} else {
    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId]['quantity'] = $quantity;
    }
}

// Tính lại subtotal
$subtotal = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

echo json_encode([
    'success'    => true,
    'cart_count' => getCartCount(),
    'cart'       => $_SESSION['cart'] ?? [],
    'subtotal'   => $subtotal,
    'subtotal_formatted' => formatPrice($subtotal),
]);
?>
