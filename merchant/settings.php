<?php
// merchant/settings.php - Cài đặt nhà hàng
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/provinces.php';
require_once __DIR__ . '/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) { header('Location: /foodbooking/views/user/profile.php'); exit; }

$rid = $restaurant['id'];
$pageTitle = 'Thông tin Quán';
$activePage = 'settings';

$flashToast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_restaurant'])) {
    
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $province = trim($_POST['province'] ?? 'TP. Hồ Chí Minh');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $minOrders = (int)($_POST['min_order'] ?? 0);
    $fee = (int)($_POST['delivery_fee'] ?? 0);
    $time = (int)($_POST['delivery_time'] ?? 0);
    $img = trim($_POST['image'] ?? '');
    $cover = trim($_POST['cover_image'] ?? '');
    $openT = $_POST['open_time'] ?? '07:00:00';
    $closeT = $_POST['close_time'] ?? '22:00:00';
    
    if ($name && $address) {
        $stmt = $conn->prepare("
            UPDATE restaurants SET 
                name=?, description=?, province=?, address=?, phone=?, min_order=?, delivery_fee=?, 
                delivery_time=?, image=?, cover_image=?, open_time=?, close_time=?
            WHERE id=?
        ");
        $stmt->execute([$name, $desc, $province, $address, $phone, $minOrders, $fee, $time, $img, $cover, $openT, $closeT, $rid]);
        $flashToast = ['type' => 'success', 'message' => 'Lưu cài đặt thành công!'];
        // Relaod data
        $restaurant = getMerchantRestaurant($conn);
    } else {
        $flashToast = ['type' => 'error', 'message' => 'Tên quán và địa chỉ không được trống.'];
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

require_once 'layout.php';
?>

<div class="max-w-4xl">
    <form method="POST" class="space-y-6">
        
        <!-- General Info -->
        <div class="merchant-card">
            <div class="merchant-card-header">
                <h2 class="font-black text-gray-800 text-sm flex items-center gap-2">
                    <i class="fas fa-store text-cica-red"></i> Thông tin chung
                </h2>
            </div>
            <div class="merchant-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Tên quán ăn <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?= e($restaurant['name']) ?>" class="form-input" required>
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Địa chỉ <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select name="province" class="form-input w-1/3">
                                <?php foreach(getProvinces() as $p): ?>
                                <option value="<?= e($p) ?>" <?= ($restaurant['province'] ?? 'TP. Hồ Chí Minh') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="address" value="<?= e($restaurant['address']) ?>" class="form-input w-2/3" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" value="<?= e($restaurant['phone'] ?? '') ?>" class="form-input">
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Mô tả quán</label>
                        <textarea name="description" rows="3" class="form-input"><?= e($restaurant['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Operating & Delivery -->
        <div class="merchant-card">
            <div class="merchant-card-header">
                <h2 class="font-black text-gray-800 text-sm flex items-center gap-2">
                    <i class="fas fa-clock text-cica-red"></i> Vận hành & Giao hàng
                </h2>
            </div>
            <div class="merchant-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">Giờ mở cửa</label>
                        <input type="time" name="open_time" value="<?= e($restaurant['open_time']) ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giờ đóng cửa</label>
                        <input type="time" name="close_time" value="<?= e($restaurant['close_time']) ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thời gian giao dự kiến (phút)</label>
                        <input type="number" name="delivery_time" value="<?= $restaurant['delivery_time'] ?>" class="form-input" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phí giao hàng cơ bản (VND)</label>
                        <input type="number" name="delivery_fee" value="<?= $restaurant['delivery_fee'] ?>" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn tối thiểu (VND)</label>
                        <input type="number" name="min_order" value="<?= $restaurant['min_order'] ?>" class="form-input" min="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="merchant-card">
            <div class="merchant-card-header">
                <h2 class="font-black text-gray-800 text-sm flex items-center gap-2">
                    <i class="fas fa-image text-cica-red"></i> Hình ảnh
                </h2>
            </div>
            <div class="merchant-card-body">
                <div class="grid grid-cols-1 gap-5">
                    <!-- Avatar / Logo -->
                    <div class="form-group">
                        <label class="form-label">Ảnh đại diện (Logo)</label>
                        <div class="flex items-start gap-4">
                            <div class="w-24 h-24 rounded-xl overflow-hidden shadow-sm border-2 border-dashed border-gray-300 flex-shrink-0 relative group cursor-pointer" onclick="document.getElementById('logoInput').click()" id="logoArea">
                                <?php if($restaurant['image']): ?>
                                    <img src="<?= getImageUrl($restaurant['image']) ?>" class="w-full h-full object-cover" id="logoPreviewImg">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-50 flex items-center justify-center" id="logoPreviewImg">
                                        <i class="fas fa-store text-gray-300 text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-cloud-upload-alt text-white"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-2">Tải lên logo quán ăn. Định dạng: JPG, PNG. Dung lượng tối đa: 5MB.</p>
                                <input type="file" id="logoInput" class="hidden" accept="image/*">
                                <input type="hidden" name="image" id="logoVal" value="<?= e($restaurant['image'] ?? '') ?>">
                                <div id="logoLoading" class="hidden text-xs text-blue-500 font-bold animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i>Đang tải...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cover Image -->
                    <div class="form-group border-t border-gray-100 pt-5">
                        <label class="form-label">Ảnh bìa (Cover)</label>
                        <div class="w-full h-40 rounded-2xl overflow-hidden shadow-sm border-2 border-dashed border-gray-300 relative group cursor-pointer" onclick="document.getElementById('coverInput').click()" id="coverArea">
                            <?php if($restaurant['cover_image']): ?>
                                <img src="<?= getImageUrl($restaurant['cover_image']) ?>" class="w-full h-full object-cover" id="coverPreviewImg">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-50 flex flex-col items-center justify-center" id="coverPreviewImg">
                                    <i class="fas fa-image text-gray-300 text-4xl mb-2"></i>
                                    <p class="text-sm text-gray-400 font-medium">Click hoặc kéo thả ảnh bìa vào đây</p>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-cloud-upload-alt text-white text-3xl mb-2"></i>
                                <span class="text-white font-bold text-sm">Tải ảnh bìa lên</span>
                            </div>
                            <input type="file" id="coverInput" class="hidden" accept="image/*">
                        </div>
                        <input type="hidden" name="cover_image" id="coverVal" value="<?= e($restaurant['cover_image'] ?? '') ?>">
                        <div id="coverLoading" class="hidden mt-2 text-xs text-blue-500 font-bold animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i>Đang tải...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end pt-4 pb-10">
            <button type="submit" name="update_restaurant" class="btn-primary text-sm px-6 py-3">
                <i class="fas fa-save"></i> Lưu Thay Đổi
            </button>
        </div>
        
    </form>
</div>

<?php require_once 'layout_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function setupDragAndDrop(areaId, inputId, valId, imgId, loadingId) {
        const area = document.getElementById(areaId);
        const input = document.getElementById(inputId);
        const valElem = document.getElementById(valId);
        const loading = document.getElementById(loadingId);
        
        if(!area) return;

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
            formData.append('type', 'restaurant');
            loading.classList.remove('hidden');

            fetch('/foodbooking/api/merchant/upload_image.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                loading.classList.add('hidden');
                if(data.success) {
                    // Lưu path vào input để submit form
                    valElem.value = data.path;
                    
                    // Cập nhật preview với URL đầy đủ
                    let previewImg = document.getElementById(imgId);
                    if (previewImg && previewImg.tagName === 'IMG') {
                        previewImg.src = data.url;
                    } else {
                        // Replace placeholder div with image
                        const newImg = document.createElement('img');
                        newImg.src = data.url;
                        newImg.className = 'w-full h-full object-cover';
                        newImg.id = imgId;
                        area.innerHTML = '';
                        area.appendChild(newImg);
                    }
                    showToast('success', 'Tải ảnh thành công, vui lòng "Lưu Thay Đổi"');
                } else showToast('error', data.message);
            }).catch(() => { loading.classList.add('hidden'); showToast('error', 'Lỗi kết nối'); });
        }
    }

    setupDragAndDrop('logoArea', 'logoInput', 'logoVal', 'logoPreviewImg', 'logoLoading');
    setupDragAndDrop('coverArea', 'coverInput', 'coverVal', 'coverPreviewImg', 'coverLoading');
});
</script>
