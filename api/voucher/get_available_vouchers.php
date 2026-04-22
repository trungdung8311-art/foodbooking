<?php
/**
 * API: Lấy danh sách voucher khả dụng cho nhà hàng
 * Trả về tất cả voucher của nhà hàng + voucher toàn sàn
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Lấy tham số
$restaurantId = (int)($_GET['restaurant_id'] ?? 0);
$subtotal = (int)($_GET['subtotal'] ?? 0);

if (!$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin nhà hàng']);
    exit;
}

try {
    $userId = (int)$_SESSION['user_id'];
    
    // Lấy voucher của nhà hàng + voucher toàn sàn (restaurant_id = NULL)
    // Chỉ lấy voucher còn hạn và đang active
    $stmt = $conn->prepare("
        SELECT v.*, 
               r.name AS restaurant_name,
               uv.id AS user_voucher_id,
               uv.is_used
        FROM vouchers v
        LEFT JOIN restaurants r ON v.restaurant_id = r.id
        LEFT JOIN user_vouchers uv ON (v.id = uv.voucher_id AND uv.user_id = ?)
        WHERE v.is_active = 1 
          AND v.end_date >= CURDATE()
          AND (v.restaurant_id = ? OR v.restaurant_id IS NULL)
        ORDER BY 
          CASE WHEN v.restaurant_id = ? THEN 0 ELSE 1 END,
          v.min_order ASC
    ");
    
    $stmt->execute([$userId, $restaurantId, $restaurantId]);
    $vouchers = $stmt->fetchAll();
    
    // Phân loại voucher
    $result = [];
    foreach ($vouchers as $v) {
        $isValid = ($subtotal >= $v['min_order']);
        $isSaved = !empty($v['user_voucher_id']);
        $isUsed = (bool)($v['is_used'] ?? false);
        
        // Tính giá trị giảm giá
        $discountAmount = 0;
        if ($isValid) {
            if ($v['type'] === 'percent') {
                $discountAmount = min(
                    (int)($subtotal * $v['value'] / 100),
                    $v['max_discount'] ?? 999999
                );
            } elseif ($v['type'] === 'fixed') {
                $discountAmount = min($v['value'], $subtotal);
            } elseif ($v['type'] === 'freeship') {
                $discountAmount = $v['max_discount'] ?? 30000;
            }
        }
        
        $result[] = [
            'id' => $v['id'],
            'code' => $v['code'],
            'name' => $v['name'],
            'description' => $v['description'],
            'type' => $v['type'],
            'value' => $v['value'],
            'max_discount' => $v['max_discount'],
            'min_order' => $v['min_order'],
            'end_date' => $v['end_date'],
            'restaurant_id' => $v['restaurant_id'],
            'restaurant_name' => $v['restaurant_name'],
            'is_valid' => $isValid,
            'is_saved' => $isSaved,
            'is_used' => $isUsed,
            'discount_amount' => $discountAmount,
            'discount_formatted' => formatPrice($discountAmount),
            'min_order_formatted' => formatPrice($v['min_order']),
            'reason' => !$isValid ? "Đơn hàng tối thiểu " . formatPrice($v['min_order']) : null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'vouchers' => $result,
        'subtotal' => $subtotal,
        'subtotal_formatted' => formatPrice($subtotal)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>
