<?php
// views/order/success.php - Trang đặt hàng thành công
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) { header('Location: /foodbooking/views/order/history.php'); exit; }

$stmt = $conn->prepare("
    SELECT o.*, r.name AS restaurant_name, r.image AS restaurant_image, r.delivery_time
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: /foodbooking/views/order/history.php'); exit; }

$stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll();

$statusInfo = getOrderStatusLabel($order['status']);
$pageTitle = "Đặt Hàng Thành Công #{$order['order_code']} - Cicafood";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 py-12">
    
    <!-- Success Animation -->
    <div class="text-center mb-8">
        <div class="relative inline-block">
            <div class="w-28 h-28 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                <div class="absolute inset-0 bg-green-200 rounded-full animate-ping opacity-40"></div>
                <i class="fas fa-check-circle text-green-500 text-6xl relative z-10"></i>
            </div>
        </div>
        <h1 class="text-3xl font-black text-gray-900 mb-2">Đặt Hàng Thành Công!</h1>
        <p class="text-gray-500 mb-4">Đơn hàng của bạn đã được ghi nhận và đang được xử lý</p>
        <div class="inline-flex items-center gap-2 bg-gray-100 px-5 py-2.5 rounded-full">
            <span class="text-sm text-gray-600">Mã đơn hàng:</span>
            <span class="font-black text-gray-900 text-base">#<?= e($order['order_code']) ?></span>
            <button onclick="navigator.clipboard.writeText('<?= e($order['order_code']) ?>').then(()=>showToast('success','Đã sao chép!'))" 
                    class="text-cica-red hover:underline text-xs font-semibold ml-1">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>

    <!-- Order Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        
        <!-- Status bar -->
        <div class="bg-gradient-to-r from-cica-red to-red-600 p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/70 text-xs uppercase tracking-wider mb-1">Trạng thái đơn hàng</p>
                    <div class="flex items-center gap-2">
                        <i class="fas <?= $statusInfo['icon'] ?> text-xl"></i>
                        <span class="font-black text-xl"><?= $statusInfo['label'] ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-white/70 text-xs">Thời gian giao ước tính</p>
                    <p class="font-black text-2xl"><?= $order['delivery_time'] ?> phút</p>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4">Hành Trình Đơn Hàng</h3>
            <?php 
            $statuses = [
                'pending'    => ['Đã đặt hàng', 'Đơn hàng đang chờ xác nhận từ quán'],
                'confirmed'  => ['Quán xác nhận', 'Quán đã nhận đơn và chuẩn bị'],
                'preparing'  => ['Đang chuẩn bị', 'Đầu bếp đang nấu món cho bạn'],
                'delivering' => ['Tài xế đang đến', 'Shipper đang trên đường giao hàng'],
                'completed'  => ['Đã giao hàng', 'Đơn hàng đã được giao thành công'],
            ];
            $statusOrder = array_keys($statuses);
            $currentIdx = array_search($order['status'], $statusOrder);
            ?>
            <div class="space-y-4">
                <?php foreach ($statuses as $step => $info): 
                    $stepIdx = array_search($step, $statusOrder);
                    $isDone    = $stepIdx <= $currentIdx;
                    $isCurrent = $stepIdx === $currentIdx;
                ?>
                <div class="flex items-start gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 <?= $isDone ? 'bg-green-500' : 'bg-gray-200' ?>">
                            <?php if ($isDone): ?>
                            <i class="fas fa-check text-white text-xs"></i>
                            <?php else: ?>
                            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($step !== 'completed'): ?>
                        <div class="w-0.5 h-6 <?= $isDone ? 'bg-green-300' : 'bg-gray-200' ?> mt-1"></div>
                        <?php endif; ?>
                    </div>
                    <div class="pt-1 <?= $isCurrent ? 'opacity-100' : ($isDone ? 'opacity-70' : 'opacity-30') ?>">
                        <p class="font-bold text-gray-800 text-sm <?= $isCurrent ? 'text-green-600' : '' ?>"><?= $info[0] ?></p>
                        <p class="text-xs text-gray-500"><?= $info[1] ?></p>
                    </div>
                    <?php if ($isCurrent): ?>
                    <span class="ml-auto mt-1 bg-green-100 text-green-600 text-[10px] font-black px-2.5 py-1 rounded-full animate-pulse">Hiện tại</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Restaurant + Delivery info -->
        <div class="p-6 grid grid-cols-2 gap-6 border-b border-gray-100">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Từ nhà hàng</p>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
                        <img src="<?= getImageUrl($order['restaurant_image']) ?>" class="w-full h-full object-cover">
                    </div>
                    <p class="font-semibold text-gray-800 text-sm"><?= e($order['restaurant_name']) ?></p>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Giao đến</p>
                <p class="font-semibold text-gray-800 text-sm"><?= e($order['recipient_name']) ?></p>
                <p class="text-xs text-gray-500"><?= e($order['recipient_phone']) ?></p>
                <p class="text-xs text-gray-400 mt-1"><?= e($order['delivery_address']) ?></p>
            </div>
        </div>

        <!-- Order items -->
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4">Các Món Đã Đặt</h3>
            <div class="space-y-2">
                <?php foreach ($items as $oi): ?>
                <div class="flex justify-between text-sm text-gray-700">
                    <span><?= $oi['quantity'] ?>x <?= e($oi['item_name']) ?></span>
                    <span class="font-semibold"><?= formatPrice($oi['subtotal']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price summary -->
        <div class="p-6">
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between"><span>Tạm tính</span><span><?= formatPrice($order['subtotal']) ?></span></div>
                <div class="flex justify-between"><span>Phí giao hàng</span><span class="<?= $order['delivery_fee'] == 0 ? 'text-green-600 font-semibold' : '' ?>"><?= $order['delivery_fee'] == 0 ? 'Miễn phí' : formatPrice($order['delivery_fee']) ?></span></div>
                <div class="flex justify-between"><span>Phí dịch vụ</span><span><?= formatPrice($order['service_fee']) ?></span></div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="flex justify-between text-green-600"><span>Voucher giảm giá</span><span>-<?= formatPrice($order['discount_amount']) ?></span></div>
                <?php endif; ?>
                <?php if ($order['shipping_discount'] > 0): ?>
                <div class="flex justify-between text-green-600"><span>Voucher freeship</span><span>-<?= formatPrice($order['shipping_discount']) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="pt-4 border-t border-gray-100 mt-3 flex justify-between font-black text-xl">
                <span class="text-gray-900">Tổng thanh toán</span>
                <span class="text-cica-red"><?= formatPrice($order['total_amount']) ?></span>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                <i class="fas fa-credit-card text-blue-400"></i>
                Thanh toán: <?= match($order['payment_method']) {
                    'cod' => 'Tiền mặt khi nhận hàng (COD)',
                    'momo' => 'Ví MoMo',
                    'zalopay' => 'ZaloPay',
                    'bank' => 'Chuyển khoản ngân hàng',
                    default => $order['payment_method']
                } ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4">
        <a href="/foodbooking/views/order/history.php" class="bg-white border-2 border-gray-200 text-gray-700 py-4 rounded-2xl font-bold text-center hover:bg-gray-50 hover:border-gray-300 transition">
            <i class="fas fa-receipt mr-2"></i>Đơn hàng của tôi
        </a>
        <a href="/foodbooking/" class="bg-cica-red text-white py-4 rounded-2xl font-bold text-center hover:bg-red-700 transition shadow-lg shadow-red-100 active:scale-95">
            <i class="fas fa-utensils mr-2"></i>Đặt thêm món
        </a>
    </div>

    <!-- Note -->
    <?php if ($order['note']): ?>
    <div class="mt-5 bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
        <p class="text-yellow-800 text-sm"><i class="fas fa-note-sticky mr-2 text-yellow-500"></i><strong>Ghi chú:</strong> <?= e($order['note']) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
