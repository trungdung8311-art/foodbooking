<?php
// api/merchant/upload_image.php - Upload ảnh cho merchant (restaurant, menu items)
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không có file hoặc có lỗi khi tải lên']);
    exit;
}

$file = $_FILES['image'];
$type = $_POST['type'] ?? 'restaurant'; // restaurant, menu_item, review

// Validate type
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ file ảnh (JPG, PNG, WEBP, GIF)']);
    exit;
}

// Validate size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Dung lượng file tối đa là 5MB']);
    exit;
}

// Xác định thư mục upload dựa vào type
$subDir = match($type) {
    'restaurant' => 'restaurants/',
    'menu_item' => 'menu_items/',
    'review' => 'reviews/',
    default => 'restaurants/'
};

// Tạo thư mục nếu chưa có
$uploadDir = __DIR__ . '/../../public/uploads/' . $subDir;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'img_' . uniqid() . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Trả về đường dẫn tương đối để lưu vào database
    $dbPath = 'public/uploads/' . $subDir . $filename;
    echo json_encode([
        'success' => true,
        'message' => 'Tải ảnh thành công!',
        'path' => $dbPath,
        'url' => '/foodbooking/' . $dbPath
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file vào máy chủ.']);
}
