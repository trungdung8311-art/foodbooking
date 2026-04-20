<?php
// merchant/menu_categories.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_check.php';
requireMerchant();

$restaurant = getMerchantRestaurant($conn);
if (!$restaurant) { header('Location: /foodbooking/views/user/profile.php'); exit; }

$rid = $restaurant['id'];
$pageTitle = 'Danh mục Thực đơn';
$activePage = 'menu_categories';

// Xử lý Thêm / Sửa / Xóa
$flashToast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $sortOrders = (int)($_POST['sort_order'] ?? 0);
            if ($name) {
                $stmt = $conn->prepare("INSERT INTO menu_categories (restaurant_id, name, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$rid, $name, $sortOrders]);
                $flashToast = ['type' => 'success', 'message' => 'Thêm danh mục thành công!'];
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $sortOrders = (int)($_POST['sort_order'] ?? 0);
            if ($id && $name) {
                $stmt = $conn->prepare("UPDATE menu_categories SET name = ?, sort_order = ? WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$name, $sortOrders, $id, $rid]);
                $flashToast = ['type' => 'success', 'message' => 'Cập nhật danh mục thành công!'];
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['category_id'] ?? 0);
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM menu_categories WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$id, $rid]);
                $flashToast = ['type' => 'success', 'message' => 'Đã xóa danh mục!'];
            }
        }
    } catch (Exception $e) {
        $flashToast = ['type' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()];
    }
}

// Lấy danh sách categories
$stmt = $conn->prepare("SELECT * FROM menu_categories WHERE restaurant_id = ? ORDER BY sort_order ASC, id DESC");
$stmt->execute([$rid]);
$categories = $stmt->fetchAll();

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

require_once 'layout.php';
?>

<div class="merchant-card">
    <div class="merchant-card-header">
        <h2 class="font-black text-gray-800 text-sm flex items-center gap-2">
            <i class="fas fa-layer-group text-cica-red"></i> Danh mục hiện tại
        </h2>
        <button onclick="openModal('modal-add-cat')" class="btn-primary text-xs">
            <i class="fas fa-plus"></i> Thêm danh mục
        </button>
    </div>
    <div class="merchant-card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-10">
                <i class="fas fa-folder-open text-gray-200 text-5xl mb-3"></i>
                <p class="text-gray-400 text-sm">Chưa có danh mục nào.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="merchant-table">
                    <thead>
                        <tr>
                            <th>Tên danh mục</th>
                            <th>Thứ tự</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="font-bold text-gray-700"><?= e($cat['name']) ?></td>
                            <td><?= $cat['sort_order'] ?></td>
                            <td class="text-right">
                                <button onclick="editCat(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', <?= $cat['sort_order'] ?>)" class="btn-edit mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="inline-block" onsubmit="return confirmDelete(this, 'Xóa danh mục này sẽ xóa luôn các món ăn bên trong. Bạn có chắc chắn?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add -->
<div id="modal-add-cat" class="modal-overlay">
    <div class="modal-box">
        <h3 class="font-black text-lg text-gray-800 mb-4">Thêm danh mục mới</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Tên danh mục</label>
                <input type="text" name="name" class="form-input" required placeholder="Ví dụ: Món chính, Đồ uống...">
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự hiển thị (Số nhỏ xếp trước)</label>
                <input type="number" name="sort_order" class="form-input" value="0">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-add-cat')">Hủy</button>
                <button type="submit" class="btn-primary">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit-cat" class="modal-overlay">
    <div class="modal-box">
        <h3 class="font-black text-lg text-gray-800 mb-4">Chỉnh sửa danh mục</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="category_id" id="edit-cat-id">
            <div class="form-group">
                <label class="form-label">Tên danh mục</label>
                <input type="text" name="name" id="edit-cat-name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" id="edit-cat-sort" class="form-input">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-edit-cat')">Hủy</button>
                <button type="submit" class="btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCat(id, name, sortOrder) {
    document.getElementById('edit-cat-id').value = id;
    document.getElementById('edit-cat-name').value = name;
    document.getElementById('edit-cat-sort').value = sortOrder;
    openModal('modal-edit-cat');
}
</script>

<?php require_once 'layout_footer.php'; ?>
