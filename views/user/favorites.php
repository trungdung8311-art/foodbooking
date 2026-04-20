<?php
// views/user/favorites.php - Danh sách nhà hàng yêu thích
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/provinces.php';
requireLogin();

$pageTitle = 'Quán Yêu Thích - Cicafood';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT r.*, c.name AS category_name, c.icon AS category_icon
    FROM restaurants r
    JOIN favorites f ON r.id = f.restaurant_id
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE f.user_id = ? AND r.is_open = 1
    ORDER BY f.created_at DESC
");
$stmt->execute([$userId]);
$restaurants = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-xl mx-auto px-4 py-6">
    <div class="mb-6 flex items-center gap-3">
        <a href="/foodbooking/views/user/profile.php" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-50 transition shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-heart text-cica-red"></i> Quán Yêu Thích
            </h1>
            <p class="text-sm text-gray-500 mt-1">Đã lưu <?= count($restaurants) ?> quán</p>
        </div>
    </div>

    <div id="restaurant-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($restaurants as $r): ?>
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100 group">
            <a href="/foodbooking/views/restaurant/detail.php?id=<?= $r['id'] ?>" class="block">
                <div class="relative h-48 overflow-hidden">
                    <img src="<?= getImageUrl($r['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($r['name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                        <?php if ($r['is_featured']): ?>
                        <span class="bg-white/95 backdrop-blur-sm text-cica-red text-[9px] font-black px-2 py-1 rounded-full uppercase tracking-wider shadow">
                            <i class="fas fa-fire-flame-curved mr-1" style="color:#f97316"></i>Nổi bật
                        </span>
                        <?php endif; ?>
                    </div>
                    <button onclick="toggleFavorite(event, <?= $r['id'] ?>, this)" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:scale-110 transition z-10">
                        <i class="fas text-cica-red fa-heart"></i>
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

    <?php if (empty($restaurants)): ?>
    <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-heart-crack text-4xl text-gray-300"></i>
        </div>
        <h2 class="text-xl font-black text-gray-900 mb-2">Chưa có quán yêu thích nào</h2>
        <p class="text-gray-500 mb-6 text-sm max-w-sm mx-auto">Hãy thả tim cho các quán ngon để lưu lại và đặt món nhanh chóng hơn nhé!</p>
        <a href="/foodbooking/views/restaurant/list.php" class="inline-block bg-cica-red text-white px-8 py-3.5 rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">
            Khám phá quán ngon
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
