<?php
// merchant/layout.php
// Gọi sau khi đã require db_config.php và auth_check.php
// Biến cần có: $pageTitle, $activePage, $restaurant

if (!isset($pageTitle)) $pageTitle = 'Merchant - Cicafood';
if (!isset($activePage)) $activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Cicafood Merchant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --cica-red: #ee2624; --sidebar-w: 240px; }
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { background: #f4f6f9; min-height: 100vh; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: #1a1a2e;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }
        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .nav-section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.3);
            padding: 14px 20px 6px;
            text-transform: uppercase;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 20px;
            margin: 2px 10px;
            border-radius: 10px;
            color: rgba(255,255,255,0.55);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }
        .nav-item.active {
            background: linear-gradient(135deg, #ee2624, #c41f1d);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(238,38,36,0.35);
        }
        .nav-item .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }
        .sidebar-bottom {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }
        .content-area { padding: 28px; flex: 1; }

        /* ===== UTILITIES ===== */
        .bg-cica-red { background-color: #ee2624; }
        .text-cica-red { color: #ee2624; }
        .border-cica-red { border-color: #ee2624; }

        /* Cards */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            border: 1px solid #edf0f5;
            transition: all 0.25s ease;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }

        .merchant-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #edf0f5;
            overflow: hidden;
        }
        .merchant-card-header {
            padding: 16px 22px;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .merchant-card-body { padding: 22px; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-pending    { background: #fef3c7; color: #d97706; }
        .badge-confirmed  { background: #dbeafe; color: #2563eb; }
        .badge-preparing  { background: #ffedd5; color: #ea580c; }
        .badge-delivering { background: #ede9fe; color: #7c3aed; }
        .badge-completed  { background: #dcfce7; color: #16a34a; }
        .badge-cancelled  { background: #fee2e2; color: #dc2626; }
        .badge-available  { background: #dcfce7; color: #16a34a; }
        .badge-unavailable{ background: #fee2e2; color: #dc2626; }

        /* Table */
        .merchant-table { width: 100%; border-collapse: collapse; }
        .merchant-table th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            background: #fafafa;
            border-bottom: 1px solid #f0f2f5;
        }
        .merchant-table td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: #374151;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }
        .merchant-table tr:last-child td { border-bottom: none; }
        .merchant-table tr:hover td { background: #fafbfc; }

        /* Buttons */
        .btn-primary {
            background: #ee2624;
            color: #fff;
            padding: 9px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-primary:hover { background: #ca1f1d; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(238,38,36,0.3); }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-secondary:hover { background: #e9eaec; }
        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            padding: 7px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-danger:hover { background: #fecaca; }
        .btn-edit {
            background: #eff6ff;
            color: #2563eb;
            padding: 7px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-edit:hover { background: #dbeafe; }

        /* Form inputs */
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.2s;
            background: #fafafa;
        }
        .form-input:focus { border-color: #ee2624; background: #fff; box-shadow: 0 0 0 3px rgba(238,38,36,0.08); }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .form-group { margin-bottom: 16px; }

        /* Toast */
        #toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
        .toast {
            padding: 13px 20px; border-radius: 12px; color: #fff; font-weight: 600;
            font-size: 14px; display: flex; align-items: center; gap: 10px;
            margin-top: 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            animation: slideInToast 0.3s ease;
        }
        @keyframes slideInToast {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .toast.success { background: #16a34a; }
        .toast.error   { background: #dc2626; }
        .toast.info    { background: #2563eb; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 200;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            width: 94%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.95);
            transition: transform 0.2s ease;
        }
        .modal-overlay.open .modal-box { transform: scale(1); }

        /* Spinner */
        .spinner { width: 18px; height: 18px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Online dot */
        .online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; }

        /* Sidebar toggle for mobile */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }

        /* Page fade */
        .page-fade { animation: pageFade 0.35s ease; }
        @keyframes pageFade { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<!-- Toast Container -->
<div id="toast-container"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="/foodbooking/" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl overflow-hidden border border-red-900/30">
                <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg"
                     alt="Cicafood" class="w-full h-full object-cover">
            </div>
            <div>
                <p class="text-white font-black text-base leading-tight">Cicafood</p>
                <p class="text-red-400 text-[10px] font-semibold tracking-wide">MERCHANT CENTER</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <p class="nav-section-title">Tổng quan</p>
        <a href="/foodbooking/merchant/index.php" class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie nav-icon"></i>
            <span>Dashboard</span>
        </a>

        <p class="nav-section-title">Quản lý</p>
        <a href="/foodbooking/merchant/orders.php" class="nav-item <?= $activePage === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-receipt nav-icon"></i>
            <span>Quản lý Đơn hàng</span>
            <?php
            // Count pending orders
            if (isset($restaurant) && $restaurant) {
                $stmtPending = $conn->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ? AND status = 'pending'");
                $stmtPending->execute([$restaurant['id']]);
                $pendingCount = $stmtPending->fetchColumn();
                if ($pendingCount > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= $pendingCount ?></span>
            <?php endif;
            } ?>
        </a>
        <a href="/foodbooking/merchant/menu.php" class="nav-item <?= $activePage === 'menu' ? 'active' : '' ?>">
            <i class="fas fa-utensils nav-icon"></i>
            <span>Quản lý Thực đơn</span>
        </a>
        <a href="/foodbooking/merchant/menu_categories.php" class="nav-item <?= $activePage === 'menu_categories' ? 'active' : '' ?>">
            <i class="fas fa-layer-group nav-icon"></i>
            <span>Danh mục Thực đơn</span>
        </a>
        <a href="/foodbooking/merchant/vouchers.php" class="nav-item <?= $activePage === 'vouchers' ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt nav-icon"></i>
            <span>Quản lý Voucher</span>
        </a>

        <p class="nav-section-title">Cài đặt</p>
        <a href="/foodbooking/merchant/settings.php" class="nav-item <?= $activePage === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-store nav-icon"></i>
            <span>Thông tin Quán</span>
        </a>
    </nav>

    <!-- Bottom: User info -->
    <div class="sidebar-bottom">
        <?php
        $stmtU = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $stmtU->execute([$_SESSION['user_id']]);
        $sidebarUser = $stmtU->fetch();
        ?>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-gradient-to-br from-red-500 to-red-700 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm font-black"><?= strtoupper(mb_substr($sidebarUser['full_name'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-bold truncate"><?= htmlspecialchars($sidebarUser['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-gray-400 text-[10px] truncate"><?= htmlspecialchars($sidebarUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <a href="/foodbooking/api/auth/logout.php"
           class="flex items-center gap-2.5 p-2.5 rounded-xl text-gray-400 hover:text-red-400 hover:bg-white/5 transition text-xs font-semibold">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
        <a href="/foodbooking/"
           class="flex items-center gap-2.5 p-2.5 rounded-xl text-gray-400 hover:text-blue-400 hover:bg-white/5 transition text-xs font-semibold">
            <i class="fas fa-arrow-left"></i> Về trang khách hàng
        </a>
    </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <!-- Mobile hamburger -->
        <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition">
            <i class="fas fa-bars text-gray-600"></i>
        </button>

        <div class="flex items-center gap-3">
            <div>
                <h1 class="font-black text-gray-800 text-lg leading-tight"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (isset($restaurant) && $restaurant): ?>
                <p class="text-gray-400 text-xs flex items-center gap-1.5 mt-0.5">
                    <span class="online-dot"></span>
                    <?= htmlspecialchars($restaurant['name'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php if (isset($restaurant) && $restaurant): ?>
            <!-- Open/Close toggle -->
            <form method="POST" action="/foodbooking/merchant/api_toggle_open.php" class="flex items-center gap-2">
                <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                <label class="flex items-center gap-2 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_open" value="1" class="sr-only peer"
                               <?= $restaurant['is_open'] ? 'checked' : '' ?>
                               onchange="this.form.submit()">
                        <div class="w-10 h-5 bg-gray-300 rounded-full peer-checked:bg-green-500 transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                    <span class="text-xs font-bold <?= $restaurant['is_open'] ? 'text-green-600' : 'text-gray-400' ?>">
                        <?= $restaurant['is_open'] ? 'Đang mở' : 'Đã đóng' ?>
                    </span>
                </label>
            </form>
            <?php endif; ?>

            <!-- Notification bell -->
            <button class="relative p-2.5 hover:bg-gray-100 rounded-xl transition" title="Thông báo">
                <i class="fas fa-bell text-gray-500"></i>
            </button>

            <!-- Back to store -->
            <?php if (isset($restaurant) && $restaurant): ?>
            <a href="/foodbooking/views/restaurant/detail.php?slug=<?= urlencode($restaurant['slug']) ?>"
               target="_blank"
               class="hidden sm:flex items-center gap-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-2.5 rounded-xl transition">
                <i class="fas fa-external-link-alt text-gray-400"></i> Xem trang quán
            </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Page content injected here -->
    <div class="content-area page-fade">
