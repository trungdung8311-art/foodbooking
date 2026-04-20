<?php
// api/auth/login_process.php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $_POST['account'];
    $password = $_POST['password'];

    // 1. Tìm người dùng trong database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email_or_phone = :account");
    $stmt->bindParam(':account', $account);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 2. Kiểm tra mật khẩu (Sử dụng password_verify để so khớp với hash trong DB)
        if (password_verify($password, $user['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            
            // Chuyển hướng về trang chủ
            header("Location: /foodbooking/");
            exit();
        } else {
            $error = "Mật khẩu không chính xác.";
        }
    } else {
        $error = "Tài khoản không tồn tại.";
    }
}
?>