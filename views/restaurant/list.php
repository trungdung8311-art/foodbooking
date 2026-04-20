<?php
// views/restaurant/list.php - Danh sách tất cả nhà hàng với bộ lọc
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/provinces.php';
$currentProvince = getCurrentProvince();


$pageTitle = 'Tất Cả Nhà Hàng - Cicafood | Đặt Đồ Ăn Online';
$pageDesc  = 'Khám phá hàng trăm nhà hàng, quán ăn ngon gần bạn. Lọc theo danh mục, đánh giá, khoảng cách.';

// Parameters
$category = trim($_GET['category'] ?? '');
$sort     = $_GET['sort'] ?? 'default';
$search   = trim($_GET['q'] ?? '');
$freeship = (int)($_GET['freeship'] ?? 0);
$deal     = (int)($_GET['deal'] ?? 0);

// Build query
$where  = ['r.is_open = 1'];
$params = [];

// Thêm lọc tỉnh thành (bỏ qua khi "tất cả" = empty string)
if ($currentProvince && $currentProvince !== 'all') {
    $where[]  = 'r.province = ?';
    $params[] = $currentProvince;
}

if ($category) {
    $where[]  = 'c.slug = ?';
    $params[] = $category;
}
if ($search) {
    $where[]  = '(r.name LIKE ? OR r.description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($freeship) { $where[] = 'r.has_freeship = 1'; }
if ($deal)     { $where[] = 'r.has_deal = 1'; }

$whereStr = implode(' AND ', $where);
$orderBy  = match ($sort) {
    'rating'   => 'r.rating DESC, r.total_reviews DESC',
    'distance' => 'r.distance ASC',
    'time'     => 'r.delivery_time ASC',
    'price'    => 'r.delivery_fee ASC',
    default    => 'r.is_featured DESC, r.rating DESC',
};

$stmt = $conn->prepare("
    SELECT r.*, c.name AS category_name, c.icon AS category_icon
    FROM restaurants r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE {$whereStr}
    ORDER BY {$orderBy}
");
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

// Get current category info
$currentCat = null;
if ($category) {
    $stmtCat = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmtCat->execute([$category]);
    $currentCat = $stmtCat->fetch();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-xl mx-auto px-4 py-6">
    
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
            <a href="/foodbooking/" class="hover:text-cica-red transition">Trang chủ</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-semibold">
                <?= $currentCat ? e($currentCat['name']) : ($search ? "Kết quả cho \"" . e($search) . "\"" : 'Tất cả nhà hàng') ?>
            </span>
        </div>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">
                    <?= $currentCat ? e($currentCat['name']) : ($search ? "Kết quả: <em>\"" . e($search) . "\"</em>" : '🍽️ Tất Cả Nhà Hàng') ?>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    <span id="result-count"><?= count($restaurants) ?></span> kết quả tìm được
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Sort -->
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-600">Sắp xếp:</span>
                <div class="flex gap-1.5 flex-wrap">
                    <?php 
                    $sorts = ['default'=>'Phổ biến', 'rating'=>'Đánh giá', 'distance'=>'Gần nhất', 'time'=>'Nhanh nhất', 'price'=>'Phí ship'];
                    foreach ($sorts as $val => $label): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => $val])) ?>"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold transition <?= $sort === $val ? 'bg-cica-red text-white' : 'bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-cica-red' ?>">
                        <?= $label ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="w-px h-6 bg-gray-200 hidden md:block"></div>
            
            <!-- Quick filters -->
            <div class="flex gap-2 flex-wrap">
                <a href="?<?= http_build_query(array_merge($_GET, ['freeship' => $freeship ? 0 : 1])) ?>"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition <?= $freeship ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-green-50 hover:text-green-600' ?>">
                    <i class="fas fa-motorcycle"></i> Freeship
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['deal' => $deal ? 0 : 1])) ?>"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition <?= $deal ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' ?>">
                    <i class="fas fa-tag"></i> Deal hời
                </a>
            </div>

            <!-- Clear filters -->
            <?php if ($category || $search || $freeship || $deal || $sort !== 'default'): ?>
            <a href="/foodbooking/views/restaurant/list.php" class="ml-auto text-xs text-gray-400 hover:text-red-500 transition font-medium flex items-center gap-1">
                <i class="fas fa-times-circle"></i> Xoá bộ lọc
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Restaurant Grid -->
    <div id="restaurant-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($restaurants as $r): ?>
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100 group">
            <a href="/foodbooking/views/restaurant/detail.php?id=<?= $r['id'] ?>" class="block">
                    <div class="relative h-48 overflow-hidden">
                    <img src="<?= getImageUrl($r['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($r['name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700" loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                        <?php if ($r['is_featured']): ?>
                        <span class="bg-white/95 backdrop-blur-sm text-cica-red text-[9px] font-black px-2 py-1 rounded-full uppercase tracking-wider shadow">
                            <i class="fas fa-fire-flame-curved mr-1" style="color:#f97316"></i>Nổi bật
                        </span>
                        <?php endif; ?>
                    </div>
                    <button onclick="toggleFavorite(event, <?= $r['id'] ?>, this)" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:scale-110 transition z-10">
                        <i class="<?= in_array($r['id'], $userFavorites) ? 'fas text-cica-red' : 'far text-gray-400' ?> fa-heart"></i>
                    </button>
                    <div class="absolute bottom-3 right-3 flex gap-1.5 z-10">
                        <?php if ($r['has_freeship']): ?>
                        <span class="bg-green-500/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-md shadow">Freeship</span>
                        <?php endif; ?>
                        <?php if ($r['has_deal']): ?>
                        <span class="bg-orange-500/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-md shadow">Deal</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-base text-gray-800 mb-1 truncate group-hover:text-cica-red transition"><?= e($r['name']) ?></h3>
                    <p class="text-xs text-gray-400 mb-3 flex items-center gap-1.5">
                        <?php if ($r['category_icon']): ?>
                        <i class="fas <?= e($r['category_icon']) ?>" style="color:<?= e($r['color'] ?? '#ee2624') ?>"></i>
                        <?php endif; ?>
                        <?= e($r['category_name'] ?? 'Ẩm thực') ?>
                    </p>
                    <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1 text-yellow-500 font-bold">
                                <i class="fas fa-star"></i><?= $r['rating'] ?>
                            </span>
                            <span class="text-gray-300">|</span>
                            <span><i class="far fa-clock mr-1"></i><?= $r['delivery_time'] ?>'</span>
                            <span class="text-gray-300">|</span>
                            <span><?= $r['distance'] ?>km</span>
                        </div>
                        <span class="<?= $r['delivery_fee'] == 0 ? 'text-green-600 font-bold' : '' ?>">
                            <?= $r['delivery_fee'] == 0 ? '🚚 Free' : formatPrice($r['delivery_fee']) ?>
                        </span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty state -->
    <?php if (empty($restaurants)): ?>
    <div class="text-center py-20">
        <i class="fas fa-store-slash text-6xl text-gray-200 mb-6"></i>
        <h2 class="text-2xl font-black text-gray-400 mb-2">Không tìm thấy kết quả</h2>
        <p class="text-gray-400 mb-6">Thử tìm kiếm với từ khoá khác hoặc xoá bộ lọc</p>
        <a href="/foodbooking/views/restaurant/list.php" class="inline-block bg-cica-red text-white px-8 py-3.5 rounded-2xl font-bold hover:bg-red-700 transition">
            Xem tất cả nhà hàng
        </a>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>