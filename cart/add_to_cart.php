<?php
// api/cart/add_to_cart.php - AJAX: Thêm món vào giỏ hàng
require_once __DIR__ . '/../../config/database.php'; // Session đã được khởi tạo trong file này
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$itemId       = (int)($input['item_id'] ?? 0);
$restaurantId = (int)($input['restaurant_id'] ?? 0);
$quantity     = max(1, (int)($input['quantity'] ?? 1));

if (!$itemId || !$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra giỏ hàng có từ nhà hàng khác không
if (!empty($_SESSION['cart']) && isset($_SESSION['cart_restaurant_id'])) {
    if ($_SESSION['cart_restaurant_id'] != $restaurantId) {
        echo json_encode([
            'success'  => false,
            'message'  => 'Giỏ hàng đang có món từ nhà hàng khác. Bạn có muốn xoá và thêm mới?',
            'conflict' => true
        ]);
        exit;
    }
}

// Lấy thông tin món ăn từ DB
$stmt = $conn->prepare("SELECT id, name, price, restaurant_id, is_available FROM menu_items WHERE id = ? AND is_available = 1");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Món ăn không tồn tại hoặc đã hết']);
    exit;
}

// Thêm hoặc cập nhật giỏ hàng trong session
$_SESSION['cart_restaurant_id'] = $restaurantId;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$itemId])) {
    $_SESSION['cart'][$itemId]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$itemId] = [
        'id'        => $item['id'],
        'name'      => $item['name'],
        'price'     => $item['price'],
        'quantity'  => $quantity,
    ];
}

$cartCount = getCartCount();

echo json_encode([
    'success'    => true,
    'message'    => "Đã thêm \"{$item['name']}\" vào giỏ hàng!",
    'cart_count' => $cartCount,
    'cart'       => $_SESSION['cart'],
]);
?>
