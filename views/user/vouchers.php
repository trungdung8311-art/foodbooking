<?php
// views/user/vouchers.php - Danh sách voucher của tôi (Nâng cấp V2)
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle = 'Voucher Của Tôi - Cicafood';
$userId    = (int)$_SESSION['user_id'];

// Tab: active (còn hạn, chưa dùng) | used (đã dùng) | expired (hết hạn)
$tab = $_GET['tab'] ?? 'active';

// Vouchers còn hiệu lực (chưa dùng + còn hạn)
$stmtActive = $conn->prepare("
    SELECT uv.*, v.code, v.name, v.description, v.type, v.value, v.max_discount, v.min_order, v.end_date,
           r.name AS restaurant_name, r.id AS restaurant_id, r.image AS restaurant_image
    FROM user_vouchers uv
    JOIN vouchers v ON uv.voucher_id = v.id
    LEFT JOIN restaurants r ON v.restaurant_id = r.id
    WHERE uv.user_id = ? AND uv.is_used = 0 AND v.end_date >= CURDATE() AND v.is_active = 1
    ORDER BY uv.created_at DESC
");
$stmtActive->execute([$userId]);
$activeVouchers = $stmtActive->fetchAll();

// Vouchers đã dùng
$stmtUsed = $conn->prepare("
    SELECT uv.*, v.code, v.name, v.description, v.type, v.value, v.max_discount, v.min_order, v.end_date,
           r.name AS restaurant_name, r.id AS restaurant_id
    FROM user_vouchers uv
    JOIN vouchers v ON uv.voucher_id = v.id
    LEFT JOIN restaurants r ON v.restaurant_id = r.id
    WHERE uv.user_id = ? AND uv.is_used = 1
    ORDER BY uv.used_at DESC
    LIMIT 20
");
$stmtUsed->execute([$userId]);
$usedVouchers = $stmtUsed->fetchAll();

// Vouchers hết hạn (chưa dùng nhưng hết hạn)
$stmtExpired = $conn->prepare("
    SELECT uv.*, v.code, v.name, v.description, v.type, v.value, v.max_discount, v.min_order, v.end_date,
           r.name AS restaurant_name, r.id AS restaurant_id
    FROM user_vouchers uv
    JOIN vouchers v ON uv.voucher_id = v.id
    LEFT JOIN restaurants r ON v.restaurant_id = r.id
    WHERE uv.user_id = ? AND uv.is_used = 0 AND v.end_date < CURDATE()
    ORDER BY v.end_date DESC
    LIMIT 10
");
$stmtExpired->execute([$userId]);
$expiredVouchers = $stmtExpired->fetchAll();

// Hiển thị danh sách theo tab
$displayVouchers = match($tab) {
    'used'    => $usedVouchers,
    'expired' => $expiredVouchers,
    default   => $activeVouchers
};

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
        <a href="/foodbooking/views/user/profile.php" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-50 transition shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-ticket-alt text-cica-red"></i> Ví Voucher
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                <span class="text-cica-red font-bold"><?= count($activeVouchers) ?></span> voucher khả dụng ·
                <span class="text-gray-400"><?= count($usedVouchers) ?> đã dùng</span>
            </p>
        </div>
        <a href="/foodbooking/" class="ml-auto text-sm font-bold text-cica-red bg-red-50 hover:bg-red-100 transition px-4 py-2 rounded-xl flex items-center gap-2">
            <i class="fas fa-plus"></i> Lấy thêm
        </a>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-gray-100">
        <a href="?tab=active"
           class="px-5 py-3 text-sm font-bold border-b-2 transition <?= ($tab === 'active') ? 'border-cica-red text-cica-red' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            Khả dụng <span class="ml-1 bg-red-100 text-cica-red text-xs font-black px-2 py-0.5 rounded-full"><?= count($activeVouchers) ?></span>
        </a>
        <a href="?tab=used"
           class="px-5 py-3 text-sm font-bold border-b-2 transition <?= ($tab === 'used') ? 'border-gray-700 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            Đã dùng <span class="ml-1 bg-gray-100 text-gray-500 text-xs font-black px-2 py-0.5 rounded-full"><?= count($usedVouchers) ?></span>
        </a>
        <a href="?tab=expired"
           class="px-5 py-3 text-sm font-bold border-b-2 transition <?= ($tab === 'expired') ? 'border-gray-400 text-gray-500' : 'border-transparent text-gray-400 hover:text-gray-600' ?>">
            Hết hạn <span class="ml-1 bg-gray-100 text-gray-400 text-xs font-black px-2 py-0.5 rounded-full"><?= count($expiredVouchers) ?></span>
        </a>
    </div>

    <!-- Banner lấy thêm voucher -->
    <?php if ($tab === 'active' && empty($activeVouchers)): ?>
    <div class="bg-white rounded-3xl border border-gray-100 p-16 text-center max-w-2xl mx-auto mb-6">
        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-ticket-alt text-4xl text-cica-red opacity-50"></i>
        </div>
        <h2 class="text-xl font-black text-gray-800 mb-2">Ví voucher trống</h2>
        <p class="text-gray-500 mb-6 text-sm">Hãy ghé thăm các nhà hàng để lưu mã giảm giá hấp dẫn!</p>
        <div class="flex gap-3 justify-center">
            <a href="/foodbooking/views/restaurant/list.php" class="inline-block bg-cica-red text-white font-bold px-6 py-3 rounded-2xl shadow-lg shadow-red-200 hover:bg-red-700 transition active:scale-95">
                <i class="fas fa-store mr-2"></i>Tìm nhà hàng
            </a>
            <a href="/foodbooking/" class="inline-block border border-cica-red text-cica-red font-bold px-6 py-3 rounded-2xl hover:bg-red-50 transition">
                <i class="fas fa-gift mr-2"></i>Lấy voucher
            </a>
        </div>
    </div>
    <?php elseif (!empty($displayVouchers)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($displayVouchers as $v):
            $isUsed    = (bool)($v['is_used'] ?? false);
            $isExpired = strtotime($v['end_date']) < time();
            $colorRed  = (bool)$v['restaurant_id'];
            $accentCls = $colorRed ? 'text-cica-red bg-red-50 border-red-100' : 'text-blue-600 bg-blue-50 border-blue-100';
            $iconCls   = $colorRed ? 'text-cica-red' : 'text-blue-500';
            // Giá trị hiển thị
            $valueStr  = match($v['type']) {
                'percent'  => $v['value'] . '%',
                'freeship' => 'Freeship',
                default    => formatPrice($v['value'])
            };
        ?>
        <div class="bg-white border rounded-2xl flex overflow-hidden shadow-sm hover:shadow-md transition relative <?= $isUsed || $isExpired ? 'opacity-60' : '' ?> border-<?= $colorRed ? 'red' : 'blue' ?>-100">
            <!-- Watermark trạng thái -->
            <?php if ($isUsed): ?>
            <div class="absolute top-2 right-2 z-10">
                <span class="bg-gray-200 text-gray-500 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">Đã dùng</span>
            </div>
            <?php elseif ($isExpired): ?>
            <div class="absolute top-2 right-2 z-10">
                <span class="bg-red-100 text-red-400 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">Hết hạn</span>
            </div>
            <?php endif; ?>

            <!-- Left decoration -->
            <div class="w-20 <?= $colorRed ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200' ?> border-r border-dashed flex flex-col items-center justify-center p-2 relative">
                <div class="absolute -top-3 -right-3 w-6 h-6 bg-white rounded-full border border-gray-100"></div>
                <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-white rounded-full border border-gray-100"></div>
                <?php
                $icon = match($v['type']) {
                    'freeship' => 'fa-motorcycle',
                    'percent'  => 'fa-percent',
                    default    => 'fa-money-bill-wave'
                };
                ?>
                <i class="fas <?= $icon ?> text-2xl <?= $iconCls ?> mb-1"></i>
                <span class="text-[9px] font-black uppercase text-center <?= $iconCls ?> leading-tight"><?= $valueStr ?></span>
            </div>

            <!-- Right info -->
            <div class="flex-1 p-3.5 flex flex-col">
                <?php if ($v['restaurant_id'] && $v['restaurant_name']): ?>
                <span class="text-[10px] font-bold text-gray-500 mb-1 flex items-center gap-1">
                    <i class="fas fa-store text-cica-red"></i>
                    <span class="text-cica-red truncate"><?= e($v['restaurant_name']) ?></span>
                </span>
                <?php else: ?>
                <span class="text-[10px] font-bold text-blue-500 mb-1 flex items-center gap-1">
                    <i class="fas fa-globe"></i> Toàn sàn Cicafood
                </span>
                <?php endif; ?>

                <h3 class="font-black text-gray-800 text-sm mb-1 line-clamp-2 leading-snug"><?= e($v['name']) ?></h3>

                <div class="flex items-center gap-1 mb-1">
                    <span class="font-mono font-black text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md tracking-widest"><?= e($v['code']) ?></span>
                </div>

                <p class="text-[11px] text-gray-500 mb-2">
                    Đơn từ <?= formatPrice($v['min_order']) ?>
                    <?= $v['type'] === 'percent' && $v['max_discount'] ? ' · Giảm tối đa ' . formatPrice($v['max_discount']) : '' ?>
                </p>

                <div class="mt-auto pt-2 flex items-end justify-between border-t border-gray-50">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 mb-0.5">HSD: <?= date('d/m/Y', strtotime($v['end_date'])) ?></p>
                    </div>
                    <?php if (!$isUsed && !$isExpired): ?>
                        <?php if ($v['restaurant_id']): ?>
                        <a href="/foodbooking/views/restaurant/detail.php?id=<?= $v['restaurant_id'] ?>"
                           class="text-[10px] font-bold text-cica-red bg-red-50 hover:bg-red-100 transition px-3 py-1.5 rounded-xl">
                            Dùng Ngay →
                        </a>
                        <?php else: ?>
                        <a href="/foodbooking/views/order/checkout.php"
                           class="text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition px-3 py-1.5 rounded-xl">
                            Dùng Ngay →
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php elseif ($tab === 'used'): ?>
    <div class="bg-white rounded-3xl border border-gray-100 p-12 text-center max-w-xl mx-auto">
        <i class="fas fa-history text-5xl text-gray-200 mb-4"></i>
        <p class="text-gray-400 font-medium">Bạn chưa sử dụng voucher nào.</p>
    </div>
    <?php elseif ($tab === 'expired'): ?>
    <div class="bg-white rounded-3xl border border-gray-100 p-12 text-center max-w-xl mx-auto">
        <i class="fas fa-calendar-times text-5xl text-gray-200 mb-4"></i>
        <p class="text-gray-400 font-medium">Không có voucher hết hạn.</p>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
