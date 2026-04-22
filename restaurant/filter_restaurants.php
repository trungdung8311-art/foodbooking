<?php
// api/restaurant/filter_restaurants.php - AJAX: Lọc nhà hàng (V2 + Province)
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$category = $_GET['category'] ?? '';
$sort     = $_GET['sort']     ?? 'default';
$search   = trim($_GET['q']  ?? '');
$freeship = (int)($_GET['freeship'] ?? 0);
$deal     = (int)($_GET['deal']     ?? 0);
$province = trim($_GET['province']  ?? '');

$where  = ['r.is_open = 1'];
$params = [];

if ($category) {
    $where[]  = 'c.slug = ?';
    $params[] = $category;
}
if ($search) {
    $where[]  = '(r.name LIKE ? OR r.description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($freeship) {
    $where[] = 'r.has_freeship = 1';
}
if ($deal) {
    $where[] = 'r.has_deal = 1';
}
// Lọc theo tỉnh thành (bỏ qua khi province = 'all' hoặc rỗng)
if ($province && $province !== 'all') {
    $where[]  = 'r.province = ?';
    $params[] = $province;
}

$whereStr = implode(' AND ', $where);

$orderBy = match ($sort) {
    'rating'   => 'r.rating DESC, r.total_reviews DESC',
    'distance' => 'r.distance ASC',
    'time'     => 'r.delivery_time ASC',
    'price'    => 'r.delivery_fee ASC',
    default    => 'r.is_featured DESC, r.rating DESC'
};

$sql = "
    SELECT r.*, c.name AS category_name, c.icon AS category_icon
    FROM restaurants r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE {$whereStr}
    ORDER BY {$orderBy}
    LIMIT 24
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();


// Render HTML
$userFavorites = [];
if (isLoggedIn()) {
    $stmtFav = $conn->prepare("SELECT restaurant_id FROM favorites WHERE user_id = ?");
    $stmtFav->execute([$_SESSION['user_id']]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

ob_start();
foreach ($restaurants as $r):
    $ratingStars = round($r['rating']);

?>
<div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100 group cursor-pointer">
    <a href="/foodbooking/views/restaurant/detail.php?id=<?= $r['id'] ?>" class="block">
        <div class="relative h-48 overflow-hidden">
            <img src="<?= getImageUrl($r['image'] ?? null, 'https://images.unsplash.com/photo-1504674900247?auto=format&fit=crop&w=800&q=80') ?>" 
                 alt="<?= e($r['name']) ?>" 
                 class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                 loading="lazy">
            
            <!-- Badges -->
            <div class="absolute top-3 left-3 flex gap-2 z-10">
                <?php if ($r['is_featured']): ?>
                <span class="bg-white/95 backdrop-blur-sm text-cica-red text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                    <i class="fas fa-fire-flame-curved mr-1" style="color:#f97316"></i>Hot
                </span>
                <?php endif; ?>
            </div>
            <button onclick="toggleFavorite(event, <?= $r['id'] ?>, this)" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:scale-110 transition z-10">
                <i class="<?= in_array($r['id'], $userFavorites) ? 'fas text-cica-red' : 'far text-gray-400' ?> fa-heart"></i>
            </button>
            <div class="absolute bottom-3 right-3 flex gap-2">
                <?php if ($r['has_freeship']): ?>
                <span class="bg-cica-red text-white text-[10px] font-bold px-2.5 py-1 rounded-lg shadow">
                    <i class="fas fa-motorcycle mr-1"></i>Freeship
                </span>
                <?php endif; ?>
                <?php if ($r['has_deal']): ?>
                <span class="bg-orange-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg shadow">
                    <i class="fas fa-tag mr-1"></i>Deal
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-base text-gray-800 mb-1 truncate group-hover:text-cica-red transition"><?= e($r['name']) ?></h3>
            <p class="text-xs text-gray-400 mb-3 truncate"><?= e($r['category_name'] ?? '') ?> • <?= e($r['address']) ?></p>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1 text-yellow-500 font-bold">
                        <i class="fas fa-star text-[11px]"></i><?= $r['rating'] ?>
                    </span>
                    <span class="text-gray-300">|</span>
                    <span><i class="far fa-clock mr-1"></i><?= $r['delivery_time'] ?> phút</span>
                    <span class="text-gray-300">|</span>
                    <span><?= $r['distance'] ?> km</span>
                </div>
                <span class="<?= $r['delivery_fee'] == 0 ? 'text-green-600 font-bold' : 'text-gray-500' ?>">
                    <?= $r['delivery_fee'] == 0 ? '<i class="fas fa-truck mr-1"></i>Miễn phí' : formatPrice($r['delivery_fee']) ?>
                </span>
            </div>
        </div>
    </a>
</div>
<?php endforeach;

$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'count'   => count($restaurants),
    'html'    => $html,
]);
?>
