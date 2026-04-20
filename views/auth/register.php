<?php
// views/auth/register.php - Trang đăng ký tài khoản
require_once __DIR__ . '/../../config/database.php';

if (isLoggedIn()) { header('Location: /foodbooking/'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim(strtolower($_POST['email'] ?? ''));
    $phone     = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $agree     = isset($_POST['agree']);

    // Validation
    if (empty($fullName) || mb_strlen($fullName) < 2) $errors['full_name'] = 'Vui lòng nhập họ tên (tối thiểu 2 ký tự)';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
    if (strlen($phone) < 9 || strlen($phone) > 11) $errors['phone'] = 'Số điện thoại không hợp lệ (9-11 chữ số)';
    if (strlen($password) < 6) $errors['password'] = 'Mật khẩu tối thiểu 6 ký tự';
    if ($password !== $confirm) $errors['confirm'] = 'Mật khẩu xác nhận không khớp';
    if (!$agree) $errors['agree'] = 'Vui lòng đồng ý với điều khoản sử dụng';

    if (empty($errors)) {
        // Kiểm tra email đã tồn tại
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $stmtCheck->execute([$email, $phone]);
        if ($stmtCheck->fetch()) {
            $errors['email'] = 'Email hoặc số điện thoại đã được đăng ký';
        } else {
            // Tạo tài khoản mới
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmtInsert = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$fullName, $email, $phone, $hash]);
            $newUserId = $conn->lastInsertId();

            // Auto login
            $_SESSION['user_id']    = $newUserId;
            $_SESSION['user_name']  = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role']  = 'customer';

            setFlash('success', "Chào mừng {$fullName} đến với Cicafood! Hãy dùng mã CICA20 để nhận giảm giá 20% nhé!");
            header('Location: /foodbooking/');
            exit;
        }
    }
}

$pageTitle = 'Đăng Ký - Cicafood';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .bg-cica-red { background-color: #ee2624; }
        .text-cica-red { color: #ee2624; }
        .login-gradient { background: linear-gradient(135deg, #1a0a00 0%, #2d0e0e 40%, #ee2624 100%); }
        .input-field {
            width: 100%; padding: 13px 16px 13px 46px;
            background: #f9fafb; border: 1.5px solid #e5e7eb;
            border-radius: 14px; outline: none; font-size: 14px; color: #374151;
            transition: all 0.2s ease;
        }
        .input-field:focus { background: #fff; border-color: #ee2624; box-shadow: 0 0 0 4px rgba(238,38,36,0.08); }
        .input-field.error { border-color: #ef4444; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="max-w-5xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
    
    <!-- Left Panel -->
    <div class="hidden md:flex md:w-5/12 login-gradient p-12 flex-col justify-between relative overflow-hidden">
        <div class="relative z-10">
            <a href="/foodbooking/" class="flex items-center gap-3 mb-14">
                <div class="w-12 h-12 rounded-2xl overflow-hidden bg-white p-1.5">
                    <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" class="w-full h-full object-cover rounded-xl">
                </div>
                <span class="text-3xl font-black text-white tracking-tight">Cicafood</span>
            </a>
            <h1 class="text-5xl font-black text-white leading-tight mb-4">Tham gia<br>Cicafood! 🚀</h1>
            <p class="text-white/70 text-base leading-relaxed mb-8">Đăng ký miễn phí và nhận voucher chào mừng <strong class="text-yellow-300">GIẢM 20%</strong> cho đơn hàng đầu tiên!</p>
            
            <!-- Benefits -->
            <div class="space-y-3">
                <div class="flex items-center gap-3 text-white/90">
                    <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-green-300 text-xs"></i>
                    </div>
                    <span class="text-sm">Voucher CICA20 - Giảm 20% đơn đầu</span>
                </div>
                <div class="flex items-center gap-3 text-white/90">
                    <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-green-300 text-xs"></i>
                    </div>
                    <span class="text-sm">Freeship toàn bộ đơn đầu tiên</span>
                </div>
                <div class="flex items-center gap-3 text-white/90">
                    <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-green-300 text-xs"></i>
                    </div>
                    <span class="text-sm">Tich lũy điểm thưởng CicaPoint</span>
                </div>
                <div class="flex items-center gap-3 text-white/90">
                    <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-green-300 text-xs"></i>
                    </div>
                    <span class="text-sm">Ưu đãi thành viên độc quyền mỗi ngày</span>
                </div>
            </div>
        </div>
        <div class="relative z-10">
            <p class="text-white/60 text-sm text-center">Đã có tài khoản? 
                <a href="/foodbooking/views/auth/login.php" class="text-yellow-300 font-bold hover:underline">Đăng nhập ngay</a>
            </p>
        </div>
        <i class="fas fa-utensils absolute -bottom-8 -right-6 text-[200px] text-white/5 rotate-12"></i>
    </div>

    <!-- Right Panel: Form -->
    <div class="flex-1 p-8 md:p-10 overflow-y-auto" style="max-height:100vh;">
        <!-- Mobile Logo -->
        <div class="md:hidden text-center mb-6">
            <a href="/foodbooking/" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 rounded-2xl overflow-hidden shadow">
                    <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" class="w-full h-full object-cover">
                </div>
                <span class="text-2xl font-black text-cica-red">Cicafood</span>
            </a>
        </div>

        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900 mb-1">Tạo Tài Khoản</h2>
            <p class="text-gray-500 text-sm">Chỉ mất 30 giây để bắt đầu!</p>
        </div>

        <form method="POST" class="space-y-4" id="register-form">
            <!-- Full Name -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Họ và Tên <span class="text-cica-red">*</span></label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>"
                           placeholder="Nguyễn Văn A"
                           class="input-field <?= isset($errors['full_name']) ? 'error' : '' ?>">
                </div>
                <?php if (isset($errors['full_name'])): ?>
                <p class="text-red-500 text-xs mt-1"><i class="fas fa-circle-exclamation mr-1"></i><?= e($errors['full_name']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email <span class="text-cica-red">*</span></label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="example@gmail.com"
                           class="input-field <?= isset($errors['email']) ? 'error' : '' ?>">
                </div>
                <?php if (isset($errors['email'])): ?>
                <p class="text-red-500 text-xs mt-1"><i class="fas fa-circle-exclamation mr-1"></i><?= e($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Số Điện Thoại <span class="text-cica-red">*</span></label>
                <div class="relative">
                    <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"
                           placeholder="0912 345 678"
                           class="input-field <?= isset($errors['phone']) ? 'error' : '' ?>">
                </div>
                <?php if (isset($errors['phone'])): ?>
                <p class="text-red-500 text-xs mt-1"><i class="fas fa-circle-exclamation mr-1"></i><?= e($errors['phone']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password + Confirm (grid) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Mật Khẩu <span class="text-cica-red">*</span></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="password" placeholder="••••••"
                               class="input-field <?= isset($errors['password']) ? 'error' : '' ?>">
                    </div>
                    <?php if (isset($errors['password'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= e($errors['password']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Xác Nhận <span class="text-cica-red">*</span></label>
                    <div class="relative">
                        <i class="fas fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="confirm_password" placeholder="••••••"
                               class="input-field <?= isset($errors['confirm']) ? 'error' : '' ?>">
                    </div>
                    <?php if (isset($errors['confirm'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= e($errors['confirm']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Password strength -->
            <div>
                <div class="flex gap-1.5" id="strength-bars">
                    <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar1"></div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar2"></div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar3"></div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar4"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1" id="strength-text">Nhập mật khẩu để kiểm tra độ mạnh</p>
            </div>

            <!-- Agree -->
            <div class="flex items-start gap-3">
                <input type="checkbox" name="agree" id="agree" class="mt-0.5 w-4 h-4 rounded border-gray-300 accent-red-600 cursor-pointer flex-shrink-0">
                <label for="agree" class="text-sm text-gray-600 cursor-pointer leading-relaxed">
                    Tôi đồng ý với <a href="#" class="text-cica-red font-semibold hover:underline">Điều khoản dịch vụ</a> và 
                    <a href="#" class="text-cica-red font-semibold hover:underline">Chính sách bảo mật</a> của Cicafood
                </label>
            </div>
            <?php if (isset($errors['agree'])): ?>
            <p class="text-red-500 text-xs -mt-2"><i class="fas fa-circle-exclamation mr-1"></i><?= e($errors['agree']) ?></p>
            <?php endif; ?>

            <!-- Submit -->
            <button type="submit" class="w-full bg-cica-red text-white py-4 rounded-2xl font-black text-base shadow-lg shadow-red-100 hover:bg-red-700 transition-all active:scale-[0.98] mt-2">
                <i class="fas fa-rocket mr-2"></i>ĐĂNG KÝ MIỄN PHÍ
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-6">
            Đã có tài khoản? 
            <a href="/foodbooking/views/auth/login.php" class="text-cica-red font-bold hover:underline">Đăng nhập ngay</a>
        </p>
    </div>
</div>

<script>
// Password strength
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) || /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
    const labels = ['', 'Yếu', 'Trung bình', 'Khá mạnh', 'Rất mạnh'];
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('bar' + i);
        bar.style.background = i <= score ? colors[score] : '#e5e7eb';
    }
    document.getElementById('strength-text').textContent = score > 0 ? `Độ mạnh: ${labels[score]}` : 'Nhập mật khẩu để kiểm tra độ mạnh';
    document.getElementById('strength-text').style.color = score > 0 ? colors[score] : '#9ca3af';
});
</script>
</body>
</html>