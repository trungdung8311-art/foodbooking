<?php
// restaurant.php - Trang chi tiết nhà hàng + menu + giỏ hàng + đánh giá sao
require_once __DIR__ . '/../../config/database.php';

$restaurantId = (int)($_GET['id'] ?? 0);
if (!$restaurantId) { header('Location: /foodbooking/views/restaurant/list.php'); exit; }

// Lấy thông tin nhà hàng
$stmtR = $conn->prepare("
    SELECT r.*, c.name AS category_name, c.icon AS category_icon
    FROM restaurants r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.id = ?
");
$stmtR->execute([$restaurantId]);
$restaurant = $stmtR->fetch();
if (!$restaurant) { header('Location: /foodbooking/views/restaurant/list.php'); exit; }

// Menu categories
$stmtMC = $conn->prepare("SELECT * FROM menu_categories WHERE restaurant_id = ? ORDER BY sort_order");
$stmtMC->execute([$restaurantId]);
$menuCategories = $stmtMC->fetchAll();

// Menu items
$stmtItems = $conn->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? AND is_available = 1 ORDER BY menu_category_id, sort_order, is_best_seller DESC");
$stmtItems->execute([$restaurantId]);
$allItems = $stmtItems->fetchAll();

// Group items by category
$itemsByCategory = [];
foreach ($allItems as $item) {
    $itemsByCategory[$item['menu_category_id'] ?? 0][] = $item;
}

// Conflict cart check
$cartRestaurantId = $_SESSION['cart_restaurant_id'] ?? null;
$hasConflict = $cartRestaurantId && $cartRestaurantId != $restaurantId && !empty($_SESSION['cart']);

// Vouchers (quán + platform)
$stmtV = $conn->prepare("
    SELECT v.*,
    (SELECT COUNT(*) FROM user_vouchers uv WHERE uv.voucher_id = v.id AND uv.user_id = ?) AS is_saved
    FROM vouchers v
    WHERE (v.restaurant_id = ? OR v.restaurant_id IS NULL)
      AND v.end_date >= CURDATE() AND v.is_active = 1
    ORDER BY v.restaurant_id DESC
");
$stmtV->execute([$_SESSION['user_id'] ?? 0, $restaurantId]);
$restaurantVouchers = $stmtV->fetchAll();

// Reviews
$stmtReviews = $conn->prepare("
    SELECT rv.*, u.full_name, u.avatar
    FROM reviews rv
    JOIN users u ON rv.user_id = u.id
    WHERE rv.restaurant_id = ? AND rv.is_visible = 1
    ORDER BY rv.created_at DESC
    LIMIT 10
");
$stmtReviews->execute([$restaurantId]);
$reviews = $stmtReviews->fetchAll();

// Kiểm tra quyền đánh giá
$hasReviewed = false;
$canReview   = false;
if (isLoggedIn()) {
    $stmtMyRv = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND restaurant_id = ?");
    $stmtMyRv->execute([$_SESSION['user_id'], $restaurantId]);
    $hasReviewed = (bool)$stmtMyRv->fetch();

    if (!$hasReviewed) {
        $stmtCanRv = $conn->prepare("
            SELECT id FROM orders
            WHERE user_id = ? AND restaurant_id = ? AND status = 'completed'
            LIMIT 1
        ");
        $stmtCanRv->execute([$_SESSION['user_id'], $restaurantId]);
        $canReview = (bool)$stmtCanRv->fetch();
    }
}

$pageTitle = e($restaurant['name']) . ' - Cicafood | Đặt Đồ Ăn Online';
$pageDesc  = e($restaurant['description'] ?? "Đặt đồ ăn tại {$restaurant['name']} qua Cicafood");

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- COVER IMAGE -->
<div class="relative h-72 md:h-96 overflow-hidden">
    <img src="<?= getImageUrl($restaurant['cover_image'] ?: $restaurant['image'] ?? null, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80') ?>"
         alt="<?= e($restaurant['name']) ?>"
         class="w-full h-full object-cover"
         onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80'">
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

    <a href="javascript:history.back()"
       class="absolute top-4 left-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white transition shadow-lg z-10">
        <i class="fas fa-arrow-left"></i>
    </a>

    <button onclick="toggleFavorite(event, <?= $restaurantId ?>, this)"
       class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition shadow-lg z-10">
        <i class="<?= in_array($restaurantId, $userFavorites) ? 'fas text-cica-red' : 'far text-gray-400' ?> fa-heart"></i>
    </button>

    <div class="absolute bottom-0 left-0 right-0 px-6 pb-6">
        <div class="max-w-screen-xl mx-auto flex items-end gap-5">
            <div class="w-24 h-24 rounded-2xl overflow-hidden border-4 border-white shadow-xl flex-shrink-0 hidden md:block bg-gray-100">
                <img src="<?= getImageUrl($restaurant['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=200&q=80') ?>" alt=""
                     class="w-full h-full object-cover"
                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=200&q=80'">
            </div>
            <div class="flex-1 pb-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">
                        <i class="fas <?= e($restaurant['category_icon'] ?? 'fa-utensils') ?> mr-1"></i>
                        <?= e($restaurant['category_name'] ?? '') ?>
                    </span>
                    <?php if ($restaurant['is_open']): ?>
                    <span class="bg-green-500/80 text-white text-xs font-bold px-3 py-1 rounded-full">
                        <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i>Đang mở
                    </span>
                    <?php else: ?>
                    <span class="bg-gray-500/80 text-white text-xs font-bold px-3 py-1 rounded-full">Đã đóng</span>
                    <?php endif; ?>
                    <?php if ($restaurant['has_freeship']): ?>
                    <span class="bg-cica-red/80 text-white text-xs font-bold px-3 py-1 rounded-full">🚚 Freeship</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-white drop-shadow-lg"><?= e($restaurant['name']) ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="max-w-screen-xl mx-auto px-4 py-6">

    <!-- Restaurant Meta Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4 mb-6 flex flex-wrap items-center gap-6">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-yellow-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-star text-yellow-400 text-sm"></i>
            </div>
            <div>
                <p class="font-black text-gray-900 text-sm" id="avg-rating-display"><?= number_format($restaurant['rating'], 1) ?> / 5.0</p>
                <p class="text-xs text-gray-400" id="total-reviews-display"><?= number_format($restaurant['total_reviews']) ?> đánh giá</p>
            </div>
        </div>
        <div class="w-px h-10 bg-gray-100"></div>
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-blue-400 text-sm"></i>
            </div>
            <div>
                <p class="font-black text-gray-900 text-sm"><?= $restaurant['delivery_time'] ?> phút</p>
                <p class="text-xs text-gray-400">Thời gian giao</p>
            </div>
        </div>
        <div class="w-px h-10 bg-gray-100"></div>
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-location-dot text-green-400 text-sm"></i>
            </div>
            <div>
                <p class="font-black text-gray-900 text-sm"><?= $restaurant['distance'] ?> km</p>
                <p class="text-xs text-gray-400">Khoảng cách</p>
            </div>
        </div>
        <div class="w-px h-10 bg-gray-100"></div>
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-motorcycle text-cica-red text-sm"></i>
            </div>
            <div>
                <p class="font-black text-gray-900 text-sm">
                    <?= $restaurant['delivery_fee'] == 0 ? 'Miễn phí' : formatPrice($restaurant['delivery_fee']) ?>
                </p>
                <p class="text-xs text-gray-400">Phí giao hàng</p>
            </div>
        </div>
        <div class="w-px h-10 bg-gray-100 hidden md:block"></div>
        <div class="flex items-center gap-2 hidden md:flex">
            <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-shopping-bag text-orange-400 text-sm"></i>
            </div>
            <div>
                <p class="font-black text-gray-900 text-sm"><?= formatPrice($restaurant['min_order']) ?></p>
                <p class="text-xs text-gray-400">Đơn tối thiểu</p>
            </div>
        </div>
        <div class="ml-auto text-xs text-gray-400 hidden lg:block">
            <i class="fas fa-map-marker-alt text-cica-red mr-1"></i><?= e($restaurant['address']) ?>
        </div>
    </div>

    <!-- Conflict Cart Warning -->
    <?php if ($hasConflict): ?>
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i class="fas fa-triangle-exclamation text-orange-500 text-xl"></i>
            <div>
                <p class="font-bold text-orange-800 text-sm">Giỏ hàng đang có món từ nhà hàng khác</p>
                <p class="text-orange-600 text-xs">Xoá giỏ hàng cũ để thêm món từ <?= e($restaurant['name']) ?></p>
            </div>
        </div>
        <button onclick="clearAndSwitch()" class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-orange-600 transition flex-shrink-0">
            Xoá & Tiếp tục
        </button>
    </div>
    <?php endif; ?>

    <div class="flex gap-6">
        <!-- LEFT: Menu -->
        <div class="flex-1 min-w-0">

            <!-- Vouchers -->
            <?php if (!empty($restaurantVouchers)): ?>
            <div class="mb-5 overflow-hidden">
                <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-cica-red"></i> Vouchers Khuyến Mãi
                </h3>
                <div class="flex gap-3 overflow-x-auto pb-2 -mx-2 px-2" style="scrollbar-width:none;">
                    <?php foreach ($restaurantVouchers as $v): ?>
                    <div class="flex-shrink-0 w-64 bg-white border border-red-100 rounded-xl flex shadow-sm hover:shadow-md transition">
                        <div class="w-16 bg-red-50 flex flex-col items-center justify-center border-r border-dashed border-red-200 rounded-l-xl">
                            <i class="fas fa-gift text-cica-red text-xl mb-1"></i>
                        </div>
                        <div class="flex-1 p-2.5 flex flex-col pt-3 pb-3">
                            <p class="text-xs font-black text-gray-800 mb-0.5 line-clamp-1"><?= e($v['name']) ?></p>
                            <p class="text-[10px] text-gray-500 line-clamp-1 leading-tight mb-1">
                                <?= $v['type'] === 'percent' ? $v['value'].'% giảm' : ($v['type'] === 'freeship' ? 'Freeship' : formatPrice($v['value']).' giảm') ?>
                                · Đơn từ <?= formatPrice($v['min_order']) ?>
                            </p>
                            <div class="mt-auto flex items-center justify-between">
                                <span class="text-[10px] text-gray-400 font-mono font-bold"><?= e($v['code']) ?></span>
                                <?php if ($v['is_saved']): ?>
                                <button disabled class="text-[10px] font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full cursor-not-allowed">Đã lưu</button>
                                <?php else: ?>
                                <button onclick="saveVoucher(<?= $v['id'] ?>, this)"
                                        class="text-[10px] font-bold text-white bg-cica-red hover:bg-red-700 transition px-3 py-1 rounded-full shadow-sm shadow-red-200">Lưu</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ghi chú -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-pen-to-square text-cica-red mr-2"></i>Ghi chú cho quán
                </label>
                <textarea id="order-note" rows="2" placeholder="Ví dụ: Ít đường, nhiều đá, không hành..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 outline-none focus:border-red-400 resize-none transition"
                          onchange="saveNote(this.value)"></textarea>
            </div>

            <!-- Menu Category Tabs -->
            <?php if (!empty($menuCategories)): ?>
            <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-6" style="scrollbar-width:none">
                <button onclick="filterMenu('all')" data-tab="all"
                        class="menu-tab flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-bold bg-cica-red text-white transition">
                    Tất cả
                </button>
                <?php foreach ($menuCategories as $mc): ?>
                <button onclick="filterMenu(<?= $mc['id'] ?>)" data-tab="<?= $mc['id'] ?>"
                        class="menu-tab flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-semibold bg-white border border-gray-200 text-gray-600 hover:border-red-300 hover:text-cica-red transition">
                    <?= e($mc['name']) ?>
                </button>
                <?php endforeach; ?>
                <!-- Tab đánh giá -->
                <button onclick="scrollToReviews()" data-tab="reviews"
                        class="menu-tab flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-semibold bg-white border border-gray-200 text-gray-600 hover:border-yellow-400 hover:text-yellow-600 transition flex items-center gap-1.5">
                    <i class="fas fa-star text-yellow-400 text-xs"></i> Đánh giá
                    <span class="bg-yellow-100 text-yellow-700 text-[10px] font-black px-1.5 rounded-full"><?= $restaurant['total_reviews'] ?></span>
                </button>
            </div>
            <?php endif; ?>

            <!-- Menu Items -->
            <?php foreach ($menuCategories as $mc): ?>
            <?php $items = $itemsByCategory[$mc['id']] ?? []; if (empty($items)) continue; ?>
            <div class="menu-section mb-8" data-category="<?= $mc['id'] ?>">
                <h2 class="text-lg font-black text-gray-900 mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <span class="w-1 h-6 bg-cica-red rounded-full"></span>
                    <?= e($mc['name']) ?>
                    <span class="text-sm font-normal text-gray-400">(<?= count($items) ?> món)</span>
                </h2>
                <div class="space-y-3">
                    <?php foreach ($items as $item): ?>
                    <div class="menu-item bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex gap-4 hover:shadow-md transition group"
                         data-category="<?= $item['menu_category_id'] ?>" data-id="<?= $item['id'] ?>">
                        <div class="relative w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100">
                            <img src="<?= getImageUrl($item['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80') ?>"
                                 alt="<?= e($item['name']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                 loading="lazy"
                                 onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=200&q=80'">
                            <?php if ($item['is_best_seller']): ?>
                            <span class="absolute top-1 left-1 bg-orange-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md leading-tight">
                                Best Seller
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-800 text-base mb-1 truncate"><?= e($item['name']) ?></h3>
                            <?php if ($item['description']): ?>
                            <p class="text-xs text-gray-500 mb-2 line-clamp-2"><?= e($item['description']) ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between mt-auto">
                                <div class="flex items-center gap-2">
                                    <span class="text-cica-red font-black text-lg"><?= formatPrice($item['price']) ?></span>
                                    <?php if ($item['original_price']): ?>
                                    <span class="text-gray-400 text-sm line-through"><?= formatPrice($item['original_price']) ?></span>
                                    <?php if ($item['original_price'] > $item['price']): $disc = round((1-$item['price']/$item['original_price'])*100); ?>
                                    <span class="text-xs bg-red-100 text-cica-red font-bold px-1.5 py-0.5 rounded-md">-<?= $disc ?>%</span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2" id="item-control-<?= $item['id'] ?>">
                                    <?php
                                    $inCart = isset($_SESSION['cart'][$item['id']]);
                                    $qty    = $inCart ? $_SESSION['cart'][$item['id']]['quantity'] : 0;
                                    ?>
                                    <?php if ($inCart): ?>
                                    <div class="flex items-center gap-2 bg-red-50 rounded-xl px-1">
                                        <button onclick="changeQty(<?= $item['id'] ?>, -1)" class="w-7 h-7 flex items-center justify-center text-cica-red font-bold hover:bg-red-100 rounded-lg transition text-lg leading-none">−</button>
                                        <span class="text-gray-800 font-bold text-sm min-w-[20px] text-center" id="qty-<?= $item['id'] ?>"><?= $qty ?></span>
                                        <button onclick="changeQty(<?= $item['id'] ?>, 1)" class="w-7 h-7 flex items-center justify-center text-cica-red font-bold hover:bg-red-100 rounded-lg transition text-lg leading-none">+</button>
                                    </div>
                                    <?php else: ?>
                                    <button onclick="addToCart(<?= $item['id'] ?>, <?= $restaurant['id'] ?>)"
                                            class="w-9 h-9 bg-cica-red text-white rounded-xl flex items-center justify-center shadow-sm shadow-red-200 hover:bg-red-700 transition active:scale-90">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- ======================================================== -->
            <!-- ĐÁNH GIÁ SAO -->
            <!-- ======================================================== -->
            <div id="reviews-section" class="mb-10">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                            <i class="fas fa-star text-yellow-400"></i> Đánh Giá & Nhận Xét
                        </h2>
                        <p class="text-sm text-gray-400 mt-0.5">
                            Trung bình <strong class="text-yellow-500" id="avg-rating-big"><?= number_format($restaurant['rating'], 1) ?></strong>/5 từ
                            <span id="total-big"><?= number_format($restaurant['total_reviews']) ?></span> đánh giá
                        </p>
                    </div>
                    <!-- Hiển thị sao trung bình -->
                    <div class="flex items-center gap-1 text-2xl" id="avg-stars-display">
                        <?php
                        $avgRating = (float)$restaurant['rating'];
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= floor($avgRating)) $starClass = 'fas fa-star text-yellow-400';
                            elseif ($i == ceil($avgRating) && $avgRating != floor($avgRating)) $starClass = 'fas fa-star-half-stroke text-yellow-400';
                            else $starClass = 'far fa-star text-gray-300';
                        ?>
                        <i class="<?= $starClass ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Form đánh giá -->
                <?php if (!isLoggedIn()): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 mb-5 text-center">
                    <i class="fas fa-user-lock text-gray-300 text-3xl mb-2"></i>
                    <p class="text-gray-500 text-sm font-medium mb-3">Vui lòng đăng nhập để đánh giá quán</p>
                    <a href="/foodbooking/views/auth/login.php" class="bg-cica-red text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition inline-block">
                        Đăng nhập ngay
                    </a>
                </div>
                <?php elseif ($hasReviewed): ?>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    <p class="text-green-700 font-medium text-sm">Bạn đã đánh giá quán này rồi. Cảm ơn bạn!</p>
                </div>
                <?php elseif ($canReview): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-5 shadow-sm" id="review-form-card">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-edit text-cica-red"></i> Viết đánh giá của bạn
                    </h3>
                    <!-- Chọn sao -->
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-sm font-semibold text-gray-600 mr-1">Chất lượng:</span>
                        <div class="flex gap-1" id="star-picker">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                            <button type="button" onclick="selectStar(<?= $s ?>)"
                                    data-star="<?= $s ?>"
                                    class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition-all duration-100 active:scale-95"
                                    title="<?= $s ?> sao">
                                ★
                            </button>
                            <?php endfor; ?>
                        </div>
                        <span id="star-label" class="text-sm text-gray-400 ml-2"></span>
                    </div>
                    <!-- Nhận xét -->
                    <textarea id="review-comment" rows="3"
                              placeholder="Chia sẻ trải nghiệm của bạn về món ăn, dịch vụ, thái độ phục vụ..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 outline-none focus:border-yellow-400 resize-none transition mb-4"></textarea>
                    <button onclick="submitReview()"
                            id="submit-review-btn"
                            class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black px-6 py-2.5 rounded-xl transition shadow-sm active:scale-95 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Gửi Đánh Giá
                    </button>
                </div>
                <?php else: ?>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                    <i class="fas fa-info-circle text-amber-500 text-xl"></i>
                    <p class="text-amber-700 text-sm">Bạn cần hoàn thành đơn hàng tại quán này để có thể đánh giá.</p>
                </div>
                <?php endif; ?>

                <!-- Danh sách reviews -->
                <div id="reviews-list" class="space-y-4">
                    <?php if (empty($reviews)): ?>
                    <div class="text-center py-8 text-gray-400" id="no-reviews-msg">
                        <i class="fas fa-comment-slash text-4xl mb-3 text-gray-200"></i>
                        <p class="font-medium">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 bg-gray-100">
                                <?php if (!empty($rv['avatar']) && $rv['avatar'] !== 'default_avatar.png'): ?>
                                <img src="<?= getImageUrl($rv['avatar']) ?>" class="w-full h-full object-cover"
                                     onerror="this.style.display='none'">
                                <?php else: ?>
                                <div class="w-full h-full bg-cica-red flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?= strtoupper(mb_substr($rv['full_name'], 0, 1)) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-bold text-gray-800 text-sm"><?= e($rv['full_name']) ?></span>
                                    <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($rv['created_at'])) ?></span>
                                </div>
                                <div class="flex gap-0.5 mb-2">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <span class="text-sm <?= $s <= $rv['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($rv['comment']): ?>
                                <p class="text-sm text-gray-600 leading-relaxed"><?= nl2br(e($rv['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- /ĐÁNH GIÁ -->

        </div><!-- /LEFT -->

        <!-- RIGHT: Cart Sidebar -->
        <div class="hidden lg:block w-80 flex-shrink-0">
            <div class="sticky top-36">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" id="cart-sidebar">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-black text-gray-900 flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-cica-red"></i> Giỏ Hàng
                            <span class="bg-cica-red text-white text-xs font-bold rounded-full px-2 py-0.5" id="sidebar-count">0</span>
                        </h3>
                        <button onclick="clearCart()" class="text-xs text-gray-400 hover:text-red-500 transition font-medium">Xoá tất cả</button>
                    </div>

                    <div id="cart-empty" class="<?= empty($_SESSION['cart']) || $hasConflict ? '' : 'hidden' ?> p-8 text-center">
                        <i class="fas fa-shopping-bag text-5xl text-gray-200 mb-4"></i>
                        <p class="text-gray-400 font-medium text-sm">Giỏ hàng trống</p>
                        <p class="text-gray-300 text-xs mt-1">Thêm món để bắt đầu!</p>
                    </div>

                    <div id="cart-items" class="<?= (empty($_SESSION['cart']) || $hasConflict) ? 'hidden' : '' ?> max-h-[300px] overflow-y-auto p-4 space-y-3">
                        <?php if (!empty($_SESSION['cart']) && !$hasConflict): ?>
                        <?php foreach ($_SESSION['cart'] as $cartItemId => $cartItem): ?>
                        <div class="flex items-center gap-3" id="cart-row-<?= $cartItemId ?>">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate"><?= e($cartItem['name']) ?></p>
                                <p class="text-xs text-cica-red font-bold mt-0.5"><?= formatPrice($cartItem['price']) ?></p>
                            </div>
                            <div class="flex items-center gap-1.5 bg-gray-50 rounded-xl px-1">
                                <button onclick="changeQty(<?= $cartItemId ?>, -1)" class="w-6 h-6 flex items-center justify-center text-cica-red font-bold hover:bg-red-50 rounded-lg text-base">−</button>
                                <span class="text-xs font-bold text-gray-800 min-w-[16px] text-center" id="qty-<?= $cartItemId ?>"><?= $cartItem['quantity'] ?></span>
                                <button onclick="changeQty(<?= $cartItemId ?>, 1)" class="w-6 h-6 flex items-center justify-center text-cica-red font-bold hover:bg-red-50 rounded-lg text-base">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="cart-totals" class="<?= (empty($_SESSION['cart']) || $hasConflict) ? 'hidden' : '' ?> p-4 border-t border-gray-100 space-y-2.5">
                        <?php
                        $subtotal   = getCartSubtotal();
                        $shipFee    = $restaurant['delivery_fee'];
                        $serviceFee = (int)($subtotal * SERVICE_FEE_PERCENT / 100);
                        $total      = $subtotal + $shipFee + $serviceFee;
                        ?>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tạm tính</span>
                            <span class="font-semibold" id="subtotal-display"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Phí giao hàng</span>
                            <span class="<?= $shipFee == 0 ? 'text-green-600 font-bold' : '' ?>" id="ship-display">
                                <?= $shipFee == 0 ? 'Miễn phí' : formatPrice($shipFee) ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Phí dịch vụ (<?= SERVICE_FEE_PERCENT ?>%)</span>
                            <span id="service-display"><?= formatPrice($serviceFee) ?></span>
                        </div>
                        <div class="border-t border-dashed border-gray-200 pt-2.5 flex justify-between font-black text-lg">
                            <span class="text-gray-900">Tổng cộng</span>
                            <span class="text-cica-red" id="total-display"><?= formatPrice($total) ?></span>
                        </div>
                        <a href="/foodbooking/views/order/checkout.php"
                           class="block w-full bg-cica-red text-white py-3.5 rounded-2xl font-black text-center hover:bg-red-700 transition shadow-md shadow-red-100 active:scale-95 mt-2">
                            Đặt Hàng Ngay <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                        <p class="text-center text-xs text-gray-400 mt-1">
                            Tối thiểu <?= formatPrice($restaurant['min_order']) ?> · Giao trong <?= $restaurant['delivery_time'] ?> phút
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Checkout Bar -->
<div id="mobile-checkout-bar"
     class="<?= empty($_SESSION['cart']) ? 'hidden' : '' ?> fixed bottom-0 left-0 right-0 lg:hidden bg-white border-t border-gray-200 px-4 py-3 z-50 shadow-2xl">
    <a href="/foodbooking/views/order/checkout.php" class="flex items-center justify-between bg-cica-red text-white px-5 py-4 rounded-2xl font-bold shadow-lg active:scale-95">
        <span class="bg-red-700 text-white text-xs font-black rounded-lg px-2 py-1" id="mobile-cart-count"><?= getCartCount() ?></span>
        <span>Xem giỏ hàng</span>
        <span id="mobile-total-display"><?= formatPrice(getCartSubtotal()) ?></span>
    </a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
const RESTAURANT_ID = <?= $restaurant['id'] ?>;
const SHIP_FEE      = <?= $restaurant['delivery_fee'] ?>;
const SERVICE_RATE  = <?= SERVICE_FEE_PERCENT ?>;

// ─── Menu tab filtering ───
function filterMenu(categoryId) {
    document.querySelectorAll('.menu-tab').forEach(btn => {
        const isActive = String(btn.dataset.tab) === String(categoryId);
        if (btn.dataset.tab === 'reviews') return; // skip review tab
        if (isActive) {
            btn.className = btn.className.replace(/bg-white border border-gray-200 text-gray-600/g, '').trim();
            btn.classList.add('bg-cica-red','text-white');
            btn.classList.remove('border','border-gray-200','text-gray-600');
        } else {
            btn.classList.remove('bg-cica-red','text-white');
            if (!btn.classList.contains('bg-white')) btn.classList.add('bg-white','border','border-gray-200','text-gray-600');
        }
    });
    document.querySelectorAll('.menu-section').forEach(s => {
        s.style.display = (categoryId === 'all' || s.dataset.category == categoryId) ? '' : 'none';
    });
    document.getElementById('reviews-section').style.display = 'block';
}

function scrollToReviews() {
    document.getElementById('reviews-section').scrollIntoView({behavior:'smooth', block:'start'});
}

// ─── Đánh giá sao ───
let selectedStar = 0;
const starLabels = ['', 'Rất tệ 😞', 'Tệ 😕', 'Bình thường 😐', 'Tốt 😊', 'Tuyệt vời 🤩'];

function selectStar(n) {
    selectedStar = n;
    document.querySelectorAll('.star-btn').forEach((btn, i) => {
        btn.style.color = (i < n) ? '#facc15' : '#d1d5db';
        btn.style.transform = (i < n) ? 'scale(1.1)' : 'scale(1)';
    });
    const lbl = document.getElementById('star-label');
    if (lbl) lbl.textContent = starLabels[n];
}

function submitReview() {
    if (!selectedStar) { showToast('error', 'Vui lòng chọn số sao đánh giá!'); return; }
    const comment   = document.getElementById('review-comment').value.trim();
    const btn       = document.getElementById('submit-review-btn');
    const origHTML  = btn.innerHTML;
    btn.disabled    = true;
    btn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

    fetch('/foodbooking/api/review/submit_rating.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({restaurant_id: RESTAURANT_ID, rating: selectedStar, comment})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            // Cập nhật rating hiển thị
            document.getElementById('avg-rating-display').textContent = data.new_rating + ' / 5.0';
            document.getElementById('total-reviews-display').textContent = data.total_reviews + ' đánh giá';
            document.getElementById('avg-rating-big').textContent = data.new_rating;
            document.getElementById('total-big').textContent = data.total_reviews;

            // Ẩn form, hiện review mới
            document.getElementById('review-form-card').style.display = 'none';
            prependNewReview(data.review);
        } else {
            showToast('error', data.message);
            btn.disabled  = false;
            btn.innerHTML = origHTML;
        }
    })
    .catch(() => { showToast('error','Lỗi kết nối'); btn.disabled=false; btn.innerHTML=origHTML; });
}

function prependNewReview(rv) {
    const noMsg = document.getElementById('no-reviews-msg');
    if (noMsg) noMsg.remove();

    const stars = Array.from({length:5},(_,i)=>`<span class="text-sm ${i<rv.rating?'text-yellow-400':'text-gray-200'}">★</span>`).join('');
    const html = `
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm animate-fadeIn">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-cica-red flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">${rv.user_name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-bold text-gray-800 text-sm">${rv.user_name}</span>
                        <span class="text-xs text-gray-400">${rv.created_at}</span>
                    </div>
                    <div class="flex gap-0.5 mb-2">${stars}</div>
                    ${rv.comment ? `<p class="text-sm text-gray-600 leading-relaxed">${rv.comment}</p>` : ''}
                </div>
            </div>
        </div>`;
    document.getElementById('reviews-list').insertAdjacentHTML('afterbegin', html);
}

// ─── Cart functions ───
function changeQty(itemId, delta) {
    const qtyEl = document.getElementById('qty-' + itemId);
    const currentQty = parseInt(qtyEl?.textContent || 0);
    const newQty = currentQty + delta;
    const action = newQty <= 0 ? 'remove' : 'update';

    fetch('/foodbooking/api/cart/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({item_id: itemId, action, quantity: newQty})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            updateSidebarTotals(data.subtotal);
            if (action === 'remove') {
                const row = document.getElementById('cart-row-' + itemId);
                if (row) row.remove();
                const ctrl = document.getElementById('item-control-' + itemId);
                if (ctrl) ctrl.innerHTML = `<button onclick="addToCart(${itemId},${RESTAURANT_ID})" class="w-9 h-9 bg-cica-red text-white rounded-xl flex items-center justify-center shadow-sm shadow-red-200 hover:bg-red-700 transition active:scale-90"><i class="fas fa-plus text-sm"></i></button>`;
                if (Object.keys(data.cart).length === 0) showEmptyCart();
            } else {
                document.querySelectorAll('[id="qty-' + itemId + '"]').forEach(el => el.textContent = newQty);
            }
            updateMobileBar(data.cart_count, data.subtotal);
        }
    });
}

function addToCart(itemId, restaurantId) {
    fetch('/foodbooking/api/cart/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({item_id: itemId, restaurant_id: restaurantId, quantity: 1})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            updateCartBadge(data.cart_count);
            const ctrl = document.getElementById('item-control-' + itemId);
            if (ctrl) {
                const it = data.cart[itemId];
                ctrl.innerHTML = `<div class="flex items-center gap-2 bg-red-50 rounded-xl px-1"><button onclick="changeQty(${itemId},-1)" class="w-7 h-7 flex items-center justify-center text-cica-red font-bold hover:bg-red-100 rounded-lg transition text-lg leading-none">−</button><span class="text-gray-800 font-bold text-sm min-w-[20px] text-center" id="qty-${itemId}">${it?.quantity||1}</span><button onclick="changeQty(${itemId},1)" class="w-7 h-7 flex items-center justify-center text-cica-red font-bold hover:bg-red-100 rounded-lg transition text-lg leading-none">+</button></div>`;
            }
            addToCartSidebar(itemId, data.cart[itemId]);
            const sub = Object.values(data.cart).reduce((s,i)=>s+i.price*i.quantity,0);
            updateSidebarTotals(sub);
            updateMobileBar(data.cart_count, sub);
        } else if (data.conflict) {
            if (confirm(data.message + '\n\nBấm OK để xoá giỏ hàng cũ và thêm mới.')) {
                clearAndSwitch();
                setTimeout(() => addToCart(itemId, restaurantId), 300);
            }
        } else { showToast('error', data.message); }
    });
}

function addToCartSidebar(itemId, item) {
    const cartItems  = document.getElementById('cart-items');
    const cartEmpty  = document.getElementById('cart-empty');
    const cartTotals = document.getElementById('cart-totals');
    cartEmpty.classList.add('hidden');
    cartItems.classList.remove('hidden');
    cartTotals.classList.remove('hidden');
    if (document.getElementById('cart-row-' + itemId)) {
        document.querySelectorAll('[id="qty-' + itemId + '"]').forEach(el => el.textContent = item.quantity);
        return;
    }
    const row = document.createElement('div');
    row.className = 'flex items-center gap-3';
    row.id = 'cart-row-' + itemId;
    row.innerHTML = `<div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-800 truncate">${item.name}</p><p class="text-xs text-cica-red font-bold mt-0.5">${formatVND(item.price)}</p></div><div class="flex items-center gap-1.5 bg-gray-50 rounded-xl px-1"><button onclick="changeQty(${itemId},-1)" class="w-6 h-6 flex items-center justify-center text-cica-red font-bold hover:bg-red-50 rounded-lg text-base">−</button><span class="text-xs font-bold text-gray-800 min-w-[16px] text-center" id="qty-${itemId}">${item.quantity}</span><button onclick="changeQty(${itemId},1)" class="w-6 h-6 flex items-center justify-center text-cica-red font-bold hover:bg-red-50 rounded-lg text-base">+</button></div>`;
    cartItems.appendChild(row);
}

function updateSidebarTotals(subtotal) {
    const svc   = Math.round(subtotal * SERVICE_RATE / 100);
    const total = subtotal + SHIP_FEE + svc;
    const el = (id, v) => { const e = document.getElementById(id); if(e) e.textContent = v; };
    el('subtotal-display', formatVND(subtotal));
    el('service-display', formatVND(svc));
    el('total-display', formatVND(total));
    document.getElementById('sidebar-count').textContent = document.querySelectorAll('#cart-items > div').length;
}

function updateMobileBar(count, subtotal) {
    const bar = document.getElementById('mobile-checkout-bar');
    if (count > 0) {
        bar?.classList.remove('hidden');
        const c = document.getElementById('mobile-cart-count'); if(c) c.textContent = count;
        const t = document.getElementById('mobile-total-display'); if(t) t.textContent = formatVND(subtotal);
    } else { bar?.classList.add('hidden'); }
}

function showEmptyCart() {
    document.getElementById('cart-empty').classList.remove('hidden');
    document.getElementById('cart-items').classList.add('hidden');
    document.getElementById('cart-totals').classList.add('hidden');
    document.getElementById('mobile-checkout-bar')?.classList.add('hidden');
}

function clearCart() {
    if (!confirm('Xoá tất cả món trong giỏ hàng?')) return;
    fetch('/foodbooking/api/cart/update_cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'clear'})})
    .then(r=>r.json()).then(data=>{
        if(data.success){
            document.getElementById('cart-items').innerHTML='';
            showEmptyCart(); updateCartBadge(0);
            document.querySelectorAll('.menu-item').forEach(mi=>{
                const itemId=mi.dataset.id;
                const ctrl=mi.querySelector('[id^="item-control-"]');
                if(ctrl) ctrl.innerHTML=`<button onclick="addToCart(${itemId},${RESTAURANT_ID})" class="w-9 h-9 bg-cica-red text-white rounded-xl flex items-center justify-center shadow-sm shadow-red-200 hover:bg-red-700 transition active:scale-90"><i class="fas fa-plus text-sm"></i></button>`;
            });
        }
    });
}

function clearAndSwitch() {
    fetch('/foodbooking/api/cart/update_cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'clear'})})
    .then(r=>r.json()).then(()=>location.reload());
}

function saveNote(note) { sessionStorage.setItem('order_note',note); }

function saveVoucher(voucherId, btn) {
    btn.disabled = true;
    fetch('/foodbooking/api/voucher/save_voucher.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({voucher_id:voucherId})})
    .then(r=>r.json()).then(data=>{
        if(data.success){
            showToast('success',data.message);
            btn.textContent='Đã lưu';
            btn.className='text-[10px] font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full cursor-not-allowed';
        } else { showToast('error',data.message); btn.disabled=false; }
    }).catch(()=>{btn.disabled=false;});
}

function toggleFavorite(event, restaurantId, btn) {
    event.preventDefault(); event.stopPropagation();
    const icon = btn.querySelector('i');
    fetch('/foodbooking/api/restaurant/toggle_favorite.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({restaurant_id:restaurantId})})
    .then(r=>r.json()).then(data=>{
        if(data.success){
            icon.className = data.favorited ? 'fas text-cica-red fa-heart' : 'far text-gray-400 fa-heart';
            showToast(data.favorited ? 'success' : 'info', data.favorited ? 'Đã thêm yêu thích!' : 'Đã bỏ yêu thích');
        } else if(data.login_required) { window.location.href='/foodbooking/views/auth/login.php'; }
    });
}

function formatVND(n) { return n.toLocaleString('vi-VN')+'đ'; }

// Init sidebar count
document.getElementById('sidebar-count').textContent = document.querySelectorAll('#cart-items > div').length;
</script>
