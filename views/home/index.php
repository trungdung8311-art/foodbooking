<?php
// index.php - Trang chủ Cicafood (Nâng cấp V2)
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/provinces.php';

$currentProvince = getCurrentProvince(); // '' = tất cả, hoặc tên tỉnh cụ thể

$pageTitle = 'Cicafood - Đặt Đồ Ăn Trực Tuyến | Giao Nhanh 30 Phút';
$pageDesc  = 'Đặt đồ ăn online nhanh nhất tại Cicafood. Hơn 500+ quán ngon, giao hàng 30 phút, freeship đơn đầu tiên. Phở, Cơm Tấm, Burger, Sushi, Lẩu...';

// ─── Lấy nhà hàng nổi bật (tất cả province hoặc lọc theo province) ───
if ($currentProvince && $currentProvince !== 'all') {
    $stmtFeatured = $conn->prepare("
        SELECT r.*, c.name AS category_name, c.icon AS category_icon
        FROM restaurants r
        LEFT JOIN categories c ON r.category_id = c.id
        WHERE r.is_open = 1 AND r.province = ?
        ORDER BY r.rating DESC, r.total_reviews DESC
        LIMIT 6
    ");
    $stmtFeatured->execute([$currentProvince]);
    $featuredRestaurants = $stmtFeatured->fetchAll();

    // Nếu không có quán trong tỉnh này, fallback toàn quốc
    if (empty($featuredRestaurants)) {
        $stmtFeatured = $conn->query("
            SELECT r.*, c.name AS category_name, c.icon AS category_icon
            FROM restaurants r
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.is_open = 1
            ORDER BY r.rating DESC, r.total_reviews DESC
            LIMIT 6
        ");
        $featuredRestaurants = $stmtFeatured->fetchAll();
        $noLocalRestaurants = true;
    }
} else {
    // Tất cả tỉnh thành – hiển thị quán nổi bật nhất toàn quốc
    $stmtFeatured = $conn->query("
        SELECT r.*, c.name AS category_name, c.icon AS category_icon
        FROM restaurants r
        LEFT JOIN categories c ON r.category_id = c.id
        WHERE r.is_open = 1
        ORDER BY r.rating DESC, r.total_reviews DESC
        LIMIT 6
    ");
    $featuredRestaurants = $stmtFeatured->fetchAll();
}

// ─── Lấy nhà hàng có deal hời ───
$dealWhere = "r.is_open = 1 AND r.has_deal = 1";
$dealParams = [];
if ($currentProvince && $currentProvince !== 'all') {
    $dealWhere .= " AND r.province = ?";
    $dealParams[] = $currentProvince;
}
$stmtDeal = $conn->prepare("
    SELECT r.*, c.name AS category_name
    FROM restaurants r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE {$dealWhere}
    ORDER BY r.rating DESC
    LIMIT 4
");
$stmtDeal->execute($dealParams);
$dealRestaurants = $stmtDeal->fetchAll();

// ─── Lấy món ăn bán chạy ───
$itemWhere = "mi.is_best_seller = 1 AND mi.is_available = 1 AND r.is_open = 1";
$itemParams = [];
if ($currentProvince && $currentProvince !== 'all') {
    $itemWhere .= " AND r.province = ?";
    $itemParams[] = $currentProvince;
}
$stmtItems = $conn->prepare("
    SELECT mi.*, r.name AS restaurant_name, r.id AS restaurant_id
    FROM menu_items mi
    JOIN restaurants r ON mi.restaurant_id = r.id
    WHERE {$itemWhere}
    ORDER BY RAND()
    LIMIT 8
");
$stmtItems->execute($itemParams);
$bestSellerItems = $stmtItems->fetchAll();

// ─── Lấy tất cả categories ───
$stmtCats = $conn->query("SELECT * FROM categories ORDER BY sort_order");
$allCategories = $stmtCats->fetchAll();

// ─── Lấy vouchers công khai (restaurant_id IS NULL = toàn platform) ───
$stmtVouchers = $conn->query("
    SELECT * FROM vouchers
    WHERE restaurant_id IS NULL AND end_date >= CURDATE() AND is_active = 1
    ORDER BY value DESC
    LIMIT 3
");
$publicVouchers = $stmtVouchers->fetchAll();

// ─── Danh sách voucher user đã lưu ───
$userSavedVoucherIds = [];
if (isLoggedIn()) {
    $stmtSaved = $conn->prepare("SELECT voucher_id FROM user_vouchers WHERE user_id = ?");
    $stmtSaved->execute([$_SESSION['user_id']]);
    $userSavedVoucherIds = $stmtSaved->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ============================================================ -->
<!-- HERO SECTION -->
<!-- ============================================================ -->
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a0a00 0%, #2d0e0e 40%, #ee2624 100%);">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
    </div>
    <!-- Floating food icons -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <i class="fas fa-pizza-slice absolute text-white/5 text-9xl" style="top:10%; left:5%; animation: float1 6s ease-in-out infinite;"></i>
        <i class="fas fa-burger absolute text-white/5 text-8xl" style="top:20%; right:8%; animation: float2 8s ease-in-out infinite;"></i>
        <i class="fas fa-bowl-rice absolute text-white/5 text-7xl" style="bottom:15%; left:15%; animation: float1 7s ease-in-out infinite 1s;"></i>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-16 md:py-24 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2 text-white/90 text-sm font-medium mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                Đang giao hàng tại 63+ tỉnh thành
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6 drop-shadow-lg">
                Thèm gì là có,<br>
                <span class="text-yellow-300">Cicafood</span> giao ngay! 🔥
            </h1>
            <p class="text-white/80 text-lg mb-10 font-medium">
                Hơn <strong class="text-yellow-300">500+ quán ngon</strong> · Giao hàng trong <strong class="text-yellow-300">30 phút</strong> · Freeship đơn đầu
            </p>

            <!-- Search Box -->
            <div class="bg-white rounded-2xl md:rounded-full p-2 shadow-2xl flex flex-col md:flex-row gap-2">
                <div class="flex-1 flex items-center bg-gray-50 rounded-xl md:rounded-full px-5 gap-3">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" id="hero-search" placeholder="Tìm món ngon, quán quen..."
                           class="flex-1 py-3.5 bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base">
                </div>
                <!-- Province selector (interactive) -->
                <div class="flex items-center bg-gray-50 rounded-xl md:rounded-full px-4 gap-2 md:max-w-[220px]">
                    <i class="fas fa-map-marker-alt text-cica-red flex-shrink-0"></i>
                    <form method="POST" id="hero-province-form" class="flex-1">
                        <input type="hidden" name="change_province" value="1">
                        <select name="province_name" onchange="this.form.submit()"
                                class="w-full py-3.5 bg-transparent outline-none text-gray-600 text-sm appearance-none cursor-pointer">
                            <option value="all" <?= ($currentProvince === '' || $currentProvince === 'all') ? 'selected' : '' ?>>📍 Tất cả tỉnh thành</option>
                            <?php foreach (getProvinces() as $prov): ?>
                            <option value="<?= e($prov) ?>" <?= $currentProvince === $prov ? 'selected' : '' ?>><?= e($prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <button onclick="heroSearch()"
                        class="bg-cica-red text-white px-8 py-3.5 rounded-xl md:rounded-full font-bold text-base hover:bg-red-700 transition-all shadow-lg shadow-red-200 active:scale-95 flex items-center gap-2 justify-center">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes float1 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-20px) rotate(5deg)} }
@keyframes float2 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-15px) rotate(-5deg)} }
</style>

<!-- ============================================================ -->
<!-- THÔNG BÁO KHI TỈNH KHÔNG CÓ QUÁN -->
<!-- ============================================================ -->
<?php if (!empty($noLocalRestaurants)): ?>
<div class="max-w-screen-xl mx-auto px-4 pt-4">
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3 flex items-center gap-3 text-amber-800 text-sm font-medium">
        <i class="fas fa-map-marker-alt text-amber-500"></i>
        Chưa có quán nào tại <strong><?= e($currentProvince) ?></strong>. Đang hiển thị quán nổi bật toàn quốc.
    </div>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- CATEGORY PILLS -->
<!-- ============================================================ -->
<section class="max-w-screen-xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 overflow-x-auto pb-2" style="scrollbar-width:none">
        <span class="text-sm font-bold text-gray-700 whitespace-nowrap">Lọc nhanh:</span>
        <?php foreach ($allCategories as $cat): ?>
        <a href="/foodbooking/views/restaurant/list.php?category=<?= e($cat['slug']) ?>"
           class="flex-shrink-0 flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-cica-red transition shadow-sm">
            <i class="fas <?= e($cat['icon']) ?>" style="color:<?= e($cat['color']) ?>"></i>
            <?= e($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============================================================ -->
<!-- PROMOTIONAL BANNERS / PUBLIC VOUCHERS -->
<!-- ============================================================ -->
<section class="max-w-screen-xl mx-auto px-4 mb-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Banner 1: Freeship -->
        <div class="md:col-span-2 relative bg-gradient-to-br from-red-600 via-red-500 to-orange-500 rounded-3xl p-8 overflow-hidden shadow-lg group cursor-pointer hover:shadow-2xl transition-all">
            <div class="relative z-10">
                <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">Chào Giao Hàng</span>
                <h2 class="text-white text-4xl font-black mt-3 mb-2 leading-tight">FREESHIP<br>Đơn Đầu Tiên!</h2>
                <p class="text-white/80 text-sm mb-5">Nhập <strong class="text-yellow-300 text-base">FREESHIP</strong> khi thanh toán để miễn phí vận chuyển</p>
                <a href="/foodbooking/views/restaurant/list.php" class="inline-flex items-center gap-2 bg-white text-cica-red px-6 py-3 rounded-xl font-black text-sm shadow-lg hover:shadow-2xl transition active:scale-95">
                    Đặt ngay <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <i class="fas fa-motorcycle absolute bottom-0 right-6 text-[140px] text-white/10 -rotate-12 group-hover:rotate-0 transition-transform duration-500"></i>
        </div>

        <!-- Vouchers công khai -->
        <div class="flex flex-col gap-4">
            <?php
            $bannerColors = [
                ['from-orange-400','to-yellow-400','text-orange-900','text-orange-800','fa-bolt'],
                ['from-purple-500','to-indigo-600','text-purple-200','text-purple-200','fa-gift'],
                ['from-green-500','to-teal-500','text-green-100','text-green-100','fa-tag'],
            ];
            $vIdx = 0;
            foreach ($publicVouchers as $pv):
                $bc = $bannerColors[$vIdx % count($bannerColors)];
                $alreadySaved = in_array($pv['id'], $userSavedVoucherIds);
                $vIdx++;
            ?>
            <div class="bg-gradient-to-br <?= $bc[0] ?> <?= $bc[1] ?> rounded-2xl p-5 flex items-center justify-between hover:shadow-lg transition group <?= $vIdx === 1 ? '' : 'flex-1' ?>">
                <div>
                    <p class="<?= $bc[2] ?> font-bold text-xs uppercase tracking-widest mb-1">
                        <?= $pv['type'] === 'freeship' ? 'Freeship' : ($pv['type'] === 'percent' ? 'Giảm '.$pv['value'].'%' : 'Tiết Kiệm') ?>
                    </p>
                    <h3 class="text-white font-black text-xl leading-tight"><?= e($pv['name']) ?></h3>
                    <p class="<?= $bc[3] ?> text-xs mt-0.5 font-mono font-bold">Mã: <?= e($pv['code']) ?></p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <i class="fas <?= $bc[4] ?> text-white text-3xl opacity-70 group-hover:scale-110 transition"></i>
                    <?php if (!isLoggedIn()): ?>
                    <a href="/foodbooking/views/auth/login.php" class="text-[10px] font-black bg-white/30 hover:bg-white/50 text-white px-3 py-1.5 rounded-full transition whitespace-nowrap">
                        Đăng nhập để lấy
                    </a>
                    <?php elseif ($alreadySaved): ?>
                    <span class="text-[10px] font-black bg-white/30 text-white px-3 py-1.5 rounded-full cursor-default whitespace-nowrap">
                        ✓ Đã lưu
                    </span>
                    <?php else: ?>
                    <button onclick="claimVoucher(<?= $pv['id'] ?>, this)"
                            class="text-[10px] font-black bg-white/90 hover:bg-white text-gray-800 px-3 py-1.5 rounded-full transition shadow-sm whitespace-nowrap active:scale-90">
                        🎁 Lấy mã
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Fallback nếu ít hơn 2 vouchers -->
            <?php if (count($publicVouchers) < 2): ?>
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-5 flex items-center justify-between flex-1 hover:shadow-lg transition group">
                <div>
                    <p class="text-purple-200 font-bold text-xs uppercase tracking-widest mb-1">Thành Viên Mới</p>
                    <h3 class="text-white font-black text-xl">Giảm 50K</h3>
                    <p class="text-purple-200 text-xs font-mono font-bold">Mã: CICASAVE50</p>
                </div>
                <i class="fas fa-gift text-white text-3xl opacity-70 group-hover:scale-110 transition"></i>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================ -->
<!-- QUÁN NGON NỔI BẬT -->
<!-- ============================================================ -->
<section class="max-w-screen-xl mx-auto px-4 mb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">🔥 Quán Ngon Nổi Bật</h2>
            <p class="text-sm text-gray-500 mt-1">
                <?= ($currentProvince && $currentProvince !== 'all') ? 'Tại ' . e($currentProvince) : 'Được yêu thích nhất toàn quốc' ?>
            </p>
        </div>
        <a href="/foodbooking/views/restaurant/list.php?sort=rating" class="text-cica-red font-bold text-sm hover:underline flex items-center gap-1">
            Xem tất cả <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <?php if (empty($featuredRestaurants)): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
        <i class="fas fa-store-slash text-5xl text-gray-200 mb-4"></i>
        <p class="text-gray-400 font-medium">Chưa có quán nào. <a href="/foodbooking/views/restaurant/list.php" class="text-cica-red hover:underline">Xem tất cả quán</a></p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($featuredRestaurants as $r): ?>
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100 group">
            <a href="/foodbooking/views/restaurant/detail.php?id=<?= $r['id'] ?>" class="block">
                <div class="relative h-48 overflow-hidden">
                    <img src="<?= getImageUrl($r['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80') ?>"
                         alt="<?= e($r['name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                         loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                    <div class="absolute top-3 left-3">
                        <span class="bg-cica-red/90 backdrop-blur-sm text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                            <i class="fas fa-fire-flame-curved mr-1"></i>Nổi bật
                        </span>
                    </div>
                    <button onclick="toggleFavorite(event, <?= $r['id'] ?>, this)" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:scale-110 transition z-10">
                        <i class="<?= in_array($r['id'], $userFavorites) ? 'fas text-cica-red' : 'far text-gray-400' ?> fa-heart"></i>
                    </button>
                    <div class="absolute bottom-3 right-3 flex gap-1.5">
                        <?php if ($r['has_freeship']): ?>
                        <span class="bg-green-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-md">Freeship</span>
                        <?php endif; ?>
                        <?php if ($r['has_deal']): ?>
                        <span class="bg-orange-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-md">Deal</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 text-base leading-tight group-hover:text-cica-red transition"><?= e($r['name']) ?></h3>
                        <span class="flex items-center gap-1 text-yellow-500 font-bold text-sm flex-shrink-0">
                            <i class="fas fa-star text-xs"></i><?= number_format($r['rating'], 1) ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 mb-2 flex items-center gap-1">
                        <i class="fas fa-<?= e($r['category_icon'] ?? 'utensils') ?>" style="color:#ee2624"></i>
                        <?= e($r['category_name'] ?? '') ?>
                        <?php if (!empty($r['province'])): ?>
                        <span class="ml-auto text-gray-300">|</span>
                        <i class="fas fa-map-marker-alt text-gray-300"></i>
                        <span class="text-gray-400 truncate max-w-[90px]"><?= e($r['province']) ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-50 pt-3">
                        <span><i class="far fa-clock mr-1 text-cica-red"></i><?= $r['delivery_time'] ?> phút</span>
                        <span><i class="fas fa-location-dot mr-1 text-cica-red"></i><?= $r['distance'] ?> km</span>
                        <span class="<?= $r['delivery_fee'] == 0 ? 'text-green-600 font-semibold' : '' ?>">
                            <?= $r['delivery_fee'] == 0 ? '🚚 Freeship' : 'Ship: ' . formatPrice($r['delivery_fee']) ?>
                        </span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ============================================================ -->
<!-- DEAL HỜI -->
<!-- ============================================================ -->
<?php if (!empty($dealRestaurants)): ?>
<section class="max-w-screen-xl mx-auto px-4 mb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">⚡ Deal Hời Hôm Nay</h2>
            <p class="text-sm text-gray-500 mt-1">Ưu đãi đặc biệt, số lượng có hạn!</p>
        </div>
        <a href="/foodbooking/views/restaurant/list.php?deal=1" class="text-cica-red font-bold text-sm hover:underline flex items-center gap-1">
            Xem thêm <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($dealRestaurants as $r): ?>
        <a href="/foodbooking/views/restaurant/detail.php?id=<?= $r['id'] ?>"
           class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-orange-100 group">
            <div class="relative h-40 overflow-hidden">
                <img src="<?= getImageUrl($r['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($r['name']) ?>"
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-700" loading="lazy"
                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'">
                <div class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-black px-2 py-0.5 rounded-lg animate-pulse z-10">
                    <i class="fas fa-tag mr-1"></i>DEAL
                </div>
                <button onclick="toggleFavorite(event, <?= $r['id'] ?>, this)" class="absolute top-2 left-2 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:scale-110 transition z-10">
                    <i class="<?= in_array($r['id'], $userFavorites) ? 'fas text-cica-red' : 'far text-gray-400' ?> fa-heart"></i>
                </button>
            </div>
            <div class="p-3">
                <h3 class="font-bold text-sm text-gray-800 truncate group-hover:text-orange-500 transition"><?= e($r['name']) ?></h3>
                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                    <span><i class="fas fa-star text-yellow-400 mr-1"></i><?= number_format($r['rating'], 1) ?></span>
                    <span><i class="far fa-clock mr-1"></i><?= $r['delivery_time'] ?> phút</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================ -->
<!-- MÓN ĂN BÁN CHẠY -->
<!-- ============================================================ -->
<?php if (!empty($bestSellerItems)): ?>
<section class="max-w-screen-xl mx-auto px-4 mb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">🍽️ Món Ăn Bán Chạy</h2>
            <p class="text-sm text-gray-500 mt-1">Được đặt nhiều nhất trong tuần qua</p>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($bestSellerItems as $item): ?>
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100 group">
            <div class="relative">
                <div class="h-36 overflow-hidden">
                    <img src="<?= getImageUrl($item['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80') ?>" alt="<?= e($item['name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80'">
                </div>
                <button onclick="addToCart(<?= $item['id'] ?>, <?= $item['restaurant_id'] ?>)"
                        class="absolute bottom-2 right-2 w-8 h-8 bg-cica-red text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-700 transition active:scale-90 text-sm">
                    <i class="fas fa-plus text-xs"></i>
                </button>
                <?php if ($item['original_price'] && $item['original_price'] > $item['price']):
                    $discount = round((1 - $item['price'] / $item['original_price']) * 100); ?>
                <span class="absolute top-2 left-2 bg-cica-red text-white text-[10px] font-black px-1.5 py-0.5 rounded-md">
                    -<?= $discount ?>%
                </span>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <h4 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2"><?= e($item['name']) ?></h4>
                <p class="text-gray-400 text-xs mb-2 truncate"><?= e($item['restaurant_name']) ?></p>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-cica-red font-black text-base"><?= formatPrice($item['price']) ?></span>
                        <?php if ($item['original_price']): ?>
                        <span class="text-gray-400 text-xs line-through ml-1"><?= formatPrice($item['original_price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================ -->
<!-- WHY CICAFOOD -->
<!-- ============================================================ -->
<section class="bg-white border-y border-gray-100 py-14 mb-12">
    <div class="max-w-screen-xl mx-auto px-4">
        <h2 class="text-2xl font-black text-center text-gray-900 mb-10">Tại Sao Chọn <span class="text-cica-red">Cicafood</span>?</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php
            $whyItems = [
                ['fa-bolt','Giao Nhanh 30\'','Đội ngũ shipper tận tâm, giao hàng siêu tốc'],
                ['fa-shield-halved','An Toàn Thực Phẩm','Tất cả đối tác đều qua kiểm duyệt nghiêm ngặt'],
                ['fa-tag','Deal Tốt Nhất','Hàng trăm voucher và ưu đãi mỗi ngày'],
                ['fa-headset','Hỗ Trợ 24/7','Đội hỗ trợ luôn sẵn sàng giải quyết vấn đề'],
            ];
            foreach ($whyItems as [$icon, $title, $desc]): ?>
            <div class="text-center group">
                <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-cica-red transition">
                    <i class="fas <?= $icon ?> text-2xl text-cica-red group-hover:text-white transition"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-1"><?= $title ?></h3>
                <p class="text-xs text-gray-500"><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================ -->
<!-- CTA REGISTER -->
<!-- ============================================================ -->
<?php if (!isLoggedIn()): ?>
<section class="max-w-screen-xl mx-auto px-4 mb-12">
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-10 md:p-14 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
        <div class="absolute inset-0 opacity-5">
            <i class="fas fa-utensils absolute text-[300px] text-white -bottom-20 right-10 rotate-12"></i>
        </div>
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-white mb-3">Tham gia Cicafood<br><span class="text-yellow-400">Nhận ngay 20% giảm!</span></h2>
            <p class="text-gray-400">Đăng ký tài khoản miễn phí và nhận voucher chào mừng đặc biệt</p>
        </div>
        <div class="relative z-10 flex gap-4 flex-shrink-0">
            <a href="/foodbooking/views/auth/login.php" class="border border-white/30 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-white/10 transition">Đăng nhập</a>
            <a href="/foodbooking/views/auth/register.php" class="bg-cica-red text-white px-8 py-3.5 rounded-xl font-bold hover:bg-red-600 transition shadow-lg shadow-red-900/30 active:scale-95">Đăng ký ngay</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
function heroSearch() {
    const q = document.getElementById('hero-search').value.trim();
    if (q) window.location.href = '/foodbooking/views/restaurant/list.php?q=' + encodeURIComponent(q);
    else window.location.href = '/foodbooking/views/restaurant/list.php';
}
document.getElementById('hero-search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') heroSearch();
});

// Lấy voucher về kho của user (AJAX)
function claimVoucher(voucherId, btn) {
    if (!voucherId) return;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/foodbooking/api/save_voucher.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({voucher_id: voucherId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', '🎉 ' + data.message);
            btn.innerHTML = '✓ Đã lưu';
            btn.className = 'text-[10px] font-black bg-white/30 text-white px-3 py-1.5 rounded-full cursor-default whitespace-nowrap';
        } else {
            showToast('error', data.message);
            btn.innerHTML = original;
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('error', 'Lỗi kết nối');
        btn.innerHTML = original;
        btn.disabled = false;
    });
}

// Toggle favorite
function toggleFavorite(event, restaurantId, btn) {
    event.preventDefault();
    event.stopPropagation();
    const icon = btn.querySelector('i');
    fetch('/foodbooking/api/restaurant/toggle_favorite.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({restaurant_id: restaurantId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.favorited) {
                icon.className = 'fas text-cica-red fa-heart';
                showToast('success', 'Đã thêm vào yêu thích!');
            } else {
                icon.className = 'far text-gray-400 fa-heart';
                showToast('info', 'Đã bỏ yêu thích');
            }
        } else if (data.login_required) {
            window.location.href = '/foodbooking/views/auth/login.php';
        }
    })
    .catch(() => showToast('error', 'Lỗi kết nối'));
}
</script>