<?php
// ============================================================
// api/auth/login_process.php
// API xử lý đăng nhập (endpoint dự phòng)
// Logic đăng nhập chính nằm tại: views/auth/login.php
// ============================================================
require_once __DIR__ . '/../../config/database.php';

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$account  = trim($_POST['account'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($account) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.']);
    exit;
}

// Tìm user theo email HOẶC số điện thoại (đúng tên cột trong schema)
$stmt = $conn->prepare(
    "SELECT * FROM users WHERE (email = :account OR phone = :account) AND is_active = 1 LIMIT 1"
);
$stmt->execute([':account' => $account]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    // Đăng nhập thành công - set session
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_name']   = $user['full_name'];   // đúng tên cột
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_role']   = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'];

    // Xác định trang chuyển hướng theo role
    $redirect = '/foodbooking/';
    if ($user['role'] === 'admin') {
        $redirect = '/foodbooking/merchant/index.php';
    }

    header('Location: ' . $redirect);
    exit;
} else {
    // Quay lại trang login kèm thông báo lỗi
    $_SESSION['login_error'] = 'Email/SĐT hoặc mật khẩu không đúng.';
    header('Location: /foodbooking/views/auth/login.php');
    exit;
}