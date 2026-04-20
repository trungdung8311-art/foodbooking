<?php
// includes/header.php - Header 2 tầng dùng chung
// Biến $pageTitle phải được set trước khi include file này
if (!isset($pageTitle)) $pageTitle = 'Cicafood - Đặt Đồ Ăn Trực Tuyến';
if (!isset($pageDesc))  $pageDesc  = 'Đặt đồ ăn trực tuyến nhanh chóng, tiện lợi với hàng nghìn quán ngon toàn quốc - Cicafood';

require_once __DIR__ . '/provinces.php';
$provincesList = getProvinces();

// Handle province change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_province'])) {
    $selectedProv = $_POST['province_name'] ?? 'TP. Hồ Chí Minh';
    // 'all' = no filter
    $_SESSION['current_province'] = ($selectedProv === 'all') ? 'all' : $selectedProv;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$currentProvince = $_SESSION['current_province'] ?? 'TP. Hồ Chí Minh';
$displayProvince = ($currentProvince === 'all' || $currentProvince === '') ? 'Tất cả' : $currentProvince;

$cartCount = getCartCount();
$currentUser = getCurrentUser();

$userFavorites = [];
if ($currentUser) {
    try {
        $stmtFav = $conn->prepare("SELECT restaurant_id FROM favorites WHERE user_id = ?");
        $stmtFav->execute([$currentUser['id']]);
        $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {} // Bỏ qua nếu chưa migrate
}

// Lấy danh mục để hiển thị nav tầng 2
$categories = [];
if (isset($conn)) {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC LIMIT 8");
    $categories = $stmt->fetchAll();
}

// Flash message
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --cica-red: #ee2624; }
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f5f5f5; }

        /* Scrollbar custom */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ee2624; border-radius: 3px; }

        /* Utility classes */
        .bg-cica-red   { background-color: #ee2624; }
        .text-cica-red { color: #ee2624; }
        .border-cica-red { border-color: #ee2624; }
        .ring-cica-red { --tw-ring-color: #ee2624; }
        .hover\:bg-cica-red:hover { background-color: #ee2624; }
        .hover\:text-cica-red:hover { color: #ee2624; }
        .focus\:border-cica-red:focus { border-color: #ee2624; }
        .focus\:ring-cica-red:focus { --tw-ring-color: rgba(238,38,36,0.2); }

        /* Header */
        .header-top { background: #fff; border-bottom: 1px solid #f0f0f0; }
        .header-nav { background: #fff; border-bottom: 2px solid #f0f0f0; }
        
        /* Category nav */
        .cat-item { transition: all 0.2s ease; }
        .cat-item:hover { color: #ee2624; transform: translateY(-2px); }
        .cat-item.active { color: #ee2624; border-bottom: 2px solid #ee2624; }

        /* Cart badge */
        @keyframes bounce-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        .cart-badge-animate { animation: bounce-badge 0.3s ease; }

        /* Dropdown */
        .dropdown-menu { 
            opacity: 0; visibility: hidden; transform: translateY(8px);
            transition: all 0.2s ease; 
        }
        .dropdown:hover .dropdown-menu { 
            opacity: 1; visibility: visible; transform: translateY(0); 
        }

        /* Card hover */
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }

        /* Toast notification */
        #toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
        .toast {
            padding: 12px 20px; border-radius: 12px; color: #fff; font-weight: 600;
            font-size: 14px; display: flex; align-items: center; gap: 10px;
            margin-top: 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(50px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .toast.success { background: #16a34a; }
        .toast.error   { background: #dc2626; }
        .toast.info    { background: #2563eb; }

        /* Loading spinner */
        .spinner {
            width: 20px; height: 20px; border: 2px solid #fff;
            border-top-color: transparent; border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Page transition */
        .page-fade-in { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="page-fade-in">

<!-- Toast Container -->
<div id="toast-container"></div>

<?php if ($flash): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('<?= e($flash['type']) ?>', '<?= e($flash['message']) ?>');
    });
</script>
<?php endif; ?>

<!-- ============================================================ -->
<!-- HEADER TẦNG 1: Logo + Search + Location + User -->
<!-- ============================================================ -->
<header class="header-top sticky top-0 z-50 shadow-sm">
    <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center gap-4">
        
        <!-- Logo -->
        <a href="/foodbooking/" class="flex items-center gap-2.5 flex-shrink-0">
            <div class="w-10 h-10 rounded-xl overflow-hidden shadow border border-red-100">
                <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" 
                     alt="Cicafood Logo" class="w-full h-full object-cover">
            </div>
            <span class="text-2xl font-black text-cica-red tracking-tight hidden sm:block">Cicafood</span>
        </a>

        <!-- Location -->
        <div class="hidden md:block relative group z-[60]">
            <button class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-cica-red transition border border-gray-200 rounded-xl px-3 py-2 flex-shrink-0 hover:border-red-200 bg-gray-50">
                <i class="fas fa-map-marker-alt text-cica-red"></i>
                <span class="max-w-[130px] truncate"><?= e($displayProvince) ?></span>
                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
            </button>
            <div class="absolute left-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all max-h-96 overflow-y-auto">
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-50 mb-1">
                    Khu vực giao hàng
                </div>
                <form method="POST" id="province-form">
                    <input type="hidden" name="change_province" value="1">
                    <!-- Option: Tất cả tỉnh thành -->
                    <button type="submit" name="province_name" value="all"
                            class="w-full text-left px-4 py-2.5 text-sm font-bold <?= ($currentProvince === 'all' || $currentProvince === '') ? 'text-cica-red bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-cica-red' ?> transition flex items-center gap-2">
                        <i class="fas fa-globe text-xs"></i> Tất cả tỉnh thành
                    </button>
                    <div class="border-t border-gray-50 my-1"></div>
                    <?php foreach($provincesList as $prov): ?>
                    <button type="submit" name="province_name" value="<?= e($prov) ?>"
                            class="w-full text-left px-4 py-2.5 text-sm <?= $currentProvince === $prov ? 'text-cica-red font-bold bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-cica-red' ?> transition">
                        <?= e($prov) ?>
                    </button>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="/foodbooking/views/restaurant/list.php" class="flex-1 relative max-w-xl">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="q" 
                   value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>"
                   placeholder="Tìm món ngon, quán quen..." 
                   class="w-full pl-11 pr-4 py-2.5 bg-gray-100 border border-transparent rounded-2xl text-sm outline-none focus:bg-white focus:border-cica-red focus:ring-1 focus:ring-cica-red transition-all">
        </form>

        <!-- Right Actions -->
        <div class="flex items-center gap-3 flex-shrink-0 ml-auto">
            
            <!-- Cart -->
            <a href="/foodbooking/views/order/checkout.php" class="relative p-2.5 hover:bg-red-50 rounded-xl transition group" id="cart-btn">
                <i class="fas fa-shopping-bag text-gray-600 group-hover:text-cica-red text-xl transition"></i>
                <span id="cart-count-badge" 
                      class="absolute -top-1 -right-1 bg-cica-red text-white text-[10px] font-black rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 border-2 border-white <?= $cartCount > 0 ? '' : 'hidden' ?>">
                    <?= $cartCount ?>
                </span>
            </a>

            <!-- User Menu -->
            <?php if ($currentUser): ?>
            <div class="dropdown relative">
                <button class="flex items-center gap-2 bg-gray-100 hover:bg-red-50 px-3 py-2 rounded-xl transition">
                    <?php if (!empty($currentUser['avatar']) && $currentUser['avatar'] !== 'default_avatar.png'): ?>
                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-200">
                            <img src="<?= getImageUrl($currentUser['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                    <?php else: ?>
                        <div class="w-7 h-7 bg-cica-red rounded-full flex items-center justify-center">
                            <span class="text-white text-xs font-bold"><?= strtoupper(mb_substr($currentUser['full_name'], 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="text-sm font-semibold text-gray-700 hidden md:block max-w-[100px] truncate">
                        <?= e(explode(' ', $currentUser['full_name'])[0]) ?>
                    </span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                <div class="dropdown-menu absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-gray-50">
                        <p class="font-bold text-gray-800 text-sm truncate"><?= e($currentUser['full_name']) ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= e($currentUser['email']) ?></p>
                    </div>
                    <a href="/foodbooking/views/user/profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-cica-red transition">
                        <i class="fas fa-user-circle w-4"></i> Hồ sơ của tôi
                    </a>
                    <a href="/foodbooking/views/order/history.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-cica-red transition">
                        <i class="fas fa-receipt w-4"></i> Đơn hàng
                    </a>
                    <a href="/foodbooking/views/user/vouchers.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-cica-red transition">
                        <i class="fas fa-ticket-alt w-4 text-cica-red"></i> Ví Voucher
                    </a>
                    <a href="/foodbooking/views/user/favorites.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-cica-red transition">
                        <i class="fas fa-heart w-4 text-pink-400"></i> Yêu thích
                    </a>
                    <div class="border-t border-gray-100 mt-1 pt-1">
                        <a href="/foodbooking/api/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt w-4"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <a href="/foodbooking/views/auth/login.php" class="text-sm font-bold text-gray-700 hover:text-cica-red transition hidden md:block">Đăng nhập</a>
            <a href="/foodbooking/views/auth/register.php" class="bg-cica-red text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-red-700 transition shadow-sm shadow-red-200 active:scale-95">
                Đăng ký
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ============================================================ -->
<!-- HEADER TẦNG 2: Danh mục ngành hàng -->
<!-- ============================================================ -->
<?php if (!empty($categories)): ?>
<nav class="header-nav bg-white sticky top-[65px] z-40">
    <div class="max-w-screen-xl mx-auto px-4">
        <ul class="flex items-center gap-1 overflow-x-auto scrollbar-hide" style="scrollbar-width:none;">
            <li class="flex-shrink-0">
                <a href="/foodbooking/views/restaurant/list.php" class="cat-item flex flex-col items-center gap-1 px-4 py-3 text-gray-500 hover:text-cica-red <?= !isset($_GET['category']) ? 'text-cica-red border-b-2 border-cica-red' : '' ?>">
                    <i class="fas fa-th text-lg"></i>
                    <span class="text-[11px] font-semibold whitespace-nowrap">Tất cả</span>
                </a>
            </li>
            <?php foreach ($categories as $cat): 
                $isActive = isset($_GET['category']) && $_GET['category'] == $cat['slug'];
            ?>
            <li class="flex-shrink-0">
                <a href="/foodbooking/views/restaurant/list.php?category=<?= e($cat['slug']) ?>" 
                   class="cat-item flex flex-col items-center gap-1 px-4 py-3 <?= $isActive ? 'text-cica-red border-b-2 border-cica-red' : 'text-gray-500' ?>">
                    <i class="fas <?= e($cat['icon']) ?> text-lg" style="color: <?= $isActive ? '#ee2624' : $cat['color'] ?>"></i>
                    <span class="text-[11px] font-semibold whitespace-nowrap"><?= e($cat['name']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
<?php endif; ?>

<!-- ============================================================ -->
<!-- Global JavaScript -->
<!-- ============================================================ -->
<script>
// Toast notification
function showToast(type, message, duration = 3500) {
    const container = document.getElementById('toast-container');
    const icons = { success: 'fa-check-circle', error: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || 'fa-bell'}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'none';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Update cart badge
function updateCartBadge(count) {
    const badge = document.getElementById('cart-count-badge');
    if (!badge) return;
    badge.textContent = count;
    if (count > 0) {
        badge.classList.remove('hidden');
        badge.classList.add('cart-badge-animate');
        setTimeout(() => badge.classList.remove('cart-badge-animate'), 300);
    } else {
        badge.classList.add('hidden');
    }
}

// Add to cart via AJAX
function addToCart(itemId, restaurantId) {
    const btn = event.currentTarget;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<div class="spinner mx-auto"></div>';
    btn.disabled = true;

    fetch('/foodbooking/api/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, restaurant_id: restaurantId, quantity: 1 })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Đã thêm vào giỏ hàng!');
            updateCartBadge(data.cart_count);
            if (typeof updateCartSidebar === 'function') updateCartSidebar(data.cart);
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(() => showToast('error', 'Lỗi kết nối. Vui lòng thử lại!'))
    .finally(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}
</script>
