<?php
// merchant/index.php - Dashboard
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../merchant/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) {
    header('Location: /foodbooking/views/user/profile.php');
    exit;
}

$rid = $restaurant['id'];
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// ---- Thống kê hôm nay ----
$today = date('Y-m-d');

$stmtToday = $conn->prepare("
    SELECT
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) AS revenue,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) AS preparing,
        SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) AS delivering,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
    FROM orders
    WHERE restaurant_id = :rid AND DATE(created_at) = :today
");

$stmtToday->execute([
    ':rid'   => $rid,
    ':today' => $today
]);

$todayStats = $stmtToday->fetch(PDO::FETCH_ASSOC);

// ---- Thống kê tháng này ----
$stmtMonth = $conn->prepare("
    SELECT
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) AS revenue
    FROM orders
    WHERE restaurant_id = :rid
    AND MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
");

$stmtMonth->execute([':rid' => $rid]);
$monthStats = $stmtMonth->fetch(PDO::FETCH_ASSOC);

// ---- Đơn hàng mới nhất ----
$stmtOrders = $conn->prepare("
    SELECT o.*, u.full_name AS customer_name, u.phone AS customer_phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.restaurant_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");

$stmtOrders->execute([$rid]);
$recentOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// ---- Top món bán chạy ----
$stmtTop = $conn->prepare("
    SELECT oi.item_name,
           SUM(oi.quantity) AS sold,
           SUM(oi.subtotal) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.restaurant_id = ?
    AND o.status = 'completed'
    GROUP BY oi.item_name
    ORDER BY sold DESC
    LIMIT 5
");

$stmtTop->execute([$rid]);
$topItems = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

// ---- Chart 7 ngày ----
$stmtChart = $conn->prepare("
    SELECT DATE(created_at) AS date,
           SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) AS revenue
    FROM orders
    WHERE restaurant_id = ?
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$stmtChart->execute([$rid]);
$chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = [];
$chartRevenue = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d/m', strtotime($d));

    $value = 0;
    foreach ($chartData as $row) {
        if ($row['date'] == $d) {
            $value = (int)$row['revenue'];
            break;
        }
    }

    $chartRevenue[] = $value;
}

// ===== Helpers =====
if (!function_exists('e')) {
    function e($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fp')) {
    function fp($n)
    {
        return number_format($n, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status)
    {
        $map = [
            'pending'    => ['badge-pending', 'Chờ xác nhận'],
            'confirmed'  => ['badge-confirmed', 'Đã xác nhận'],
            'preparing'  => ['badge-preparing', 'Đang chuẩn bị'],
            'delivering' => ['badge-delivering', 'Đang giao'],
            'completed'  => ['badge-completed', 'Hoàn thành'],
            'cancelled'  => ['badge-cancelled', 'Đã huỷ']
        ];

        $cls = $map[$status][0] ?? 'badge-cancelled';
        $txt = $map[$status][1] ?? $status;

        return "<span class='badge $cls'>$txt</span>";
    }
}

require_once 'layout.php';
?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-7">

    <div class="stat-card">
        <p class="text-2xl font-black"><?= fp($todayStats['revenue'] ?? 0) ?></p>
        <p class="text-xs text-gray-400">Doanh thu hôm nay</p>
    </div>

    <div class="stat-card">
        <p class="text-2xl font-black"><?= $todayStats['total_orders'] ?? 0 ?></p>
        <p class="text-xs text-gray-400">Tổng đơn hôm nay</p>
    </div>

    <div class="stat-card">
        <p class="text-2xl font-black">
            <?= ($todayStats['pending'] ?? 0) + ($todayStats['preparing'] ?? 0) ?>
        </p>
        <p class="text-xs text-gray-400">Đơn đang xử lý</p>
    </div>

    <div class="stat-card">
        <p class="text-2xl font-black"><?= fp($monthStats['revenue'] ?? 0) ?></p>
        <p class="text-xs text-gray-400">
            <?= $monthStats['total_orders'] ?? 0 ?> đơn tháng này
        </p>
    </div>

</div>

<div class="merchant-card">
    <div class="merchant-card-header">
        <h2>Đơn hàng gần đây</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="merchant-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($recentOrders as $ord): ?>
                <tr>
                    <td>#<?= e($ord['order_code']) ?></td>
                    <td><?= e($ord['customer_name']) ?></td>
                    <td><?= fp($ord['total_amount']) ?></td>
                    <td><?= getStatusBadge($ord['status']) ?></td>
                    <td><?= date('H:i d/m', strtotime($ord['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('revenueChart');

if(ctx){
    new Chart(ctx,{
        type:'bar',
        data:{
            labels:<?= json_encode($chartLabels) ?>,
            datasets:[{
                data:<?= json_encode($chartRevenue) ?>,
                borderWidth:2
            }]
        }
    });
}
</script>

<?php require_once 'layout_footer.php'; ?>