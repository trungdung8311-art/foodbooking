<?php
// api/review/submit_rating.php – AJAX: Gửi đánh giá sao cho nhà hàng
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$data          = json_decode(file_get_contents('php://input'), true);
$restaurantId  = (int)($data['restaurant_id'] ?? 0);
$rating        = (int)($data['rating'] ?? 0);
$comment       = trim($data['comment'] ?? '');
$userId        = (int)$_SESSION['user_id'];

if (!$restaurantId || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

// Kiểm tra user đã có đơn hàng hoàn thành tại quán này chưa
$stmtOrder = $conn->prepare("
    SELECT id FROM orders
    WHERE user_id = ? AND restaurant_id = ? AND status = 'completed'
    LIMIT 1
");
$stmtOrder->execute([$userId, $restaurantId]);
$completedOrder = $stmtOrder->fetch();

if (!$completedOrder) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần hoàn thành ít nhất 1 đơn hàng tại quán này mới có thể đánh giá.'
    ]);
    exit;
}

$orderId = $completedOrder['id'];

// Kiểm tra đã đánh giá chưa (per restaurant, not per order)
$stmtCheck = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND restaurant_id = ?");
$stmtCheck->execute([$userId, $restaurantId]);
if ($stmtCheck->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá quán này rồi.']);
    exit;
}

// Lưu đánh giá
try {
    $stmtIns = $conn->prepare("
        INSERT INTO reviews (user_id, restaurant_id, order_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtIns->execute([$userId, $restaurantId, $orderId, $rating, $comment]);

    // Cập nhật rating trung bình của nhà hàng
    $stmtAvg = $conn->prepare("
        SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
        FROM reviews
        WHERE restaurant_id = ? AND is_visible = 1
    ");
    $stmtAvg->execute([$restaurantId]);
    $avgData = $stmtAvg->fetch();

    $newAvg   = round((float)$avgData['avg_rating'], 1);
    $newTotal = (int)$avgData['total'];

    $stmtUp = $conn->prepare("
        UPDATE restaurants SET rating = ?, total_reviews = ? WHERE id = ?
    ");
    $stmtUp->execute([$newAvg, $newTotal, $restaurantId]);

    // Lấy tên user để hiển thị
    $userName = $_SESSION['user_name'] ?? 'Người dùng';

    echo json_encode([
        'success'      => true,
        'message'      => 'Cảm ơn bạn đã đánh giá!',
        'new_rating'   => $newAvg,
        'total_reviews'=> $newTotal,
        'review' => [
            'user_name' => htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'),
            'rating'    => $rating,
            'comment'   => htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'),
            'created_at'=> date('d/m/Y H:i')
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi lưu đánh giá. Vui lòng thử lại.']);
}
