<?php
/**
 * ============================================================
 * CICAFOOD - Front Controller
 * ============================================================
 * 
 * Entry point cho toàn bộ ứng dụng
 * Xử lý routing và load views tương ứng
 * 
 * @version 2.0
 * @date 2026-04-20
 */

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/helpers.php';

// Get request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = str_replace($script_name, '', $request_uri);
$request_uri = trim($request_uri, '/');

// Remove query string
$request_uri = strtok($request_uri, '?');

// Simple routing
switch ($request_uri) {
    // ============================================================
    // HOME
    // ============================================================
    case '':
    case 'index':
    case 'home':
        require __DIR__ . '/views/home/index.php';
        break;

    // ============================================================
    // AUTH
    // ============================================================
    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;

    case 'register':
        require __DIR__ . '/views/auth/register.php';
        break;

    case 'logout':
        require __DIR__ . '/api/auth/logout.php';
        break;

    // ============================================================
    // RESTAURANT
    // ============================================================
    case 'restaurants':
    case 'shop':
        require __DIR__ . '/views/restaurant/list.php';
        break;

    case 'restaurant':
        require __DIR__ . '/views/restaurant/detail.php';
        break;

    // ============================================================
    // ORDER
    // ============================================================
    case 'checkout':
        require __DIR__ . '/views/order/checkout.php';
        break;

    case 'order-success':
        require __DIR__ . '/views/order/success.php';
        break;

    case 'orders':
    case 'order-history':
        require __DIR__ . '/views/order/history.php';
        break;

    // ============================================================
    // USER
    // ============================================================
    case 'profile':
    case 'user':
        require __DIR__ . '/views/user/profile.php';
        break;

    case 'favorites':
        require __DIR__ . '/views/user/favorites.php';
        break;

    case 'vouchers':
    case 'my-vouchers':
        require __DIR__ . '/views/user/vouchers.php';
        break;

    // ============================================================
    // MERCHANT
    // ============================================================
    case 'merchant':
    case 'merchant/dashboard':
        require __DIR__ . '/views/merchant/dashboard.php';
        break;

    case 'merchant/menu':
        require __DIR__ . '/views/merchant/menu.php';
        break;

    case 'merchant/orders':
        require __DIR__ . '/views/merchant/orders.php';
        break;

    case 'merchant/settings':
        require __DIR__ . '/views/merchant/settings.php';
        break;

    case 'merchant/vouchers':
        require __DIR__ . '/views/merchant/vouchers.php';
        break;

    // ============================================================
    // LEGACY SUPPORT (Backward compatibility)
    // ============================================================
    case 'Shop.php':
        header('Location: /restaurants');
        exit;

    case 'restaurant.php':
        header('Location: /restaurant?' . $_SERVER['QUERY_STRING']);
        exit;

    case 'login.php':
        header('Location: /login');
        exit;

    case 'Register.php':
        header('Location: /register');
        exit;

    case 'user.php':
        header('Location: /profile');
        exit;

    case 'my_vouchers.php':
        header('Location: /vouchers');
        exit;

    case 'orders.php':
        header('Location: /orders');
        exit;

    case 'checkout.php':
        header('Location: /checkout');
        exit;

    // ============================================================
    // 404 NOT FOUND
    // ============================================================
    default:
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-9xl font-black text-red-600">404</h1>
        <p class="text-2xl font-bold text-gray-800 mt-4">Không tìm thấy trang</p>
        <p class="text-gray-600 mt-2">Trang bạn đang tìm kiếm không tồn tại.</p>
        <a href="/" class="inline-block mt-6 bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition">
            Về trang chủ
        </a>
    </div>
</body>
</html>';
        break;
}
