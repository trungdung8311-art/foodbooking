<?php
// views/order/checkout.php - Trang thanh toán ShopeeFood style
require_once __DIR__ . '/../../config/database.php';
requireLogin();

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    setFlash('info', 'Giỏ hàng của bạn đang trống. Hãy thêm món trước nhé!');
    header('Location: /foodbooking/');
    exit;
}

$cartRestaurantId = $_SESSION['cart_restaurant_id'] ?? 0;
if (!$cartRestaurantId) { header('Location: /foodbooking/'); exit; }

// Lấy thông tin nhà hàng
$stmtR = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmtR->execute([$cartRestaurantId]);
$restaurant = $stmtR->fetch();
if (!$restaurant) { header('Location: /foodbooking/'); exit; }

// Lấy thông tin user
$stmtU = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmtU->execute([$_SESSION['user_id']]);
$user = $stmtU->fetch();

// Tính toán giỏ hàng
$subtotal    = getCartSubtotal();
$deliveryFee = $restaurant['delivery_fee'];
$serviceFee  = (int)($subtotal * SERVICE_FEE_PERCENT / 100);

// Xử lý đặt hàng
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $recipientName  = trim($_POST['recipient_name'] ?? '');
    $recipientPhone = trim($_POST['recipient_phone'] ?? '');
    $deliveryAddr   = trim($_POST['delivery_address'] ?? '');
    $note           = trim($_POST['note'] ?? '');
    $paymentMethod  = $_POST['payment_method'] ?? 'cod';
    $voucherCode    = strtoupper(trim($_POST['voucher_code'] ?? ''));
    $voucherShip    = strtoupper(trim($_POST['voucher_ship'] ?? ''));

    if (empty($recipientName))  $errors[] = 'Vui lòng nhập tên người nhận';
    if (empty($recipientPhone)) $errors[] = 'Vui lòng nhập số điện thoại';
    if (empty($deliveryAddr))   $errors[] = 'Vui lòng nhập địa chỉ giao hàng';

    if (empty($errors)) {
        // Xử lý voucher giảm giá
        $discountAmount  = (int)($_POST['discount_amount'] ?? 0);
        $shippingDiscount = (int)($_POST['shipping_discount'] ?? 0);

        // Validate và cập nhật voucher
        $processVoucher = function($code) use ($conn, $subtotal, $cartRestaurantId) {
            if (empty($code)) return ['amount' => 0, 'type' => null, 'id' => null];
            
            $stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ? AND end_date >= CURDATE() AND (restaurant_id IS NULL OR restaurant_id = ?)");
            $stmt->execute([$code, $cartRestaurantId]);
            $v = $stmt->fetch();
            if (!$v || $subtotal < $v['min_order']) return ['amount' => 0, 'type' => null, 'id' => null];
            
            if ($v['usage_limit'] > 0) {
                $stmtUsed = $conn->prepare("SELECT COUNT(*) FROM user_vouchers WHERE voucher_id = ? AND is_used = 1");
                $stmtUsed->execute([$v['id']]);
                if ($stmtUsed->fetchColumn() >= $v['usage_limit']) return ['amount' => 0, 'type' => null, 'id' => null];
            }

            $amount = 0;
            if ($v['type'] === 'percent') {
                $amount = min((int)($subtotal * $v['value'] / 100), $v['max_discount'] ?? 999999);
            } elseif ($v['type'] === 'fixed') {
                $amount = min($v['value'], $subtotal);
            } elseif ($v['type'] === 'freeship') {
                $amount = $v['max_discount'] ?? 30000;
            }
            return ['amount' => $amount, 'type' => $v['type'], 'id' => $v['id']];
        };

        $vResult = $processVoucher($voucherCode);
        $vsResult = $processVoucher($voucherShip);

        $finalDiscount = (int)($_POST['discount_amount'] ?? $vResult['amount']);
        $finalShipDiscount = (int)($_POST['shipping_discount'] ?? $vsResult['amount']);
        
        $finalDeliveryFee = max(0, $deliveryFee - $finalShipDiscount);
        $totalAmount = max(0, $subtotal + $finalDeliveryFee + $serviceFee - $finalDiscount);

        // Tạo đơn hàng
        $orderCode = generateOrderCode();
        
        $conn->beginTransaction();
        try {
            $stmtOrder = $conn->prepare("
                INSERT INTO orders (order_code, user_id, restaurant_id, delivery_address, recipient_name, recipient_phone, note, subtotal, delivery_fee, service_fee, discount_amount, shipping_discount, total_amount, voucher_code, voucher_ship_code, payment_method, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmtOrder->execute([
                $orderCode, $_SESSION['user_id'], $cartRestaurantId,
                $deliveryAddr, $recipientName, $recipientPhone, $note,
                $subtotal, $finalDeliveryFee, $serviceFee,
                $finalDiscount, $finalShipDiscount, $totalAmount,
                $voucherCode ?: null, $voucherShip ?: null, $paymentMethod
            ]);
            $orderId = $conn->lastInsertId();

            // Lưu chi tiết đơn
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, item_name, item_price, quantity, subtotal) VALUES (?,?,?,?,?,?)");
            foreach ($_SESSION['cart'] as $itemId => $cartItem) {
                $stmtItem->execute([
                    $orderId, $itemId, $cartItem['name'],
                    $cartItem['price'], $cartItem['quantity'],
                    $cartItem['price'] * $cartItem['quantity']
                ]);
            }

            // Cập nhật trạng thái voucher
            if ($vResult['id']) {
                $stmtMark = $conn->prepare("INSERT INTO user_vouchers (user_id, voucher_id, is_used) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE is_used = 1");
                $stmtMark->execute([$_SESSION['user_id'], $vResult['id']]);
            }
            if ($vsResult['id'] && $vsResult['id'] !== $vResult['id']) {
                $stmtMark = $conn->prepare("INSERT INTO user_vouchers (user_id, voucher_id, is_used) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE is_used = 1");
                $stmtMark->execute([$_SESSION['user_id'], $vsResult['id']]);
            }

            $conn->commit();

            // Xoá giỏ hàng
            $_SESSION['cart'] = [];
            unset($_SESSION['cart_restaurant_id']);

            // Nếu chọn VNPAY, chuyển hướng sang trang thanh toán
            if ($paymentMethod === 'vnpay') {
                require_once __DIR__ . '/../../config/vnpay.php';
                $paymentUrl = createVNPayPaymentUrl($orderId, $totalAmount, "Thanh toan don hang #{$orderCode}", $_SERVER['REMOTE_ADDR']);
                header("Location: " . $paymentUrl);
                exit;
            }

            setFlash('success', "Đặt hàng thành công! Mã đơn: #{$orderCode}");
            header("Location: /foodbooking/views/order/success.php?id={$orderId}");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
        }
    }
}

$total = $subtotal + $deliveryFee + $serviceFee;
$pageTitle = 'Thanh Toán - Cicafood';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-screen-lg mx-auto px-4 py-6">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/foodbooking/" class="hover:text-cica-red">Trang chủ</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="/foodbooking/views/restaurant/detail.php?id=<?= $cartRestaurantId ?>" class="hover:text-cica-red"><?= e($restaurant['name']) ?></a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-700 font-semibold">Thanh Toán</span>
    </div>

    <!-- Errors -->
    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="fas fa-triangle-exclamation text-red-500 mt-0.5"></i>
            <ul class="text-sm text-red-700 space-y-1">
                <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="checkout-form">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        <!-- ======================================================== -->
        <!-- LEFT: Địa chỉ + Voucher (3/5) -->
        <!-- ======================================================== -->
        <div class="lg:col-span-3 space-y-5">
            
            <!-- 1. Địa chỉ giao hàng -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-gray-900 text-lg mb-5 flex items-center gap-2">
                    <div class="w-8 h-8 bg-cica-red/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-cica-red text-sm"></i>
                    </div>
                    Địa Chỉ Nhận Hàng
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Họ Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" 
                               value="<?= e($_POST['recipient_name'] ?? $user['full_name']) ?>"
                               placeholder="Nguyễn Văn A"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Số Điện Thoại <span class="text-red-500">*</span></label>
                        <input type="tel" name="recipient_phone"
                               value="<?= e($_POST['recipient_phone'] ?? $user['phone']) ?>"
                               placeholder="0912 345 678"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Địa Chỉ Giao Hàng <span class="text-red-500">*</span></label>
                    <textarea name="delivery_address" rows="2" 
                              placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition resize-none"><?= e($_POST['delivery_address'] ?? $user['address']) ?></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                        <i class="fas fa-pen-to-square text-cica-red mr-1"></i>Ghi Chú Cho Quán
                    </label>
                    <textarea name="note" rows="2"
                              placeholder="Ví dụ: Ít đường, nhiều đá, không hành, giao trước 12h..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition resize-none"><?= e($_POST['note'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- 2. Voucher (Nâng cấp với Modal) -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-gray-900 text-lg mb-5 flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-ticket text-orange-500 text-sm"></i>
                    </div>
                    Mã Giảm Giá
                </h2>
                
                <!-- Nút mở Modal chọn voucher -->
                <button type="button" onclick="openVoucherModal()"
                        class="w-full bg-gradient-to-r from-red-50 to-orange-50 border-2 border-dashed border-red-200 rounded-2xl p-4 hover:from-red-100 hover:to-orange-100 transition flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-ticket-alt text-cica-red text-xl"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-black text-gray-800 text-sm">Chọn voucher giảm giá</p>
                            <p class="text-xs text-gray-500 mt-0.5" id="discount-voucher-status">Chưa chọn voucher</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-cica-red transition"></i>
                </button>

                <!-- Voucher giảm giá đã chọn -->
                <div id="discount-voucher-display" class="hidden mt-3 bg-red-50 border border-red-200 rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-red-600"></i>
                        <div>
                            <p class="font-bold text-red-800 text-sm" id="discount-voucher-code"></p>
                            <p class="text-xs text-red-600" id="discount-voucher-save"></p>
                        </div>
                    </div>
                    <button type="button" onclick="removeDiscountVoucher()" class="text-red-500 hover:text-red-700 text-sm font-bold">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Nút chọn voucher freeship -->
                <button type="button" onclick="openFreeshipModal()"
                        class="w-full mt-3 bg-gradient-to-r from-green-50 to-teal-50 border-2 border-dashed border-green-200 rounded-2xl p-4 hover:from-green-100 hover:to-teal-100 transition flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-motorcycle text-green-600 text-xl"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-black text-gray-800 text-sm">Chọn voucher freeship</p>
                            <p class="text-xs text-gray-500 mt-0.5" id="freeship-voucher-status">Chưa chọn voucher</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600 transition"></i>
                </button>

                <!-- Voucher freeship đã chọn -->
                <div id="freeship-voucher-display" class="hidden mt-3 bg-green-50 border border-green-200 rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-600"></i>
                        <div>
                            <p class="font-bold text-green-800 text-sm" id="freeship-voucher-code"></p>
                            <p class="text-xs text-green-600" id="freeship-voucher-save"></p>
                        </div>
                    </div>
                    <button type="button" onclick="removeFreeshipVoucher()" class="text-red-500 hover:text-red-700 text-sm font-bold">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Hidden inputs -->
                <input type="hidden" name="voucher_code" id="voucher-code-input" value="">
                <input type="hidden" name="voucher_ship" id="ship-voucher-code-input" value="">
            </div>

            <!-- 3. Phương thức thanh toán -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-gray-900 text-lg mb-5 flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-blue-500 text-sm"></i>
                    </div>
                    Phương Thức Thanh Toán
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <?php 
                    $methods = [
                        'cod'     => ['icon' => 'fa-money-bill-wave', 'label' => 'Tiền mặt COD', 'color' => 'text-green-600', 'bg' => 'bg-green-50', 'desc' => 'Thanh toán khi nhận hàng'],
                        'vnpay'   => ['icon' => 'fa-credit-card', 'label' => 'VNPAY', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'desc' => 'Thanh toán qua VNPAY'],
                    ];
                    foreach ($methods as $val => $m): ?>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="<?= $val ?>" 
                               <?= ($val === 'cod') ? 'checked' : '' ?>
                               class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-2xl p-4 flex items-center gap-3 peer-checked:border-cica-red peer-checked:bg-red-50 transition">
                            <div class="w-9 h-9 <?= $m['bg'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas <?= $m['icon'] ?> <?= $m['color'] ?>"></i>
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-semibold text-gray-700 block"><?= $m['label'] ?></span>
                                <span class="text-xs text-gray-400"><?= $m['desc'] ?></span>
                            </div>
                        </div>
                        <div class="absolute top-3 right-3 w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-cica-red peer-checked:bg-cica-red flex items-center justify-center hidden peer-checked:flex">
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- RIGHT: Tóm tắt đơn + Thanh toán (2/5) -->
        <!-- ======================================================== -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-36">
                <!-- Restaurant info -->
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-4">
                    <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0">
                        <img src="<?= getImageUrl($restaurant['image']) ?>" alt="" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm"><?= e($restaurant['name']) ?></h3>
                        <p class="text-xs text-gray-400"><?= count($_SESSION['cart']) ?> loại món</p>
                    </div>
                </div>

                <!-- Order items -->
                <div class="space-y-2.5 mb-4 max-h-56 overflow-y-auto">
                    <?php foreach ($_SESSION['cart'] as $itemId => $item): ?>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="bg-cica-red/10 text-cica-red font-bold text-xs rounded-md px-1.5 py-0.5"><?= $item['quantity'] ?>x</span>
                            <span class="text-gray-700 truncate max-w-[140px]"><?= e($item['name']) ?></span>
                        </div>
                        <span class="font-semibold text-gray-700 flex-shrink-0"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-2 pt-4 border-t border-dashed border-gray-200">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tạm tính</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Phí giao hàng</span>
                        <span id="delivery-display" class="<?= $deliveryFee == 0 ? 'text-green-600 font-bold' : '' ?>">
                            <?= $deliveryFee == 0 ? 'Miễn phí' : formatPrice($deliveryFee) ?>
                        </span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Phí dịch vụ (<?= SERVICE_FEE_PERCENT ?>%)</span>
                        <span><?= formatPrice($serviceFee) ?></span>
                    </div>
                    
                    <!-- Discount rows (hidden by default) -->
                    <div id="discount-row" class="flex justify-between text-sm text-green-600 hidden">
                        <span>Voucher giảm giá</span>
                        <span id="discount-display">-0đ</span>
                    </div>
                    <div id="ship-discount-row" class="flex justify-between text-sm text-green-600 hidden">
                        <span>Voucher freeship</span>
                        <span id="ship-discount-display">-0đ</span>
                    </div>

                    <!-- Hidden inputs for discount values -->
                    <input type="hidden" name="discount_amount" id="discount-amount-input" value="0">
                    <input type="hidden" name="shipping_discount" id="shipping-discount-input" value="0">
                    
                    <div class="pt-3 border-t border-gray-200 flex justify-between font-black text-lg">
                        <span class="text-gray-900">Tổng thanh toán</span>
                        <span class="text-cica-red" id="total-final"><?= formatPrice($total) ?></span>
                    </div>
                </div>

                <!-- Place Order -->
                <button type="submit" name="place_order"
                        class="w-full bg-cica-red text-white py-4 rounded-2xl font-black text-base mt-5 shadow-lg shadow-red-100 hover:bg-red-700 transition active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Đặt Hàng Ngay
                </button>
                
                <p class="text-center text-xs text-gray-400 mt-3">
                    Bằng cách đặt hàng, bạn đồng ý với <a href="#" class="text-cica-red hover:underline">Điều khoản dịch vụ</a> của Cicafood
                </p>
            </div>
        </div>
    </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
const SUBTOTAL    = <?= $subtotal ?>;
const SHIP_FEE    = <?= $deliveryFee ?>;
const SERVICE_FEE = <?= $serviceFee ?>;

let currentDiscount = 0;
let currentShipDiscount = 0;

function applyVoucher(type) {
    const isShip = type === 'ship';
    const input = document.getElementById(isShip ? 'ship-voucher-input' : 'voucher-input');
    const resultDiv = document.getElementById(isShip ? 'ship-voucher-result' : 'voucher-result');
    const code = input.value.trim().toUpperCase();

    if (!code) { showVoucherMsg(resultDiv, 'error', 'Vui lòng nhập mã voucher'); return; }

    fetch('/foodbooking/api/order/apply_voucher.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ code: code, subtotal: SUBTOTAL, type: type })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showVoucherMsg(resultDiv, 'success', data.message + ' — Tiết kiệm ' + data.discount_formatted);
            
            if (data.voucher.type === 'freeship') {
                currentShipDiscount = data.discount_amount;
                document.getElementById('shipping-discount-input').value = currentShipDiscount;
                document.getElementById('ship-discount-row').classList.remove('hidden');
                document.getElementById('ship-discount-display').textContent = '-' + data.discount_formatted;
            } else {
                currentDiscount = data.discount_amount;
                document.getElementById('discount-amount-input').value = currentDiscount;
                document.getElementById('discount-row').classList.remove('hidden');
                document.getElementById('discount-display').textContent = '-' + data.discount_formatted;
            }
            recalcTotal();
        } else {
            showVoucherMsg(resultDiv, 'error', data.message);
        }
    })
    .catch(() => showVoucherMsg(resultDiv, 'error', 'Lỗi kết nối. Thử lại!'));
}

function showVoucherMsg(el, type, msg) {
    el.classList.remove('hidden');
    const colors = { success: 'bg-green-50 text-green-700 border-green-200', error: 'bg-red-50 text-red-700 border-red-200' };
    const icons  = { success: 'fa-check-circle', error: 'fa-times-circle' };
    el.innerHTML = `<div class="text-xs font-medium flex items-center gap-2 p-2 rounded-lg border ${colors[type]}"><i class="fas ${icons[type]}"></i>${msg}</div>`;
}

function recalcTotal() {
    const newShip = Math.max(0, SHIP_FEE - currentShipDiscount);
    const total   = Math.max(0, SUBTOTAL + newShip + SERVICE_FEE - currentDiscount);
    document.getElementById('total-final').textContent = total.toLocaleString('vi-VN') + 'đ';
    if (newShip === 0) {
        document.getElementById('delivery-display').textContent = 'Miễn phí';
        document.getElementById('delivery-display').className = 'text-green-600 font-bold';
    } else {
        document.getElementById('delivery-display').textContent = newShip.toLocaleString('vi-VN') + 'đ';
    }
}

function useVoucherSuggestion(code, type) {
    const isShip = type === 'ship';
    const input = document.getElementById(isShip ? 'ship-voucher-input' : 'voucher-input');
    input.value = code;
    applyVoucher(type);
}

// ============================================================
// MODAL VOUCHER - CHỌN 2 VOUCHER ĐỒNG THỜI
// ============================================================

let availableVouchers = [];
let selectedDiscountVoucher = null;
let selectedFreeshipVoucher = null;
let currentModalType = 'discount'; // 'discount' hoặc 'freeship'

// Mở Modal chọn voucher giảm giá
function openVoucherModal() {
    currentModalType = 'discount';
    document.getElementById('modal-title').textContent = 'Chọn Voucher Giảm Giá';
    document.getElementById('modal-subtitle').textContent = 'Chọn voucher giảm giá cho đơn hàng';
    openModal();
}

// Mở Modal chọn voucher freeship
function openFreeshipModal() {
    currentModalType = 'freeship';
    document.getElementById('modal-title').textContent = 'Chọn Voucher Freeship';
    document.getElementById('modal-subtitle').textContent = 'Chọn voucher miễn phí vận chuyển';
    openModal();
}

// Mở Modal chung
function openModal() {
    const modal = document.getElementById('voucher-modal');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('active'), 10);
    loadAvailableVouchers();
}

// Đóng Modal
function closeVoucherModal() {
    const modal = document.getElementById('voucher-modal');
    modal.classList.remove('active');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

// Load danh sách voucher từ API
function loadAvailableVouchers() {
    const container = document.getElementById('voucher-list');
    container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i><p class="text-gray-400 mt-2">Đang tải voucher...</p></div>';
    
    fetch(`/foodbooking/api/voucher/get_available_vouchers.php?restaurant_id=<?= $cartRestaurantId ?>&subtotal=${SUBTOTAL}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                availableVouchers = data.vouchers;
                renderVoucherList(data.vouchers);
            } else {
                container.innerHTML = '<div class="text-center py-8 text-red-500">' + data.message + '</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div class="text-center py-8 text-red-500">Lỗi kết nối. Vui lòng thử lại.</div>';
        });
}

// Render danh sách voucher (lọc theo loại đang chọn)
function renderVoucherList(vouchers) {
    const container = document.getElementById('voucher-list');
    
    // Lọc voucher theo loại modal đang mở
    const filteredVouchers = vouchers.filter(v => {
        if (currentModalType === 'freeship') {
            return v.type === 'freeship';
        } else {
            return v.type !== 'freeship'; // percent hoặc fixed
        }
    });
    
    if (filteredVouchers.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-ticket-alt text-5xl text-gray-200 mb-3"></i>
                <p class="text-gray-400 font-medium">Không có voucher khả dụng</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    filteredVouchers.forEach(v => {
        const isValid = v.is_valid;
        const isFreeship = v.type === 'freeship';
        const colorClass = isFreeship ? 'green' : 'red';
        const bgClass = isValid ? `bg-${colorClass}-50` : 'bg-gray-100';
        const borderClass = isValid ? `border-${colorClass}-200` : 'border-gray-200';
        const textClass = isValid ? `text-${colorClass}-600` : 'text-gray-400';
        const opacity = isValid ? '' : 'opacity-60';
        const cursor = isValid ? 'cursor-pointer hover:shadow-md' : 'cursor-not-allowed';
        
        const icon = v.type === 'freeship' ? 'fa-motorcycle' : (v.type === 'percent' ? 'fa-percent' : 'fa-money-bill-wave');
        const valueDisplay = v.type === 'percent' ? `${v.value}%` : (v.type === 'freeship' ? 'Freeship' : v.discount_formatted);
        
        html += `
            <div onclick="${isValid ? `selectVoucher(${v.id})` : ''}" 
                 class="bg-white border-2 ${borderClass} rounded-2xl flex overflow-hidden ${opacity} ${cursor} transition"
                 id="voucher-item-${v.id}">
                
                <div class="w-20 ${bgClass} border-r-2 border-dashed ${borderClass} flex flex-col items-center justify-center p-2 relative">
                    <div class="absolute -top-3 -right-3 w-6 h-6 bg-gray-50 rounded-full border border-gray-200"></div>
                    <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-gray-50 rounded-full border border-gray-200"></div>
                    <i class="fas ${icon} text-2xl ${textClass} mb-1"></i>
                    <span class="text-[9px] font-black uppercase text-center ${textClass} leading-tight">${valueDisplay}</span>
                </div>
                
                <div class="flex-1 p-4">
                    ${v.restaurant_name ? `
                        <span class="text-[10px] font-bold text-cica-red mb-1 flex items-center gap-1">
                            <i class="fas fa-store"></i> ${v.restaurant_name}
                        </span>
                    ` : `
                        <span class="text-[10px] font-bold text-blue-500 mb-1 flex items-center gap-1">
                            <i class="fas fa-globe"></i> Toàn sàn Cicafood
                        </span>
                    `}
                    
                    <h3 class="font-black text-gray-800 text-sm mb-1">${v.name}</h3>
                    <p class="text-xs text-gray-500 mb-2">${v.description}</p>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">${v.code}</span>
                            <p class="text-[10px] text-gray-400 mt-1">HSD: ${new Date(v.end_date).toLocaleDateString('vi-VN')}</p>
                        </div>
                        
                        ${isValid ? `
                            <div class="text-right">
                                <p class="text-xs font-bold text-green-600">Tiết kiệm ${v.discount_formatted}</p>
                                <p class="text-[10px] text-gray-400">Đơn từ ${v.min_order_formatted}</p>
                            </div>
                        ` : `
                            <div class="text-right">
                                <p class="text-xs font-bold text-red-500">Không đủ điều kiện</p>
                                <p class="text-[10px] text-gray-500">${v.reason}</p>
                            </div>
                        `}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Chọn voucher
function selectVoucher(voucherId) {
    const voucher = availableVouchers.find(v => v.id === voucherId);
    if (!voucher || !voucher.is_valid) return;
    
    if (currentModalType === 'freeship') {
        // Chọn voucher freeship
        selectedFreeshipVoucher = voucher;
        
        document.getElementById('freeship-voucher-status').textContent = `${voucher.code} - Tiết kiệm ${voucher.discount_formatted}`;
        document.getElementById('freeship-voucher-display').classList.remove('hidden');
        document.getElementById('freeship-voucher-code').textContent = voucher.code;
        document.getElementById('freeship-voucher-save').textContent = `Tiết kiệm ${voucher.discount_formatted}`;
        
        document.getElementById('ship-voucher-code-input').value = voucher.code;
        currentShipDiscount = voucher.discount_amount;
        document.getElementById('shipping-discount-input').value = currentShipDiscount;
        document.getElementById('ship-discount-row').classList.remove('hidden');
        document.getElementById('ship-discount-display').textContent = '-' + voucher.discount_formatted;
        
    } else {
        // Chọn voucher giảm giá
        selectedDiscountVoucher = voucher;
        
        document.getElementById('discount-voucher-status').textContent = `${voucher.code} - Tiết kiệm ${voucher.discount_formatted}`;
        document.getElementById('discount-voucher-display').classList.remove('hidden');
        document.getElementById('discount-voucher-code').textContent = voucher.code;
        document.getElementById('discount-voucher-save').textContent = `Tiết kiệm ${voucher.discount_formatted}`;
        
        document.getElementById('voucher-code-input').value = voucher.code;
        currentDiscount = voucher.discount_amount;
        document.getElementById('discount-amount-input').value = currentDiscount;
        document.getElementById('discount-row').classList.remove('hidden');
        document.getElementById('discount-display').textContent = '-' + voucher.discount_formatted;
    }
    
    recalcTotal();
    closeVoucherModal();
}

// Xóa voucher giảm giá
function removeDiscountVoucher() {
    selectedDiscountVoucher = null;
    document.getElementById('discount-voucher-status').textContent = 'Chưa chọn voucher';
    document.getElementById('discount-voucher-display').classList.add('hidden');
    document.getElementById('voucher-code-input').value = '';
    
    currentDiscount = 0;
    document.getElementById('discount-amount-input').value = 0;
    document.getElementById('discount-row').classList.add('hidden');
    
    recalcTotal();
}

// Xóa voucher freeship
function removeFreeshipVoucher() {
    selectedFreeshipVoucher = null;
    document.getElementById('freeship-voucher-status').textContent = 'Chưa chọn voucher';
    document.getElementById('freeship-voucher-display').classList.add('hidden');
    document.getElementById('ship-voucher-code-input').value = '';
    
    currentShipDiscount = 0;
    document.getElementById('shipping-discount-input').value = 0;
    document.getElementById('ship-discount-row').classList.add('hidden');
    
    recalcTotal();
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<!-- Modal Chọn Voucher (Dùng chung cho cả 2 loại) -->
<div id="voucher-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-end md:items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white w-full md:max-w-2xl md:rounded-3xl rounded-t-3xl max-h-[85vh] flex flex-col transform translate-y-full md:translate-y-0 md:scale-95 transition-transform duration-300"
         id="voucher-modal-content">
        
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-cica-red"></i> <span id="modal-title">Chọn Voucher</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1" id="modal-subtitle">Chọn voucher phù hợp với đơn hàng</p>
            </div>
            <button onclick="closeVoucherModal()" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition">
                <i class="fas fa-times text-gray-600"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6">
            <div id="voucher-list" class="space-y-3"></div>
        </div>
        
        <div class="p-6 border-t border-gray-100 bg-gray-50">
            <p class="text-xs text-gray-500 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                Voucher xám là chưa đủ điều kiện sử dụng
            </p>
        </div>
    </div>
</div>

<style>
#voucher-modal.active {
    opacity: 1;
}
#voucher-modal.active #voucher-modal-content {
    transform: translateY(0) scale(1);
}
</style>
</script>
