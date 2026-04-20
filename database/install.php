<?php
// install.php - Script cài đặt database tự động
// Truy cập: http://localhost/foodbooking/install.php
// SAU KHI CHẠY XONG, XOÁ FILE NÀY!

$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'cicafood';

try {
    // Kết nối không chọn DB trước
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Đọc và chạy SQL
    $sql = file_get_contents(__DIR__ . '/cicafood.sql');

    // Bỏ comment và tách từng statement
    $sql = preg_replace('/--.*\n/', "\n", $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Chạy từng statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s)
    );

    $success = 0;
    $errors  = [];
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        try {
            $pdo->exec($statement);
            $success++;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Verify
    $pdo2 = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $tables = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $restaurants = $pdo2->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
    $vouchers = $pdo2->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
    $menuItems = $pdo2->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

} catch (PDOException $e) {
    die("❌ Lỗi kết nối: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cicafood - Cài Đặt Database</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl p-8">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-5xl">✅</span>
        </div>
        <h1 class="text-3xl font-black text-gray-900">Database Đã Được Cài Đặt!</h1>
        <p class="text-gray-500 mt-2">Cicafood đã sẵn sàng khởi chạy</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-50 rounded-2xl p-4 text-center">
            <div class="text-3xl font-black text-blue-600"><?= count($tables) ?></div>
            <div class="text-xs text-blue-500 mt-1 font-semibold">Tables</div>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 text-center">
            <div class="text-3xl font-black text-red-600"><?= $restaurants ?></div>
            <div class="text-xs text-red-500 mt-1 font-semibold">Nhà hàng</div>
        </div>
        <div class="bg-green-50 rounded-2xl p-4 text-center">
            <div class="text-3xl font-black text-green-600"><?= $menuItems ?></div>
            <div class="text-xs text-green-500 mt-1 font-semibold">Món ăn</div>
        </div>
    </div>

    <!-- Tables created -->
    <div class="bg-gray-50 rounded-2xl p-5 mb-6">
        <h3 class="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">Tables đã tạo:</h3>
        <div class="grid grid-cols-2 gap-2">
            <?php foreach ($tables as $table): ?>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="text-green-500 font-bold">✓</span> <?= $table ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Demo credentials -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 mb-6">
        <h3 class="font-bold text-yellow-800 mb-3 text-sm">🔑 Tài Khoản Demo:</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-white rounded-xl p-3">
                <p class="font-bold text-gray-800">Admin</p>
                <p class="text-gray-500">admin@cicafood.vn</p>
                <p class="text-gray-500">Mật khẩu: <strong>password</strong></p>
            </div>
            <div class="bg-white rounded-xl p-3">
                <p class="font-bold text-gray-800">Khách hàng</p>
                <p class="text-gray-500">an@gmail.com</p>
                <p class="text-gray-500">Mật khẩu: <strong>password</strong></p>
            </div>
        </div>
        <p class="text-xs text-yellow-700 mt-3 font-medium">Voucher mẫu: CICA20, FREESHIP, CICASAVE50, SHIP0, WEEKEND30</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-100 rounded-2xl p-4 mb-6">
        <h3 class="font-bold text-red-700 mb-2 text-sm">Một số câu lệnh có lỗi (thường do table đã tồn tại):</h3>
        <div class="text-xs text-red-600 space-y-1 max-h-32 overflow-y-auto">
            <?php foreach (array_slice($errors, 0, 5) as $err): ?>
            <p>• <?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action buttons -->
    <div class="grid grid-cols-2 gap-4">
        <a href="index.php" class="bg-red-600 text-white py-4 rounded-2xl font-bold text-center hover:bg-red-700 transition">
            🚀 Vào Trang Chủ
        </a>
        <a href="login.php" class="bg-gray-800 text-white py-4 rounded-2xl font-bold text-center hover:bg-gray-900 transition">
            🔐 Đăng Nhập
        </a>
    </div>

    <p class="text-center text-xs text-red-500 font-semibold mt-5">
        ⚠️ QUAN TRỌNG: Xoá file install.php sau khi setup xong!
    </p>
</div>
</body>
</html>
