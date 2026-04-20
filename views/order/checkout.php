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

            <!-- 2. Voucher -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-gray-900 text-lg mb-5 flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-ticket text-orange-500 text-sm"></i>
                    </div>
                    Mã Giảm Giá
                </h2>
                
                <!-- Voucher giảm giá món -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">
                        <i class="fas fa-tag text-cica-red mr-1"></i>Voucher Giảm Giá
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="voucher-input" name="voucher_code" 
                               value="<?= e($_POST['voucher_code'] ?? '') ?>"
                               placeholder="Nhập mã giảm giá..."
                               class="flex-1 px-4 py-3 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-sm outline-none focus:border-cica-red uppercase transition"
                               style="text-transform:uppercase">
                        <button type="button" onclick="applyVoucher('discount')"
                                class="bg-cica-red text-white px-5 py-3 rounded-xl text-sm font-bold hover:bg-red-700 transition active:scale-95">
                            Áp dụng
                        </button>
                    </div>
                    <div id="voucher-result" class="mt-2 hidden"></div>
                </div>

                <!-- Voucher freeship -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">
                        <i class="fas fa-motorcycle text-green-500 mr-1"></i>Mã Freeship
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="ship-voucher-input" name="voucher_ship"
                               value="<?= e($_POST['voucher_ship'] ?? '') ?>"
                               placeholder="Nhập mã freeship..."
                               class="flex-1 px-4 py-3 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-sm outline-none focus:border-green-500 uppercase transition"
                               style="text-transform:uppercase">
                        <button type="button" onclick="applyVoucher('ship')"
                                class="bg-green-500 text-white px-5 py-3 rounded-xl text-sm font-bold hover:bg-green-600 transition active:scale-95">
                            Áp dụng
                        </button>
                    </div>
                    <div id="ship-voucher-result" class="mt-2 hidden"></div>
                </div>

                <!-- Gợi ý voucher -->
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" onclick="useVoucherSuggestion('CICA20', 'discount')"
                            class="text-left bg-red-50 border border-red-100 rounded-xl p-3 hover:bg-red-100 transition">
                        <p class="font-bold text-cica-red text-xs">CICA20</p>
                        <p class="text-gray-500 text-[10px]">Giảm 20%, tối đa 50K</p>
                    </button>
                    <button type="button" onclick="useVoucherSuggestion('FREESHIP', 'ship')"
                            class="text-left bg-green-50 border border-green-100 rounded-xl p-3 hover:bg-green-100 transition">
                        <p class="font-bold text-green-600 text-xs">FREESHIP</p>
                        <p class="text-gray-500 text-[10px]">Miễn phí vận chuyển</p>
                    </button>
                </div>
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
                        'cod'     => ['icon' => 'fa-money-bill-wave', 'label' => 'Tiền mặt COD', 'color' => 'text-green-600', 'bg' => 'bg-green-50'],
                        'momo'    => ['icon' => 'fa-wallet', 'label' => 'Ví MoMo', 'color' => 'text-pink-600', 'bg' => 'bg-pink-50'],
                        'zalopay'=> ['icon' => 'fa-mobile-screen', 'label' => 'ZaloPay', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                        'bank'    => ['icon' => 'fa-building-columns', 'label' => 'Chuyển khoản', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50'],
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
                            <span class="text-sm font-semibold text-gray-700"><?= $m['label'] ?></span>
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

    fetch('/foodbooking/api/apply_voucher.php', {
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
</script>
