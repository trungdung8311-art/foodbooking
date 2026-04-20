<?php
// api/auth/logout.php
require_once __DIR__ . '/../../config/database.php';

// Xoá tất cả session
$_SESSION = [];
session_destroy();

// Xoá remember me cookie
if (isset($_COOKIE['cicafood_remember'])) {
    setcookie('cicafood_remember', '', time() - 3600, '/');
}

setcookie(session_name(), '', time() - 3600, '/');

header('Location: /foodbooking/');
exit;
?>
