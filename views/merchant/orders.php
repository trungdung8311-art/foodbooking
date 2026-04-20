<?php
// merchant/orders.php - Quản lý đơn hàng
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../merchant/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) { header('Location: /foodbooking/views/user/profile.php'); exit; }

$rid = $restaurant['id'];
$pageTitle = 'Quản lý Đơn hàng';
$activePage = 'orders';

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fp(int $n): string { return number_format($n, 0, ',', '.') . 'đ'; }

// ---- Xử lý cập nhật trạng thái ----
$flashToast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action  = $_POST['action'];

    // Verify order belongs to this restaurant
    $stmtCheck = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND restaurant_id = ?");
    $stmtCheck->execute([$orderId, $rid]);
    $orderRow = $stmtCheck->fetch();

    if ($orderRow) {
        $transitions = [
            'confirm'  => ['pending',    'confirmed'],
            'prepare'  => ['confirmed',  'preparing'],
            'deliver'  => ['preparing',  'delivering'],
            'complete' => ['delivering', 'completed'],
            'cancel'   => ['pending',    'cancelled'],
        ];
        if (isset($transitions[$action])) {
            [$from, $to] = $transitions[$action];
            if ($orderRow['status'] === $from) {
                $stmtUp = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmtUp->execute([$to, $orderId]);
                $flashToast = ['type' => 'success', 'message' => 'Cập nhật trạng thái thành công!'];
            }
        }
    }
    // Refresh
    header('Location: /foodbooking/views/merchant/orders.php?status=' . urlencode($_GET['status'] ?? ''));
    exit;
}

// ---- Lọc theo status ----
$filterStatus = $_GET['status'] ?? '';
$validStatuses = ['', 'pending', 'confirmed', 'preparing', 'delivering', 'completed', 'cancelled'];
if (!in_array($filterStatus, $validStatuses)) $filterStatus = '';

$sql = "
    SELECT o.*, u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.restaurant_id = :rid
";
$params = [':rid' => $rid];
if ($filterStatus !== '') {
    $sql .= " AND o.status = :status";
    $params[':status'] = $filterStatus;
}
$sql .= " ORDER BY o.created_at DESC LIMIT 100";

$stmtOrders = $conn->prepare($sql);
$stmtOrders->execute($params);
$orders = $stmtOrders->fetchAll();

// Count per status for tabs
$stmtCounts = $conn->prepare("
    SELECT status, COUNT(*) AS cnt FROM orders WHERE restaurant_id = ? GROUP BY status
");
$stmtCounts->execute([$rid]);
$countMap = [];
foreach ($stmtCounts->fetchAll() as $r) $countMap[$r['status']] = $r['cnt'];

$statusMeta = [
    ''           => ['label' => 'Tất cả',        'icon' => 'fa-list'],
    'pending'    => ['label' => 'Chờ xác nhận',   'icon' => 'fa-clock'],
    'confirmed'  => ['label' => 'Đã xác nhận',    'icon' => 'fa-check-circle'],
    'preparing'  => ['label' => 'Đang chuẩn bị',  'icon' => 'fa-fire-flame-curved'],
    'delivering' => ['label' => 'Đang giao',       'icon' => 'fa-motorcycle'],
    'completed'  => ['label' => 'Hoàn thành',      'icon' => 'fa-circle-check'],
    'cancelled'  => ['label' => 'Đã huỷ',          'icon' => 'fa-circle-xmark'],
];

$actionButtons = [
    'pending'    => ['action' => 'confirm',  'label' => 'Xác nhận nhận đơn', 'icon' => 'fa-check', 'class' => 'btn-primary'],
    'confirmed'  => ['action' => 'prepare',  'label' => 'Bắt đầu chuẩn bị',  'icon' => 'fa-fire',  'class' => 'btn-secondary'],
    'preparing'  => ['action' => 'deliver',  'label' => 'Giao cho tài xế',    'icon' => 'fa-motorcycle', 'class' => 'btn-secondary'],
    'delivering' => ['action' => 'complete', 'label' => 'Hoàn thành',         'icon' => 'fa-check-double', 'class' => 'btn-secondary'],
];

require_once 'layout.php';
?>

<!-- Status Tabs -->
<div class="flex gap-2 flex-wrap mb-6">
<?php foreach ($statusMeta as $s => $meta):
    $cnt = $s === '' ? array_sum($countMap) : ($countMap[$s] ?? 0);
    $active = $filterStatus === $s;
?>
    <a href="?status=<?= urlencode($s) ?>"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all
              <?= $active
                  ? 'bg-cica-red text-white shadow-md shadow-red-200'
                  : 'bg-white text-gray-600 hover:bg-red-50 hover:text-cica-red border border-gray-200' ?>">
        <i class="fas <?= $meta['icon'] ?>"></i>
        <?= $meta['label'] ?>
        <?php if ($cnt > 0): ?>
        <span class="<?= $active ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' ?> rounded-full px-2 py-0.5 text-[10px]">
            <?= $cnt ?>
        </span>
        <?php endif; ?>
    </a>
<?php endforeach; ?>
</div>

<!-- Orders List -->
<?php if (empty($orders)): ?>
<div class="merchant-card">
    <div class="merchant-card-body text-center py-16">
        <i class="fas fa-receipt text-gray-200 text-6xl mb-4"></i>
        <h3 class="font-bold text-gray-400 text-lg">Không có đơn hàng</h3>
        <p class="text-gray-300 text-sm mt-1">Chưa có đơn hàng nào trong mục này.</p>
    </div>
</div>
<?php else: ?>
<div class="space-y-4">
<?php foreach ($orders as $ord):
    // Get order items
    $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$ord['id']]);
    $items = $stmtItems->fetchAll();

    $statusBadgeMap = [
        'pending'    => ['badge-pending',    'Chờ xác nhận'],
        'confirmed'  => ['badge-confirmed',  'Đã xác nhận'],
        'preparing'  => ['badge-preparing',  'Đang chuẩn bị'],
        'delivering' => ['badge-delivering', 'Đang giao'],
        'completed'  => ['badge-completed',  'Hoàn thành'],
        'cancelled'  => ['badge-cancelled',  'Đã huỷ'],
    ];
    [$badgeCls, $badgeLbl] = $statusBadgeMap[$ord['status']] ?? ['badge-cancelled', $ord['status']];
?>
<div class="merchant-card">
    <div class="merchant-card-header">
        <div class="flex items-center gap-3">
            <span class="font-black text-gray-800 text-sm">#<?= e($ord['order_code']) ?></span>
            <span class="badge <?= $badgeCls ?>"><?= $badgeLbl ?></span>
        </div>
        <span class="text-xs text-gray-400"><?= date('H:i - d/m/Y', strtotime($ord['created_at'])) ?></span>
    </div>
    <div class="merchant-card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Customer info -->
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Khách hàng</p>
                <p class="font-bold text-gray-800 text-sm"><?= e($ord['customer_name']) ?></p>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-phone mr-1 text-gray-300"></i><?= e($ord['customer_phone']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-location-dot mr-1 text-gray-300"></i><?= e($ord['delivery_address']) ?></p>
                <?php if ($ord['note']): ?>
                <p class="text-xs text-orange-600 mt-1 italic"><i class="fas fa-note-sticky mr-1"></i><?= e($ord['note']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Items -->
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Món đã đặt</p>
                <div class="space-y-1.5">
                    <?php foreach ($items as $it): ?>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-700 flex-1 min-w-0 pr-2">
                            <span class="font-bold text-cica-red">×<?= $it['quantity'] ?></span>
                            <?= e($it['item_name']) ?>
                        </span>
                        <span class="font-bold text-gray-600 flex-shrink-0"><?= fp($it['subtotal']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment + Actions -->
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Thanh toán</p>
                <div class="space-y-1 text-xs text-gray-500">
                    <div class="flex justify-between"><span>Tạm tính</span><span><?= fp($ord['subtotal']) ?></span></div>
                    <div class="flex justify-between"><span>Phí ship</span><span><?= fp($ord['delivery_fee']) ?></span></div>
                    <?php if ($ord['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-green-600"><span>Giảm giá</span><span>-<?= fp($ord['discount_amount']) ?></span></div>
                    <?php endif; ?>
                    <div class="flex justify-between font-black text-gray-800 text-sm pt-1 border-t border-dashed border-gray-200 mt-1">
                        <span>Tổng cộng</span>
                        <span class="text-cica-red"><?= fp($ord['total_amount']) ?></span>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php if (isset($actionButtons[$ord['status']])): $btn = $actionButtons[$ord['status']]; ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                        <input type="hidden" name="action" value="<?= $btn['action'] ?>">
                        <button type="submit" class="<?= $btn['class'] ?>" style="font-size:12px;padding:7px 14px">
                            <i class="fas <?= $btn['icon'] ?>"></i> <?= $btn['label'] ?>
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($ord['status'] === 'pending'): ?>
                    <form method="POST" style="display:inline"
                          onsubmit="return confirm('Huỷ đơn #<?= e($ord['order_code']) ?>?')">
                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn-danger" style="font-size:12px">
                            <i class="fas fa-times"></i> Huỷ đơn
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once 'layout_footer.php'; ?>
