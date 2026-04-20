<?php
// merchant/menu.php - Quản lý món ăn
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../merchant/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) { header('Location: /foodbooking/views/user/profile.php'); exit; }

$rid = $restaurant['id'];
$pageTitle = 'Quản lý Thực đơn';
$activePage = 'menu';

// Lấy danh sách danh mục
$stmtCats = $conn->prepare("SELECT * FROM menu_categories WHERE restaurant_id = ? ORDER BY sort_order ASC, name ASC");
$stmtCats->execute([$rid]);
$categories = $stmtCats->fetchAll();

// Xử lý Form Thêm/Sửa/Xóa
$flashToast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $id = (int)($_POST['item_id'] ?? 0);
            $catId = !empty($_POST['menu_category_id']) ? (int)$_POST['menu_category_id'] : null;
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $price = (int)($_POST['price'] ?? 0);
            $origPrice = !empty($_POST['original_price']) ? (int)$_POST['original_price'] : null;
            $img = trim($_POST['image'] ?? '');
            $isAvail = isset($_POST['is_available']) ? 1 : 0;
            $isBest = isset($_POST['is_best_seller']) ? 1 : 0;
            $sortOrders = (int)($_POST['sort_order'] ?? 0);

            if ($name && $price > 0) {
                if ($action === 'add') {
                    $stmt = $conn->prepare("
                        INSERT INTO menu_items 
                        (restaurant_id, menu_category_id, name, description, price, original_price, image, is_available, is_best_seller, sort_order) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$rid, $catId, $name, $desc, $price, $origPrice, $img, $isAvail, $isBest, $sortOrders]);
                    $flashToast = ['type' => 'success', 'message' => 'Thêm món ăn thành công!'];
                } else {
                    $stmt = $conn->prepare("
                        UPDATE menu_items 
                        SET menu_category_id=?, name=?, description=?, price=?, original_price=?, image=?, is_available=?, is_best_seller=?, sort_order=? 
                        WHERE id=? AND restaurant_id=?
                    ");
                    $stmt->execute([$catId, $name, $desc, $price, $origPrice, $img, $isAvail, $isBest, $sortOrders, $id, $rid]);
                    $flashToast = ['type' => 'success', 'message' => 'Cập nhật món ăn thành công!'];
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['item_id'] ?? 0);
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$id, $rid]);
                $flashToast = ['type' => 'success', 'message' => 'Đã xóa món ăn!'];
            }
        } elseif ($action === 'toggle_status') {
             $id = (int)($_POST['item_id'] ?? 0);
             $status = (int)($_POST['status'] ?? 0);
             if ($id) {
                 $stmt = $conn->prepare("UPDATE menu_items SET is_available = ? WHERE id = ? AND restaurant_id = ?");
                 $stmt->execute([$status, $id, $rid]);
                 $flashToast = ['type' => 'success', 'message' => 'Cập nhật trạng thái thành công!'];
             }
        }
    } catch (Exception $e) {
        $flashToast = ['type' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Lấy danh sách món ăn
$filterCat = $_GET['cat'] ?? '';
$sql = "SELECT mi.*, mc.name as category_name 
        FROM menu_items mi 
        LEFT JOIN menu_categories mc ON mi.menu_category_id = mc.id 
        WHERE mi.restaurant_id = :rid ";
$params = [':rid' => $rid];

if ($filterCat !== '') {
    if ($filterCat === '0') {
        $sql .= " AND mi.menu_category_id IS NULL ";
    } else {
        $sql .= " AND mi.menu_category_id = :cid ";
        $params[':cid'] = $filterCat;
    }
}
$sql .= " ORDER BY mi.menu_category_id ASC, mi.sort_order ASC, mi.id DESC";

$stmtItems = $conn->prepare($sql);
$stmtItems->execute($params);
$items = $stmtItems->fetchAll();

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fp(int $n): string { return number_format($n, 0, ',', '.') . 'đ'; }

require_once 'layout.php';
?>

<!-- Filter -->
<div class="flex gap-2 flex-wrap mb-6 overflow-x-auto pb-2">
    <a href="?cat=" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all border
           <?= $filterCat === '' ? 'bg-cica-red text-white border-cica-red shadow-md shadow-red-200' : 'bg-white text-gray-600 hover:bg-red-50 hover:text-cica-red border-gray-200' ?>">
        Tất cả món
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="?cat=<?= $cat['id'] ?>" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all border
           <?= $filterCat == $cat['id'] ? 'bg-cica-red text-white border-cica-red shadow-md shadow-red-200' : 'bg-white text-gray-600 hover:bg-red-50 hover:text-cica-red border-gray-200' ?>">
        <?= e($cat['name']) ?>
    </a>
    <?php endforeach; ?>
    <a href="?cat=0" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all border
           <?= $filterCat === '0' ? 'bg-cica-red text-white border-cica-red shadow-md shadow-red-200' : 'bg-white text-gray-600 hover:bg-red-50 hover:text-cica-red border-gray-200' ?>">
        Chưa phân loại
    </a>
</div>

<div class="merchant-card">
    <div class="merchant-card-header">
        <h2 class="font-black text-gray-800 text-sm flex items-center gap-2">
            <i class="fas fa-utensils text-cica-red"></i> Danh sách món ăn
        </h2>
        <button onclick="openForm('add', null)" class="btn-primary text-xs">
            <i class="fas fa-plus"></i> Thêm món
        </button>
    </div>
    <div class="merchant-card-body">
        <?php if (empty($items)): ?>
            <div class="text-center py-10">
                <i class="fas fa-box-open text-gray-200 text-5xl mb-3"></i>
                <p class="text-gray-400 text-sm">Chưa có món ăn nào.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($items as $item): ?>
                <div class="border border-gray-100 rounded-2xl overflow-hidden bg-white hover:shadow-md transition">
                    <div class="h-32 bg-gray-100 relative group">
                        <img src="<?= getImageUrl($item['image'] ?? null, '/foodbooking/image/placeholder.jpg') ?>" class="w-full h-full object-cover">
                        <?php if ($item['is_best_seller']): ?>
                        <span class="absolute top-2 left-2 bg-yellow-400 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-sm">
                            <i class="fas fa-star text-xs"></i> BEST SELLER
                        </span>
                        <?php endif; ?>
                        
                        <!-- Overlay actions -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick='openForm("edit", <?= json_encode($item) ?>)' class="w-10 h-10 bg-white text-blue-600 rounded-full hover:bg-blue-50 transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" onsubmit="return confirmDelete(this)">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button class="w-10 h-10 bg-white text-red-600 rounded-full hover:bg-red-50 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-gray-800 text-sm truncate pr-2"><?= e($item['name']) ?></h3>
                            <span class="font-black text-cica-red text-sm shrink-0"><?= fp($item['price']) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-500 line-clamp-2 min-h-[32px]"><?= e($item['description'] ?: 'Không có mô tả') ?></p>
                        
                        <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-md">
                                <i class="fas fa-folder"></i> <?= e($item['category_name'] ?: 'Chưa phân loại') ?>
                            </span>
                            
                            <!-- Toggle status -->
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="status" value="<?= $item['is_available'] ? 0 : 1 ?>">
                                <button type="submit" class="badge <?= $item['is_available'] ? 'badge-available' : 'badge-unavailable' ?> cursor-pointer hover:opacity-80">
                                    <i class="fas <?= $item['is_available'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> 
                                    <?= $item['is_available'] ? 'Còn món' : 'Hết món' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form Món Ăn -->
<div id="modal-item" class="modal-overlay">
    <div class="modal-box">
        <h3 id="modal-title" class="font-black text-lg text-gray-800 mb-4">Thêm món mới</h3>
        <form method="POST">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="item_id" id="form-item-id" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group md:col-span-2">
                    <label class="form-label">Tên món ăn <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="form-name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Giá bán <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="form-price" class="form-input" required min="1000">
                </div>
                <div class="form-group">
                    <label class="form-label">Giá gốc (nếu có giảm giá)</label>
                    <input type="number" name="original_price" id="form-original-price" class="form-input">
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Danh mục</label>
                    <select name="menu_category_id" id="form-category" class="form-input">
                        <option value="">-- Không phân loại --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Mô tả món ăn</label>
                    <textarea name="description" id="form-desc" class="form-input" rows="2"></textarea>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Hình ảnh món ăn</label>
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group cursor-pointer" onclick="document.getElementById('itemImgInput').click()" id="itemImgArea">
                            <div id="itemImgPreview" class="w-full h-full flex flex-col items-center justify-center">
                                <i class="fas fa-image text-gray-300 text-xl mb-1"></i>
                            </div>
                            <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-upload text-white"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="image" id="form-image" class="form-input text-sm mb-2" placeholder="URL ảnh hoặc tải lên...">
                            <p class="text-[10px] text-gray-400">Click icon sửa hoặc kéo thả ảnh để tải lên (Max 5MB)</p>
                            <input type="file" id="itemImgInput" class="hidden" accept="image/*">
                            <div id="itemImgLoading" class="hidden text-xs text-blue-500 font-bold animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i>Đang tải...</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" id="form-sort" class="form-input" value="0">
                </div>
                
                <div class="form-group flex flex-col justify-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer mt-4 text-sm font-semibold text-gray-700">
                        <input type="checkbox" name="is_best_seller" id="form-best" value="1" class="w-4 h-4 text-cica-red rounded">
                        Món bán chạy (Nổi bật)
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-gray-700">
                        <input type="checkbox" name="is_available" id="form-avail" value="1" checked class="w-4 h-4 text-green-600 rounded">
                        Đang mở bán (Còn món)
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-item')">Hủy</button>
                <button type="submit" class="btn-primary">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<script>
function openForm(action, data) {
    document.getElementById('form-action').value = action;
    const title = document.getElementById('modal-title');
    
    if (action === 'edit' && data) {
        title.innerHTML = '<i class="fas fa-edit text-blue-500"></i> Cập nhật món ăn';
        document.getElementById('form-item-id').value = data.id;
        document.getElementById('form-name').value = data.name;
        document.getElementById('form-price').value = data.price;
        document.getElementById('form-original-price').value = data.original_price || '';
        document.getElementById('form-category').value = data.menu_category_id || '';
        document.getElementById('form-desc').value = data.description || '';
        document.getElementById('form-image').value = data.image || '';
        document.getElementById('form-sort').value = data.sort_order;
        document.getElementById('form-best').checked = data.is_best_seller == 1;
        document.getElementById('form-avail').checked = data.is_available == 1;
    } else {
        title.innerHTML = '<i class="fas fa-plus-circle text-cica-red"></i> Thêm món mới';
        document.getElementById('form-item-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-price').value = '';
        document.getElementById('form-original-price').value = '';
        document.getElementById('form-category').value = '';
        document.getElementById('form-desc').value = '';
        document.getElementById('form-image').value = '';
        document.getElementById('form-sort').value = '0';
        document.getElementById('form-best').checked = false;
        document.getElementById('form-avail').checked = true;
    }
    
    openModal('modal-item');
}

document.addEventListener('DOMContentLoaded', () => {
    const area = document.getElementById('itemImgArea');
    const input = document.getElementById('itemImgInput');
    const valElem = document.getElementById('form-image');
    const loading = document.getElementById('itemImgLoading');
    const preview = document.getElementById('itemImgPreview');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
        area.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); });
    });

    area.addEventListener('dragover', () => area.classList.add('border-cica-red', 'bg-red-50/10'));
    area.addEventListener('dragleave', () => area.classList.remove('border-cica-red', 'bg-red-50/10'));
    area.addEventListener('drop', (e) => {
        area.classList.remove('border-cica-red', 'bg-red-50/10');
        if(e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });
    
    input.addEventListener('change', function() {
        if(this.files.length) uploadFile(this.files[0]);
    });

    function uploadFile(file) {
        if (!file.type.match('image.*')) { showToast('error', 'Chỉ hỗ trợ file ảnh'); return; }
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'menu_item');
        loading.classList.remove('hidden');

        fetch('/foodbooking/api/merchant/upload_image.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('hidden');
            if(data.success) {
                valElem.value = data.path;
                preview.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">`;
                showToast('success', 'Tải ảnh món ăn thành công');
            } else showToast('error', data.message);
        }).catch(() => { loading.classList.add('hidden'); showToast('error', 'Lỗi kết nối máy chủ'); });
    }

    // Gắn event để udpate lại preview html
    valElem.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            preview.innerHTML = `<img src="${this.value}" class="w-full h-full object-cover" onerror="this.src='../image/placeholder.jpg'">`;
        } else {
            preview.innerHTML = `<i class="fas fa-image text-gray-300 text-xl mb-1"></i>`;
        }
    });

    // Modifying openForm to also update preview
    const originalOpenForm = openForm;
    window.openForm = function(action, data) {
        originalOpenForm(action, data);
        if (action === 'edit' && data && data.image) {
            preview.innerHTML = `<img src="${data.image}" class="w-full h-full object-cover">`;
        } else {
            preview.innerHTML = `<i class="fas fa-image text-gray-300 text-xl mb-1"></i>`;
        }
    };
});
</script>

<?php require_once 'layout_footer.php'; ?>
