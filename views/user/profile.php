<?php
// views/user/profile.php - Trang hồ sơ người dùng
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/provinces.php';
requireLogin();

$pageTitle = 'Hồ Sơ Của Tôi - Cicafood';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Order stats
$stmtStats = $conn->prepare("
    SELECT 
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) AS total_spent,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
    FROM orders WHERE user_id = ?
");
$stmtStats->execute([$_SESSION['user_id']]);
$stats = $stmtStats->fetch();

// Recent orders
$stmtRecent = $conn->prepare("
    SELECT o.*, r.name AS restaurant_name, r.image AS restaurant_image
    FROM orders o JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC LIMIT 5
");
$stmtRecent->execute([$_SESSION['user_id']]);
$recentOrders = $stmtRecent->fetchAll();

// Xử lý cập nhật thông tin
$updateErrors = [];
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $province = trim($_POST['province'] ?? 'TP. Hồ Chí Minh');
    $avatar   = trim($_POST['avatar'] ?? '');
    $newPass  = $_POST['new_password'] ?? '';
    $confPass = $_POST['confirm_password'] ?? '';
    $curPass  = $_POST['current_password'] ?? '';

    if (empty($fullName)) $updateErrors[] = 'Tên không được để trống';
    if (strlen($phone) < 9) $updateErrors[] = 'Số điện thoại không hợp lệ';

    // Password change
    if (!empty($newPass)) {
        if (!password_verify($curPass, $user['password_hash'])) {
            $updateErrors[] = 'Mật khẩu hiện tại không đúng';
        } elseif (strlen($newPass) < 6) {
            $updateErrors[] = 'Mật khẩu mới tối thiểu 6 ký tự';
        } elseif ($newPass !== $confPass) {
            $updateErrors[] = 'Mật khẩu xác nhận không khớp';
        }
    }

    if (empty($updateErrors)) {
        if (!empty($newPass)) {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmtUp = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=?, province=?, avatar=?, password_hash=? WHERE id=?");
            $stmtUp->execute([$fullName, $phone, $address, $province, $avatar, $newHash, $_SESSION['user_id']]);
        } else {
            $stmtUp = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=?, province=?, avatar=? WHERE id=?");
            $stmtUp->execute([$fullName, $phone, $address, $province, $avatar, $_SESSION['user_id']]);
        }
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_avatar'] = $avatar;
        $updateSuccess = true;
        // Reload user data
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}

// Xử lý Merchant Registration
$merchantErrors = [];
$merchantSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_merchant'])) {
    $rName = trim($_POST['r_name'] ?? '');
    $rAddress = trim($_POST['r_address'] ?? '');
    $rProvince = trim($_POST['r_province'] ?? 'TP. Hồ Chí Minh');
    $rPhone = trim($_POST['r_phone'] ?? '');
    $rType = trim($_POST['r_type'] ?? '');
    $rImage = trim($_POST['r_image'] ?? '');
    
    if (empty($rName)) $merchantErrors[] = 'Tên quán không được để trống';
    if (empty($rAddress)) $merchantErrors[] = 'Địa chỉ không được để trống';
    
    if (empty($merchantErrors)) {
        // Cập nhật role
        $stmtUp = $conn->prepare("UPDATE users SET role = 'merchant' WHERE id = ?");
        $stmtUp->execute([$_SESSION['user_id']]);
        
        // Log hồ sơ
        $stmtApp = $conn->prepare("INSERT INTO merchant_applications (user_id, restaurant_name, restaurant_address, province, business_type, phone, logo_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')");
        $stmtApp->execute([$_SESSION['user_id'], $rName, $rAddress, $rProvince, $rType, $rPhone, $rImage]);
        
        // Tạo quán auto-approved
        $slug = makeSlug($rName) . '-' . time();
        $stmtRest = $conn->prepare("INSERT INTO restaurants (owner_id, name, slug, address, province, phone, image, is_open) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmtRest->execute([$_SESSION['user_id'], $rName, $slug, $rAddress, $rProvince, $rPhone, $rImage]);
        
        // Update session
        $_SESSION['user_role'] = 'merchant';
        
        header("Location: /foodbooking/views/user/profile.php?merchant_success=1");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-lg mx-auto px-4 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT: Sidebar profile -->
        <div class="lg:col-span-1 space-y-5">
            
            <!-- Avatar + Basic Info -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 text-center">
                <div class="relative inline-block mb-4 group cursor-pointer" id="avatarUploadArea" onclick="document.getElementById('avatarInput').click()">
                    <div id="avatarPreview" class="w-24 h-24 bg-gradient-to-br from-cica-red to-red-400 rounded-full flex items-center justify-center mx-auto shadow-lg overflow-hidden border-4 border-white transition relative z-10">
                        <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default_avatar.png'): ?>
                            <img src="<?= getImageUrl($user['avatar']) ?>" class="w-full h-full object-cover" id="avatarImg">
                        <?php else: ?>
                            <span class="text-white font-black text-4xl" id="avatarInitials">
                                <?= strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 rounded-full backdrop-blur-[2px]">
                        <i class="fas fa-camera text-white text-xl"></i>
                        <span class="text-[9px] font-bold text-white uppercase tracking-wider mt-1">Đổi Ảnh</span>
                    </div>

                    <input type="file" id="avatarInput" class="hidden" accept="image/*">
                </div>
                <!-- Loading overlay -->
                <div id="avatarLoading" class="hidden text-xs text-blue-500 font-bold mb-3 animate-pulse">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Đang tải...
                </div>
                
                <h2 class="font-black text-gray-900 text-xl"><?= e($user['full_name']) ?></h2>
                <p class="text-gray-500 text-sm"><?= e($user['email']) ?></p>
                <?php if ($user['role'] === 'admin'): ?>
                <span class="inline-block bg-cica-red/10 text-cica-red text-xs font-bold px-3 py-1 rounded-full mt-2">
                    <i class="fas fa-shield-halved mr-1"></i>Quản trị viên
                </span>
                <?php endif; ?>
                <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
                    Thành viên từ <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4">Thống Kê</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-blue-500 text-xs"></i>
                            </div>
                            Tổng đơn hàng
                        </div>
                        <span class="font-black text-gray-900"><?= $stats['total_orders'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-circle-check text-green-500 text-xs"></i>
                            </div>
                            Đơn hoàn thành
                        </div>
                        <span class="font-black text-green-600"><?= $stats['completed_orders'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-fire-flame-curved text-cica-red text-xs"></i>
                            </div>
                            Tổng chi tiêu
                        </div>
                        <span class="font-black text-cica-red"><?= formatPrice($stats['total_spent'] ?? 0) ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <a href="/foodbooking/views/order/history.php" class="flex items-center gap-3 p-4 hover:bg-red-50 hover:text-cica-red transition border-b border-gray-50">
                    <i class="fas fa-receipt w-5 text-gray-400"></i>
                    <span class="text-sm font-semibold text-gray-700">Đơn hàng của tôi</span>
                    <i class="fas fa-chevron-right ml-auto text-gray-300 text-xs"></i>
                </a>
                <a href="/foodbooking/views/user/favorites.php" class="flex items-center gap-3 p-4 hover:bg-red-50 hover:text-cica-red transition border-b border-gray-50">
                    <i class="fas fa-heart w-5 text-gray-400"></i>
                    <span class="text-sm font-semibold text-gray-700">Quán yêu thích</span>
                    <i class="fas fa-chevron-right ml-auto text-gray-300 text-xs"></i>
                </a>
                <a href="/foodbooking/views/user/vouchers.php" class="flex items-center gap-3 p-4 hover:bg-red-50 hover:text-cica-red transition border-b border-gray-50">
                    <i class="fas fa-ticket w-5 text-gray-400"></i>
                    <span class="text-sm font-semibold text-gray-700">Voucher của tôi</span>
                    <i class="fas fa-chevron-right ml-auto text-gray-300 text-xs"></i>
                </a>
                <?php if ($user['role'] === 'merchant' || $user['role'] === 'admin'): ?>
                <a href="/foodbooking/merchant/index.php" class="flex items-center gap-3 p-4 hover:bg-red-50 text-cica-red transition border-b border-gray-50 bg-red-50/50">
                    <i class="fas fa-store w-5"></i>
                    <span class="text-sm font-bold">Quản lý cửa hàng</span>
                    <i class="fas fa-chevron-right ml-auto text-gray-300 text-xs"></i>
                </a>
                <?php endif; ?>
                <a href="/foodbooking/api/auth/logout.php" class="flex items-center gap-3 p-4 hover:bg-red-50 text-red-600 transition">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="text-sm font-semibold">Đăng xuất</span>
                </a>
            </div>
        </div>

        <!-- RIGHT: Edit Profile + Recent Orders -->
        <div class="lg:col-span-2 space-y-5">
            
            <!-- Edit form -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-gray-900 text-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-user-pen text-cica-red"></i> Chỉnh Sửa Hồ Sơ
                </h2>

                <?php if ($updateSuccess): ?>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <p class="text-green-700 font-medium text-sm">Cập nhật hồ sơ thành công!</p>
                </div>
                <?php endif; ?>

                <?php if (!empty($updateErrors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
                    <ul class="text-red-700 text-sm space-y-1">
                        <?php foreach ($updateErrors as $e_msg): ?>
                        <li class="flex items-center gap-2"><i class="fas fa-times-circle text-red-400"></i><?= e($e_msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4" id="profileForm">
                    <input type="hidden" name="avatar" id="formAvatarInput" value="<?= e($user['avatar']) ?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Họ và Tên</label>
                            <input type="text" name="full_name" value="<?= e($user['full_name']) ?>"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Số Điện Thoại</label>
                            <input type="tel" name="phone" value="<?= e($user['phone']) ?>"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Email (không thể thay đổi)</label>
                        <input type="email" value="<?= e($user['email']) ?>" disabled
                               class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Địa Chỉ Mặc Định</label>
                        <div class="flex gap-2">
                            <select name="province" class="w-1/3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                                <?php foreach(getProvinces() as $p): ?>
                                <option value="<?= e($p) ?>" <?= ($user['province'] ?? 'TP. Hồ Chí Minh') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="address" value="<?= e($user['address'] ?? '') ?>" placeholder="Số nhà, tên đường..." class="w-2/3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-700 text-sm mb-3 flex items-center gap-2">
                            <i class="fas fa-lock text-gray-400"></i> Đổi Mật Khẩu (bỏ trống nếu không muốn đổi)
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <input type="password" name="current_password" placeholder="Mật khẩu hiện tại"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                            <div class="grid grid-cols-2 gap-3">
                                <input type="password" name="new_password" placeholder="Mật khẩu mới"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_profile"
                            class="w-full bg-cica-red text-white py-4 rounded-2xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-100 active:scale-[0.98]">
                        <i class="fas fa-save mr-2"></i>Lưu Thay Đổi
                    </button>
                </form>
            </div>

            <!-- Recent Orders -->
            <?php if (!empty($recentOrders)): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-black text-gray-900 text-lg flex items-center gap-2">
                        <i class="fas fa-history text-cica-red"></i> Đơn Gần Đây
                    </h2>
                    <a href="/foodbooking/views/order/history.php" class="text-sm text-cica-red font-bold hover:underline">Xem tất cả</a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recentOrders as $order): 
                        $si = getOrderStatusLabel($order['status']);
                    ?>
                    <a href="/foodbooking/views/order/success.php?id=<?= $order['id'] ?>" 
                       class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-2xl transition group">
                        <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0">
                            <img src="<?= getImageUrl($order['restaurant_image']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm truncate group-hover:text-cica-red transition"><?= e($order['restaurant_name']) ?></p>
                            <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-black text-cica-red text-sm"><?= formatPrice($order['total_amount']) ?></p>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-600' : ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600') ?>">
                                <?= $si['label'] ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Merchant Registration Form -->
            <?php if ($user['role'] === 'customer'): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mt-5">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-black text-gray-900 text-lg flex items-center gap-2">
                        <i class="fas fa-store text-cica-red"></i> Đăng ký làm Đối tác
                    </h2>
                </div>
                
                <?php if (isset($_GET['merchant_success'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    <div>
                        <p class="text-green-700 font-bold text-sm">Đăng ký thành công!</p>
                        <p class="text-green-600 text-xs mt-0.5">Bạn đã trở thành Đối tác. <a href="/foodbooking/merchant/index.php" class="underline font-bold">Quản lý cửa hàng ngay</a>.</p>
                    </div>
                </div>
                <?php else: ?>
                    <?php if (!empty($merchantErrors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
                        <ul class="text-red-700 text-sm space-y-1">
                            <?php foreach ($merchantErrors as $e_msg): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-times-circle text-red-400"></i><?= e($e_msg) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-4">
                        <div class="form-group">
                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase">Tên Quán Ăn <span class="text-red-500">*</span></label>
                            <input type="text" name="r_name" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition" required>
                        </div>
                        <div class="form-group">
                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase">Địa chỉ quán <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <select name="r_province" class="w-1/3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition">
                                    <?php foreach(getProvinces() as $p): ?>
                                    <option value="<?= e($p) ?>" <?= ($user['province'] ?? 'TP. Hồ Chí Minh') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="r_address" class="w-2/3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition" placeholder="Số nhà, tên đường..." required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase">Số điện thoại</label>
                                <input type="tel" name="r_phone" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition" value="<?= e($user['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase">Loại hình</label>
                                <input type="text" name="r_type" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 transition" placeholder="VD: Bún, Cơm, Cafe...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase">Hình Ảnh Đại Diện</label>
                            <div class="relative w-full border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-cica-red transition group cursor-pointer" onclick="document.getElementById('rImageInput').click()" id="rImageUploadArea">
                                <div id="rImagePreview" class="hidden w-full h-32 rounded-xl mb-3 overflow-hidden bg-gray-50"></div>
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 group-hover:text-cica-red mb-2 transition"></i>
                                <p class="text-xs text-gray-500 font-semibold mb-1">Click hoặc kéo thả ảnh vào đây</p>
                                <p class="text-[10px] text-gray-400">JPG, PNG, max 5MB</p>
                                <input type="file" id="rImageInput" class="hidden" accept="image/*">
                            </div>
                            <input type="hidden" name="r_image" id="rImageVal" value="">
                            <div id="rImageLoading" class="hidden text-xs text-blue-500 font-bold mt-2 animate-pulse">
                                <i class="fas fa-spinner fa-spin mr-1"></i>Đang tải...
                            </div>
                        </div>
                        <button type="submit" name="register_merchant" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-bold hover:bg-black transition shadow-lg active:scale-[0.98]">
                            <i class="fas fa-paper-plane mr-2"></i> Gửi Đơn Đăng Ký Đối Tác
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const uploadArea = document.getElementById('avatarUploadArea');
    const avatarInput = document.getElementById('avatarInput');
    const loading = document.getElementById('avatarLoading');
    const formInput = document.getElementById('formAvatarInput');
    
    // Prevent defaults for D&D
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
        uploadArea.addEventListener(evt, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    uploadArea.addEventListener('dragover', () => {
        uploadArea.querySelector('#avatarPreview').classList.add('border-cica-red');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.querySelector('#avatarPreview').classList.remove('border-cica-red');
    });

    uploadArea.addEventListener('drop', (e) => {
        uploadArea.querySelector('#avatarPreview').classList.remove('border-cica-red');
        const files = e.dataTransfer.files;
        if(files.length) handleFileUpload(files[0]);
    });

    avatarInput.addEventListener('change', function() {
        if(this.files.length) handleFileUpload(this.files[0]);
    });

    function handleFileUpload(file) {
        if (!file.type.match('image.*')) {
            showToast('error', 'Chỉ hỗ trợ file ảnh');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);
        
        loading.classList.remove('hidden');

        fetch('/foodbooking/api/upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if(data.success) {
                formInput.value = data.path;
                const previewDiv = document.getElementById('avatarPreview');
                previewDiv.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover" id="avatarImg">`;
                showToast('success', 'Tải ảnh thành công, vui lòng "Lưu Thay Đổi"');
            } else {
                showToast('error', data.message);
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            showToast('error', 'Lỗi kết nối máy chủ');
            console.error(err);
        });
    }

    // Merchant registration image
    const rmArea = document.getElementById('rImageUploadArea');
    if (rmArea) {
        const rmInput = document.getElementById('rImageInput');
        const rmLoading = document.getElementById('rImageLoading');
        const rmVal = document.getElementById('rImageVal');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
            rmArea.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); });
        });
        rmArea.addEventListener('dragover', () => rmArea.classList.add('border-cica-red', 'bg-red-50/50'));
        rmArea.addEventListener('dragleave', () => rmArea.classList.remove('border-cica-red', 'bg-red-50/50'));
        rmArea.addEventListener('drop', (e) => {
            rmArea.classList.remove('border-cica-red', 'bg-red-50/50');
            if(e.dataTransfer.files.length) uploadMerchantImage(e.dataTransfer.files[0]);
        });
        rmInput.addEventListener('change', function() {
            if(this.files.length) uploadMerchantImage(this.files[0]);
        });

        function uploadMerchantImage(file) {
            if (!file.type.match('image.*')) { showToast('error', 'Chỉ hỗ trợ file ảnh'); return; }
            const formData = new FormData();
            formData.append('image', file);
            rmLoading.classList.remove('hidden');

            fetch('/foodbooking/api/upload_image.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                rmLoading.classList.add('hidden');
                if(data.success) {
                    rmVal.value = data.path;
                    const preview = document.getElementById('rImagePreview');
                    preview.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">`;
                    preview.classList.remove('hidden');
                    rmArea.querySelector('i').classList.add('hidden');
                    showToast('success', 'Tải ảnh quán thành công');
                } else showToast('error', data.message);
            }).catch(() => { rmLoading.classList.add('hidden'); showToast('error', 'Lỗi kết nối'); });
        }
    }
});
</script>