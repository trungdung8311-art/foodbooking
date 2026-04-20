<?php
// ============================================================
// Cicafood - Functions Helper
// ============================================================

if (!function_exists('formatPrice')) {
function formatPrice(int $amount): string {
    return number_format($amount, 0, ',', '.') . 'đ';
}
}

if (!function_exists('fp')) {
function fp(int $amount): string {
    return number_format($amount, 0, ',', '.') . 'đ';
}
}

if (!function_exists('isLoggedIn')) {
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
}

if (!function_exists('requireLogin')) {
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: {$redirect}");
        exit;
    }
}
}

if (!function_exists('getCartCount')) {
function getCartCount(): int {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}
}

if (!function_exists('getCartSubtotal')) {
function getCartSubtotal(): int {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    $total = 0;

    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    return $total;
}
}

if (!function_exists('e')) {
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('makeSlug')) {
function makeSlug(string $str): string {

    $str = strtolower(trim($str));

    $vi = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
           'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
           'ì','í','ị','ỉ','ĩ',
           'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
           'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
           'ỳ','ý','ỵ','ỷ','ỹ','đ',' '];

    $en = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
           'e','e','e','e','e','e','e','e','e','e','e',
           'i','i','i','i','i',
           'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
           'u','u','u','u','u','u','u','u','u','u','u',
           'y','y','y','y','y','d','-'];

    $str = str_replace($vi, $en, $str);
    $str = preg_replace('/[^a-z0-9-]/', '', $str);
    $str = preg_replace('/-+/', '-', $str);

    return trim($str, '-');
}
}

if (!function_exists('generateOrderCode')) {
function generateOrderCode(): string {
    return 'CF' . strtoupper(substr(uniqid(), -6)) . rand(10,99);
}
}

if (!function_exists('getCurrentUser')) {
function getCurrentUser(): ?array {

    if (!isLoggedIn()) return null;

    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'] ?? 'Người dùng',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'customer',
        'avatar'    => $_SESSION['user_avatar'] ?? null
    ];
}
}

if (!function_exists('setFlash')) {
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}
}

if (!function_exists('getFlash')) {
function getFlash(): ?array {

    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}
}

if (!function_exists('getOrderStatusLabel')) {
function getOrderStatusLabel(string $status): array {

    $map = [
        'pending'    => ['label'=>'Chờ xác nhận','color'=>'yellow','icon'=>'fa-clock'],
        'confirmed'  => ['label'=>'Đã xác nhận','color'=>'blue','icon'=>'fa-check-circle'],
        'preparing'  => ['label'=>'Đang chuẩn bị','color'=>'orange','icon'=>'fa-fire-flame-curved'],
        'delivering' => ['label'=>'Đang giao','color'=>'purple','icon'=>'fa-motorcycle'],
        'completed'  => ['label'=>'Hoàn thành','color'=>'green','icon'=>'fa-circle-check'],
        'cancelled'  => ['label'=>'Đã huỷ','color'=>'red','icon'=>'fa-circle-xmark']
    ];

    return $map[$status] ?? [
        'label'=>$status,
        'color'=>'gray',
        'icon'=>'fa-question'
    ];
}
}

if (!function_exists('getStatusBadge')) {
function getStatusBadge(string $status): string {

    $map = [
        'pending'    => ['badge-pending','Chờ xác nhận'],
        'confirmed'  => ['badge-confirmed','Đã xác nhận'],
        'preparing'  => ['badge-preparing','Đang chuẩn bị'],
        'delivering' => ['badge-delivering','Đang giao'],
        'completed'  => ['badge-completed','Hoàn thành'],
        'cancelled'  => ['badge-cancelled','Đã huỷ']
    ];

    $cls = $map[$status][0] ?? 'badge-cancelled';
    $txt = $map[$status][1] ?? $status;

    return "<span class='badge {$cls}'>{$txt}</span>";
}
}

// ============================================================
// IMAGE HELPER FUNCTIONS - Đồng bộ đường dẫn ảnh
// ============================================================

if (!function_exists('getImageUrl')) {
/**
 * Chuyển đổi đường dẫn ảnh từ DB thành URL hiển thị đúng
 * @param string|null $path - Đường dẫn từ DB (vd: "image/uploads/img_xxx.jpg")
 * @param string $default - Ảnh mặc định nếu không có
 * @return string - URL đầy đủ để hiển thị
 */
function getImageUrl(?string $path, string $default = ''): string {
    // Nếu không có path, trả về default
    if (empty($path)) {
        return $default ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
    }
    
    // Nếu đã là URL đầy đủ (http/https), trả về luôn
    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }
    
    // Nếu path bắt đầu bằng "image/uploads/" hoặc "uploads/"
    // Chuẩn hóa về dạng tương đối từ root
    $path = ltrim($path, '/');
    
    // Nếu đang ở trong thư mục con (merchant/, api/), cần thêm ../
    $currentDir = dirname($_SERVER['SCRIPT_NAME']);
    if ($currentDir !== '/' && $currentDir !== '') {
        // Đếm số cấp thư mục để thêm ../
        $levels = substr_count(trim($currentDir, '/'), '/') + 1;
        $prefix = str_repeat('../', $levels);
        return $prefix . $path;
    }
    
    // Nếu ở root, trả về path trực tiếp
    return $path;
}
}

if (!function_exists('getAvatarUrl')) {
/**
 * Lấy URL avatar người dùng
 * @param string|null $avatar - Đường dẫn avatar từ DB
 * @return string - URL avatar hoặc default
 */
function getAvatarUrl(?string $avatar): string {
    if (empty($avatar) || $avatar === 'default_avatar.png') {
        return 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') . '&background=ee2624&color=fff&bold=true';
    }
    return getImageUrl($avatar);
}
}

if (!function_exists('uploadImage')) {
/**
 * Upload ảnh và trả về đường dẫn để lưu vào DB
 * @param array $file - $_FILES['field_name']
 * @return array - ['success' => bool, 'path' => string, 'message' => string]
 */
function uploadImage(array $file): array {
    // Validate
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Không có file hoặc có lỗi khi tải lên'];
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Chỉ hỗ trợ file ảnh (JPG, PNG, WEBP, GIF)'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Dung lượng file tối đa là 5MB'];
    }
    
    // Tạo thư mục nếu chưa có
    $uploadDir = __DIR__ . '/../image/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . uniqid() . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Trả về đường dẫn tương đối để lưu vào DB
        return [
            'success' => true,
            'path' => 'image/uploads/' . $filename,
            'message' => 'Tải ảnh thành công!'
        ];
    }
    
    return ['success' => false, 'message' => 'Lỗi khi lưu file vào máy chủ.'];
}
}
?>