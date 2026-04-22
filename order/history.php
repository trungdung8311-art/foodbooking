<?php
// views/order/history.php - Lịch sử đơn hàng + Order Tracking
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pageTitle = 'Đơn Hàng Của Tôi - Cicafood';

$filterStatus = $_GET['status'] ?? '';

$params = [$_SESSION['user_id']];
$where  = 'o.user_id = ?';
if ($filterStatus) {
    $where   .= ' AND o.status = ?';
    $params[] = $filterStatus;
}

$stmt = $conn->prepare("
    SELECT o.*, r.name AS restaurant_name, r.image AS restaurant_image
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE {$where}
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-lg mx-auto px-4 py-6">
    
    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
            <i class="fas fa-receipt text-cica-red"></i> Đơn Hàng Của Tôi
        </h1>
        <p class="text-gray-500 text-sm mt-1">Theo dõi và quản lý tất cả đơn hàng của bạn</p>
    </div>

    <!-- Status Filter Tabs -->
    <?php 
    $tabs = [
        ''           => ['label' => 'Tất cả', 'icon' => 'fa-list'],
        'pending'    => ['label' => 'Chờ xác nhận', 'icon' => 'fa-clock'],
        'confirmed'  => ['label' => 'Đã xác nhận', 'icon' => 'fa-check-circle'],
        'preparing'  => ['label' => 'Đang chuẩn bị', 'icon' => 'fa-fire-flame-curved'],
        'delivering' => ['label' => 'Đang giao', 'icon' => 'fa-motorcycle'],
        'completed'  => ['label' => 'Hoàn thành', 'icon' => 'fa-circle-check'],
        'cancelled'  => ['label' => 'Đã huỷ', 'icon' => 'fa-circle-xmark'],
    ];
    ?>
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6" style="scrollbar-width:none">
        <?php foreach ($tabs as $val => $tab): ?>
        <a href="?status=<?= $val ?>"
           class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 rounded-full text-sm font-semibold transition whitespace-nowrap
           <?= $filterStatus === $val ? 'bg-cica-red text-white shadow-md shadow-red-100' : 'bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-cica-red' ?>">
            <i class="fas <?= $tab['icon'] ?> text-xs"></i>
            <?= $tab['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Orders List -->
    <?php if (empty($orders)): ?>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-16 text-center">
        <i class="fas fa-receipt text-7xl text-gray-200 mb-6"></i>
        <h2 class="text-xl font-black text-gray-400 mb-2">Chưa có đơn hàng nào</h2>
        <p class="text-gray-400 mb-8 text-sm">Hãy đặt đơn hàng đầu tiên và nhận ưu đãi chào mừng!</p>
        <a href="/foodbooking/" class="inline-block bg-cica-red text-white px-8 py-4 rounded-2xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-100 active:scale-95">
            <i class="fas fa-utensils mr-2"></i>Khám phá nhà hàng
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($orders as $order): 
            $statusInfo = getOrderStatusLabel($order['status']);
            $statusColors = [
                'pending'    => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                'confirmed'  => 'bg-blue-100 text-blue-700 border-blue-200',
                'preparing'  => 'bg-orange-100 text-orange-700 border-orange-200',
                'delivering' => 'bg-purple-100 text-purple-700 border-purple-200',
                'completed'  => 'bg-green-100 text-green-700 border-green-200',
                'cancelled'  => 'bg-red-100 text-red-700 border-red-200',
            ];
            $colorClass = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
            <!-- Order Header -->
            <div class="p-5 flex items-center justify-between border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0">
                        <img src="<?= getImageUrl($order['restaurant_image']) ?>" alt="" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800"><?= e($order['restaurant_name']) ?></h3>
                        <p class="text-xs text-gray-400">
                            #<?= e($order['order_code']) ?> · 
                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-full text-xs font-bold border <?= $colorClass ?>">
                    <i class="fas <?= $statusInfo['icon'] ?> mr-1"></i><?= $statusInfo['label'] ?>
                </span>
            </div>

            <!-- Order Progress Bar (for active orders) -->
            <?php if (!in_array($order['status'], ['completed', 'cancelled'])): 
                $steps = ['pending', 'confirmed', 'preparing', 'delivering', 'completed'];
                $currentStep = array_search($order['status'], $steps);
                $progress = ($currentStep / (count($steps) - 1)) * 100;
            ?>
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <div class="flex items-center justify-between text-[10px] text-gray-500 mb-2">
                    <span>Đặt hàng</span>
                    <span>Xác nhận</span>
                    <span>Chuẩn bị</span>
                    <span>Đang giao</span>
                    <span>Hoàn thành</span>
                </div>
                <div class="bg-gray-200 rounded-full h-2 relative">
                    <div class="bg-gradient-to-r from-red-400 to-cica-red h-2 rounded-full transition-all duration-1000" style="width:<?= $progress ?>%"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order Items & Total -->
            <div class="p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">
                        <?php
                        $stmtOI = $conn->prepare("SELECT item_name, quantity FROM order_items WHERE order_id = ? LIMIT 3");
                        $stmtOI->execute([$order['id']]);
                        $ois = $stmtOI->fetchAll();
                        $itemNames = array_map(fn($i) => "{$i['quantity']}x " . mb_strimwidth($i['item_name'], 0, 20, '...'), $ois);
                        echo e(implode(', ', $itemNames));
                        ?>
                        <?php
                        $stmtCount = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                        $stmtCount->execute([$order['id']]);
                        $totalItems = $stmtCount->fetchColumn();
                        if ($totalItems > 3): ?>
                        <span class="text-gray-400">+<?= $totalItems - 3 ?> món khác</span>
                        <?php endif; ?>
                    </p>
                    <p class="font-black text-cica-red text-xl"><?= formatPrice($order['total_amount']) ?></p>
                    <?php if ($order['payment_method'] === 'cod'): ?>
                    <p class="text-xs text-gray-400"><i class="fas fa-money-bill-wave mr-1 text-green-500"></i>Thanh toán COD</p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="/foodbooking/views/order/success.php?id=<?= $order['id'] ?>"
                       class="bg-cica-red text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-red-700 transition text-center">
                        Xem chi tiết
                    </a>
                    <?php if ($order['status'] === 'completed'): ?>
                    <a href="/foodbooking/views/restaurant/detail.php?id=<?= $order['restaurant_id'] ?>"
                       class="border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition text-center">
                        <i class="fas fa-redo mr-1"></i>Đặt lại
                    </a>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'pending'): ?>
                    <button onclick="cancelOrder(<?= $order['id'] ?>)"
                            class="border border-red-200 text-red-600 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-50 transition text-center">
                        <i class="fas fa-times mr-1"></i>Huỷ đơn
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
function cancelOrder(orderId) {
    if (!confirm('Bạn có chắc muốn huỷ đơn hàng này?')) return;
    
    fetch('/foodbooking/api/order/cancel_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Đã huỷ đơn hàng thành công');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', data.message || 'Không thể huỷ đơn hàng');
        }
    });
}

// Auto refresh status for active orders
setTimeout(() => {
    const hasActive = document.querySelector('[class*="progress"]');
    if (hasActive) location.reload();
}, 30000); // Refresh mỗi 30 giây
</script>
