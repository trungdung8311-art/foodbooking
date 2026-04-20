<?php
// api/auth/reset_password.php – AJAX: Xác minh email & đặt lại mật khẩu trực tiếp
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$step = $data['step'] ?? '';

// ─────────────────────────────────────────────────────────────
// STEP 1: Xác minh email – trả về token tạm thời
// ─────────────────────────────────────────────────────────────
if ($step === 'verify') {
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email này chưa được đăng ký trong hệ thống.']);
        exit;
    }

    // Tạo token ngẫu nhiên (hết hạn sau 15 phút)
    $token   = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 900); // 15 phút

    $stmtUp = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmtUp->execute([$token, $expires, $user['id']]);

    echo json_encode([
        'success'  => true,
        'message'  => "Xác minh thành công! Chào " . htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'),
        'token'    => $token,
        'email'    => $email,
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────
// STEP 2: Đặt lại mật khẩu mới
// ─────────────────────────────────────────────────────────────
if ($step === 'reset') {
    $email       = trim($data['email'] ?? '');
    $token       = trim($data['token'] ?? '');
    $newPassword = $data['new_password'] ?? '';
    $confirmPass = $data['confirm_password'] ?? '';

    if (empty($email) || empty($token) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc.']);
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.']);
        exit;
    }

    if ($newPassword !== $confirmPass) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp.']);
        exit;
    }

    // Xác minh token hợp lệ và chưa hết hạn
    $stmt = $conn->prepare("
        SELECT id FROM users
        WHERE email = ? AND reset_token = ? AND reset_token_expires > NOW() AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Phiên xác minh đã hết hạn hoặc không hợp lệ. Vui lòng thử lại.'
        ]);
        exit;
    }

    // Cập nhật mật khẩu mới và xoá token
    $hash    = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmtUp  = $conn->prepare("
        UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?
    ");
    $stmtUp->execute([$hash, $user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.'
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Thao tác không hợp lệ.']);
