<?php
// api/restaurant/toggle_favorite.php – AJAX toggle yêu thích nhà hàng
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'login_required' => true, 'message' => 'Vui lòng đăng nhập để thực hiện']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$data          = json_decode(file_get_contents('php://input'), true);
$restaurantId  = (int)($data['restaurant_id'] ?? 0);

if (!$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'Missing restaurant_id']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND restaurant_id = ?");
$stmt->execute([$userId, $restaurantId]);
$exist = $stmt->fetch();

if ($exist) {
    // Bỏ yêu thích
    $conn->prepare("DELETE FROM favorites WHERE id = ?")->execute([$exist['id']]);
    echo json_encode(['success' => true, 'favorited' => false, 'is_favorite' => false]);
} else {
    // Thêm yêu thích
    $conn->prepare("INSERT INTO favorites (user_id, restaurant_id) VALUES (?, ?)")->execute([$userId, $restaurantId]);
    echo json_encode(['success' => true, 'favorited' => true, 'is_favorite' => true]);
}
