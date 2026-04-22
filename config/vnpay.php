<?php
/**
 * ============================================================
 * VNPAY Payment Gateway Configuration
 * Tài liệu: https://sandbox.vnpayment.vn/apis/docs/huong-dan-tich-hop/
 * ============================================================
 */

// ============================================================
// BƯỚC 1: CẤU HÌNH VNPAY SANDBOX (DEMO)
// ============================================================
// Thông tin này lấy từ VNPAY Sandbox - không cần đăng ký
define('VNPAY_TMN_CODE', '6Z96T36Q');
define('VNPAY_HASH_SECRET', 'WMIS8RI6B2OGOKLQ746LSRZZZQL2QHZW');
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

// ============================================================
// BƯỚC 2: CẤU HÌNH RETURN URL
// ============================================================
//
define('VNPAY_RETURN_URL', SITE_URL . '/api/payment/vnpay_return.php');

// ============================================================
// THÔNG TIN THẺ TEST (VNPAY SANDBOX)
// ============================================================
/*
Ngân hàng: NCB
Số thẻ: 9704198526191432198
Tên chủ thẻ: NGUYEN VAN A
Ngày phát hành: 07/15
Mật khẩu OTP: 123456

HƯỚNG DẪN TEST:
1. Đảm bảo đã cấu hình ngrok hoặc có domain public
2. Thêm món vào giỏ hàng
3. Vào trang thanh toán
4. Chọn phương thức "VNPAY"
5. Click "Đặt Hàng Ngay"
6. Tại trang VNPAY, chọn ngân hàng NCB
7. Nhập thông tin thẻ test ở trên
8. Nhập OTP: 123456
9. Hoàn thành thanh toán
*/

// ============================================================
// BƯỚC 3: HÀM TẠO URL THANH TOÁN
// ============================================================
/**
 * Tạo URL thanh toán VNPAY
 * 
 * @param int $orderId - ID đơn hàng
 * @param int $amount - Số tiền (VND)
 * @param string $orderInfo - Mô tả đơn hàng
 * @param string $ipAddr - IP khách hàng
 * @return string - URL redirect đến VNPAY
 */
function createVNPayPaymentUrl($orderId, $amount, $orderInfo, $ipAddr) {
    
    // Set timezone Việt Nam
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    
    // Tạo mã giao dịch unique
    $vnp_TxnRef = $orderId . '_' . time();
    
    // Thời gian tạo và hết hạn (15 phút)
    $vnp_CreateDate = date('YmdHis');
    $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));
    
    // Dữ liệu gửi đến VNPAY
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => VNPAY_TMN_CODE,
        "vnp_Amount" => $amount * 100, // VNPAY yêu cầu nhân 100
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $vnp_CreateDate,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $ipAddr,
        "vnp_Locale" => "vn",
        "vnp_OrderInfo" => $orderInfo,
        "vnp_OrderType" => "billpayment",
        "vnp_ReturnUrl" => VNPAY_RETURN_URL,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => $vnp_ExpireDate
    );
    
    // Sắp xếp dữ liệu theo key
    ksort($inputData);
    
    // Tạo query string và hash data
    $query = "";
    $hashdata = "";
    $i = 0;
    
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    
    // Tạo secure hash
    $vnpSecureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
    $vnp_Url = VNPAY_URL . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;
    
    return $vnp_Url;
}

// ============================================================
// BƯỚC 4: HÀM XÁC THỰC CHỮ KÝ TRẢ VỀ
// ============================================================
/**
 * Xác thực chữ ký từ VNPAY callback
 * 
 * @param array $inputData - Dữ liệu từ $_GET
 * @return bool
 */
function validateVNPaySignature($inputData) {
    $vnp_SecureHash = $inputData['vnp_SecureHash'];
    
    // Loại bỏ các tham số không cần thiết
    $inputData = array_filter($inputData, function($key) {
        return strpos($key, 'vnp_') === 0 && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType';
    }, ARRAY_FILTER_USE_KEY);
    
    // Sắp xếp theo key
    ksort($inputData);
    
    // Tạo hash data
    $hashdata = "";
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    // Tạo secure hash
    $secureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
    
    return $secureHash === $vnp_SecureHash;
}

// ============================================================
// BƯỚC 5: HÀM LẤY THÔNG BÁO LỖI
// ============================================================
/**
 * Lấy thông báo lỗi từ mã response code
 * 
 * @param string $responseCode
 * @return string
 */
function getVNPayResponseMessage($responseCode) {
    $messages = [
        '00' => 'Giao dịch thành công',
        '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
        '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
        '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
        '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
        '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
        '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.',
        '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
        '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
        '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
        '75' => 'Ngân hàng thanh toán đang bảo trì.',
        '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định. Xin quý khách vui lòng thực hiện lại giao dịch',
        '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)'
    ];
    
    return $messages[$responseCode] ?? 'Lỗi không xác định';
}
?>
