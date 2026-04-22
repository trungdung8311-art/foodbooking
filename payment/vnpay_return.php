<?php
/**
 * VNPAY Return URL
 * Xử lý callback từ VNPAY sau khi khách hàng thanh toán
 */

require_once __DIR__ . '/../../config/database.php'; // Session đã được khởi tạo trong file này
require_once __DIR__ . '/../../config/vnpay.php';

// Lấy tất cả tham số từ VNPAY
$vnp_Params = $_GET;

// Validate chữ ký
if (!validateVNPaySignature($vnp_Params)) {
    setFlash('error', 'Chữ ký không hợp lệ. Giao dịch có thể bị giả mạo!');
    header('Location: /foodbooking/views/order/history.php');
    exit;
}

// Lấy thông tin giao dịch
$vnp_TxnRef = $vnp_Params['vnp_TxnRef']; // Order ID
$vnp_Amount = $vnp_Params['vnp_Amount'] / 100; // Chia 100 để về VND
$vnp_ResponseCode = $vnp_Params['vnp_ResponseCode'];
$vnp_TransactionNo = $vnp_Params['vnp_TransactionNo'] ?? '';
$vnp_BankCode = $vnp_Params['vnp_BankCode'] ?? '';
$vnp_PayDate = $vnp_Params['vnp_PayDate'] ?? '';

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$vnp_TxnRef]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Đơn hàng không tồn tại!');
    header('Location: /foodbooking/views/order/history.php');
    exit;
}

// Kiểm tra số tiền
if ($vnp_Amount != $order['total_amount']) {
    setFlash('error', 'Số tiền thanh toán không khớp!');
    header('Location: /foodbooking/views/order/history.php');
    exit;
}

$conn->beginTransaction();

try {
    // Xử lý theo response code
    if ($vnp_ResponseCode == '00') {
        // Thanh toán thành công
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'confirmed',
                payment_status = 'paid',
                payment_transaction_id = ?,
                payment_date = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$vnp_TransactionNo, $vnp_TxnRef]);
        
        // Lưu log thanh toán
        $stmtLog = $conn->prepare("
            INSERT INTO payment_logs (order_id, payment_method, amount, status, transaction_id, response_code, response_data, created_at)
            VALUES (?, 'vnpay', ?, 'success', ?, ?, ?, NOW())
        ");
        $stmtLog->execute([
            $vnp_TxnRef,
            $vnp_Amount,
            $vnp_TransactionNo,
            $vnp_ResponseCode,
            json_encode($vnp_Params)
        ]);
        
        $conn->commit();
        
        setFlash('success', 'Thanh toán thành công! Đơn hàng của bạn đang được xử lý.');
        header('Location: /foodbooking/views/order/success.php?id=' . $vnp_TxnRef);
        exit;
        
    } else {
        // Thanh toán thất bại
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = 'failed',
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$vnp_TxnRef]);
        
        // Lưu log lỗi
        $stmtLog = $conn->prepare("
            INSERT INTO payment_logs (order_id, payment_method, amount, status, response_code, response_data, error_message, created_at)
            VALUES (?, 'vnpay', ?, 'failed', ?, ?, ?, NOW())
        ");
        $stmtLog->execute([
            $vnp_TxnRef,
            $vnp_Amount,
            $vnp_ResponseCode,
            json_encode($vnp_Params),
            getVNPayResponseMessage($vnp_ResponseCode)
        ]);
        
        $conn->commit();
        
        $errorMsg = getVNPayResponseMessage($vnp_ResponseCode);
        setFlash('error', 'Thanh toán thất bại: ' . $errorMsg);
        header('Location: /foodbooking/views/order/checkout.php');
        exit;
    }
    
} catch (Exception $e) {
    $conn->rollBack();
    setFlash('error', 'Lỗi xử lý thanh toán. Vui lòng liên hệ hỗ trợ.');
    header('Location: /foodbooking/views/order/history.php');
    exit;
}
?>
