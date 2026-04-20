<?php
// merchant/vouchers.php - Quản lý Voucher Của Quán
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) { header('Location: /foodbooking/views/user/profile.php'); exit; }
$rid = $restaurant['id'];

$pageTitle = 'Quản lý Voucher';
$activePage = 'vouchers';

// Handle Add/Edit Voucher
$flashToast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add' || $action === 'edit') {
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $desc = trim($_POST['description'] ?? '');
            $type = $_POST['type'] ?? 'fixed'; // fixed, percent, freeship
            $value = (int)$_POST['value'];
            $max_discount = (int)($_POST['max_discount'] ?? $value);
            $min_order = (int)$_POST['min_order'];
            $usage_limit = (int)$_POST['usage_limit'];
            $end_date = $_POST['end_date'];

            if (empty($code) || empty($name) || empty($end_date)) {
                $flashToast = ['type' => 'error', 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc.'];
            } else {
                if ($action === 'add') {
                    $stmtCheck = $conn->prepare("SELECT id FROM vouchers WHERE code = ? AND restaurant_id = ?");
                    $stmtCheck->execute([$code, $rid]);
                    if ($stmtCheck->fetch()) {
                        $flashToast = ['type' => 'error', 'message' => 'Mã Voucher đã tồn tại!'];
                    } else {
                        $stmtIns = $conn->prepare("
                            INSERT INTO vouchers (restaurant_id, code, name, description, type, value, max_discount, min_order, usage_limit, end_date)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtIns->execute([$rid, $code, $name, $desc, $type, $value, $max_discount, $min_order, $usage_limit, $end_date]);
                        $flashToast = ['type' => 'success', 'message' => 'Thêm Voucher thành công!'];
                    }
                } elseif ($action === 'edit') {
                    $vid = (int)$_POST['voucher_id'];
                    $stmtUp = $conn->prepare("
                        UPDATE vouchers SET code=?, name=?, description=?, type=?, value=?, max_discount=?, min_order=?, usage_limit=?, end_date=?
                        WHERE id=? AND restaurant_id=?
                    ");
                    $stmtUp->execute([$code, $name, $desc, $type, $value, $max_discount, $min_order, $usage_limit, $end_date, $vid, $rid]);
                    $flashToast = ['type' => 'success', 'message' => 'Cập nhật Voucher thành công!'];
                }
            }
        } elseif ($action === 'delete') {
            $vid = (int)$_POST['voucher_id'];
            $stmtDel = $conn->prepare("DELETE FROM vouchers WHERE id=? AND restaurant_id=?");
            $stmtDel->execute([$vid, $rid]);
            $flashToast = ['type' => 'success', 'message' => 'Đã xoá Voucher.'];
        }
    }
}

// Lấy danh sách vouchers của quán
$stmtList = $conn->prepare("
    SELECT v.*, 
    (SELECT COUNT(*) FROM user_vouchers uv WHERE uv.voucher_id = v.id AND uv.is_used = 1) AS used_count
    FROM vouchers v
    WHERE v.restaurant_id = ?
    ORDER BY v.id DESC
");
$stmtList->execute([$rid]);
$vouchers = $stmtList->fetchAll();

function e(string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function formatVND(int $n): string { return number_format($n, 0, ',', '.') . 'đ'; }

require_once 'layout.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-black text-gray-800">Quản lý Voucher Khuyến Mãi</h1>
    <button onclick="openModal('addModal')" class="btn-primary text-sm px-4 py-2">
        <i class="fas fa-plus mr-1"></i> Thêm Voucher
    </button>
</div>

<?php if (empty($vouchers)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
    <i class="fas fa-ticket-alt text-5xl text-gray-200 mb-4"></i>
    <p class="text-gray-500 font-medium bg-gray-50 max-w-sm mx-auto p-4 rounded-xl">Bạn chưa tạo voucher nào. Tạo ngay để thu hút thêm khách hàng!</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($vouchers as $v): 
        $isActive = strtotime($v['end_date']) >= strtotime(date('Y-m-d'));
        if ($v['usage_limit'] > 0 && $v['used_count'] >= $v['usage_limit']) $isActive = false;
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <span class="font-black text-cica-red tracking-wider font-mono bg-red-100 px-3 py-1 rounded-lg">
                <?= e($v['code']) ?>
            </span>
            <?php if ($isActive): ?>
            <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full"><i class="fas fa-circle text-[8px] mr-1"></i>Đang chạy</span>
            <?php else: ?>
            <span class="text-xs font-bold text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full">Hết hạn</span>
            <?php endif; ?>
        </div>
        <div class="p-5">
            <h3 class="font-bold text-gray-800 text-lg mb-1"><?= e($v['name']) ?></h3>
            <p class="text-xs text-gray-500 mb-4 line-clamp-2"><?= e($v['description']) ?></p>
            
            <div class="space-y-2 text-sm text-gray-600 mb-5">
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-400">Giảm:</span>
                    <span class="font-bold text-gray-800">
                        <?= $v['type'] === 'percent' ? $v['value'].'%' : formatVND($v['value']) ?>
                        <?= $v['type'] === 'percent' ? ' (Tối đa '.formatVND($v['max_discount']).')' : '' ?>
                    </span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-400">Đơn t.thiểu:</span>
                    <span class="font-bold text-gray-800"><?= formatVND($v['min_order']) ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-400">HSD:</span>
                    <span class="font-bold text-gray-800"><?= date('d/m/Y', strtotime($v['end_date'])) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Đã dùng:</span>
                    <span class="font-bold text-cica-red"><?= $v['used_count'] ?> / <?= $v['usage_limit'] ?></span>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button onclick='openEditModal(<?= json_encode($v) ?>)' 
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 rounded-xl text-sm transition">
                    <i class="fas fa-edit mr-1"></i> Sửa
                </button>
                <form method="POST" onsubmit="return confirm('Xoá voucher này?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="voucher_id" value="<?= $v['id'] ?>">
                    <button type="submit" class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-500 rounded-xl flex items-center justify-center transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- MODAL: ADD/EDIT VOUCHER -->
<div id="addModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('addModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl w-full max-w-lg p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black text-gray-900 mb-5" id="modalTitle">Thêm Voucher Mới</h2>
        
        <form method="POST" id="voucherForm" class="space-y-4">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="voucher_id" id="formVoucherId" value="">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mã Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="fCode" class="form-input uppercase" placeholder="VD: SUMMER20" required>
                </div>
                <div>
                    <label class="form-label">Tên Voucher <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="fName" class="form-input" placeholder="Giảm 20%" required>
                </div>
            </div>
            
            <div>
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="description" id="fDesc" rows="2" class="form-input text-sm" placeholder="Giảm 20% cho đơn từ..."></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 mt-2">
                <div>
                    <label class="form-label">Loại Voucher</label>
                    <select name="type" id="fType" class="form-input text-sm" onchange="toggleType()">
                        <option value="fixed">Giảm số tiền cố định</option>
                        <option value="percent">Giảm theo %</option>
                        <option value="freeship">Miễn phí vận chuyển</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" id="lblValue">Số tiền giảm (VND)</label>
                    <input type="number" name="value" id="fValue" class="form-input" required min="1">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div id="wrapMax">
                    <label class="form-label">Giảm tối đa (VND)</label>
                    <input type="number" name="max_discount" id="fMax" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Đơn tối thiểu (VND) <span class="text-red-500">*</span></label>
                    <input type="number" name="min_order" id="fMin" class="form-input" required min="0">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 mt-2">
                <div>
                    <label class="form-label">Lượt sử dụng tối đa <span class="text-red-500">*</span></label>
                    <input type="number" name="usage_limit" id="fLimit" class="form-input" required min="1">
                </div>
                <div>
                    <label class="form-label">Ngày hết hạn <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" id="fEnd" class="form-input" required>
                </div>
            </div>
            
            <div class="flex gap-3 pt-6">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Hủy</button>
                <button type="submit" class="flex-1 btn-primary py-3">Lưu Mã</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Thêm Voucher Mới';
    document.getElementById('voucherForm').reset();
    toggleType();
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(v) {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa Voucher';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formVoucherId').value = v.id;
    
    document.getElementById('fCode').value = v.code;
    document.getElementById('fName').value = v.name;
    document.getElementById('fDesc').value = v.description;
    document.getElementById('fType').value = v.type;
    document.getElementById('fValue').value = v.value;
    document.getElementById('fMax').value = v.max_discount;
    document.getElementById('fMin').value = v.min_order;
    document.getElementById('fLimit').value = v.usage_limit;
    document.getElementById('fEnd').value = v.end_date.substring(0,10);
    
    toggleType();
}

function toggleType() {
    const t = document.getElementById('fType').value;
    const l = document.getElementById('lblValue');
    const m = document.getElementById('wrapMax');
    
    if (t === 'percent') {
        l.textContent = 'Phần trăm giảm (%)';
        m.style.visibility = 'visible';
    } else if (t === 'freeship') {
        l.textContent = '% Phí ship hỗ trợ';
        document.getElementById('fValue').value = 100;
        m.style.visibility = 'visible';
    } else {
        l.textContent = 'Số tiền giảm (VND)';
        m.style.visibility = 'hidden';
        document.getElementById('fMax').value = 0;
    }
}
</script>

<?php require_once 'layout_footer.php'; ?>
