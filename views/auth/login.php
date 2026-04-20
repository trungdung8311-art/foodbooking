<?php
// views/auth/login.php - Trang đăng nhập với backend thực sự
require_once __DIR__ . '/../../config/database.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    header('Location: /foodbooking/');
    exit;
}

$errors = [];
$successMsg = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Vui lòng nhập email hoặc số điện thoại';
    }
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu';
    }

    if (empty($errors)) {
        // Tìm user theo email hoặc phone
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Đăng nhập thành công
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];

            // Remember me
            if ($remember) {
                setcookie('cicafood_remember', base64_encode($user['id'] . ':' . md5($user['password_hash'])), time() + 30 * 24 * 3600, '/');
            }

            // Redirect về trang trước đó hoặc homepage
            $redirect = $_SESSION['redirect_after_login'] ?? '/foodbooking/';
            unset($_SESSION['redirect_after_login']);

            setFlash('success', "Chào mừng trở lại, {$user['full_name']}!");
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors['general'] = 'Email/SĐT hoặc mật khẩu không đúng';
        }
    }
}

$pageTitle = 'Đăng Nhập - Cicafood';
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
        .border-cica-red { border-color: #ee2624; }
        .focus\:border-cica-red:focus { border-color: #ee2624; }
        .focus\:ring-cica-red:focus { --tw-ring-color: rgba(238,38,36,0.2); }
        .login-gradient { background: linear-gradient(135deg, #1a0a00 0%, #2d0e0e 40%, #ee2624 100%); }
        .input-field {
            width: 100%; padding: 14px 16px 14px 48px;
            background: #f9fafb; border: 1.5px solid #e5e7eb;
            border-radius: 14px; outline: none; font-size: 15px; color: #374151;
            transition: all 0.2s ease;
        }
        .input-field:focus { background: #fff; border-color: #ee2624; box-shadow: 0 0 0 4px rgba(238,38,36,0.1); }
        .input-field.error { border-color: #ef4444; background: #fff5f5; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="max-w-5xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
    
    <div class="hidden md:flex md:w-5/12 login-gradient p-12 flex-col justify-between relative overflow-hidden">
        <div class="relative z-10">
            <a href="/foodbooking/" class="flex items-center gap-3 mb-14">
                <div class="w-12 h-12 rounded-2xl overflow-hidden bg-white p-1.5">
                    <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" alt="Cicafood Logo" class="w-full h-full object-cover rounded-xl">
                </div>
                <span class="text-3xl font-black text-white tracking-tight">Cicafood</span>
            </a>
            <h1 class="text-5xl font-black text-white leading-tight mb-4">Mừng bạn<br>trở lại! 👋</h1>
            <p class="text-white/70 text-base leading-relaxed">Đăng nhập để khám phá những món ăn ngon nhất quanh bạn và nhận ưu đãi độc quyền mỗi ngày.</p>
        </div>

        <!-- Deco -->
        <i class="fas fa-hamburger absolute -bottom-8 -left-8 text-[250px] text-white/5 -rotate-12"></i>
    </div>

    <!-- Right Panel: Form -->
    <div class="flex-1 p-8 md:p-12 flex flex-col justify-center">
        <div class="md:hidden text-center mb-8">
            <a href="/foodbooking/" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 rounded-2xl overflow-hidden shadow">
                    <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" class="w-full h-full object-cover">
                </div>
                <span class="text-2xl font-black text-cica-red">Cicafood</span>
            </a>
        </div>

        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900 mb-2">Đăng Nhập</h2>
            <p class="text-gray-500">Vui lòng nhập thông tin tài khoản của bạn</p>
        </div>

        <!-- General Error -->
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
            <i class="fas fa-triangle-exclamation text-red-500"></i>
            <p class="text-red-700 text-sm font-medium"><?= e($errors['general']) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <!-- Email -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email hoặc Số điện thoại</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="email" id="email"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="example@gmail.com hoặc 09xx..."
                           class="input-field <?= isset($errors['email']) ? 'error' : '' ?>">
                </div>
                <?php if (isset($errors['email'])): ?>
                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-circle-exclamation"></i> <?= e($errors['email']) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-bold text-gray-700">Mật khẩu</label>
                    <button type="button" onclick="showForgotPassword()" class="text-xs font-semibold text-cica-red hover:underline">Quên mật khẩu?</button>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••"
                           class="input-field pr-12 <?= isset($errors['password']) ? 'error' : '' ?>">
                    <button type="button" onclick="togglePassword()" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="far fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                    <i class="fas fa-circle-exclamation"></i> <?= e($errors['password']) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Remember -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="remember" id="remember" 
                       class="w-4 h-4 rounded border-gray-300 accent-red-600 cursor-pointer">
                <label for="remember" class="text-sm text-gray-600 cursor-pointer">Ghi nhớ đăng nhập trong 30 ngày</label>
            </div>

            <!-- Submit -->
            <button type="submit" id="login-btn"
                    class="w-full bg-cica-red text-white py-4 rounded-2xl font-black text-base shadow-lg shadow-red-100 hover:bg-red-700 transition-all active:scale-[0.98]">
                ĐĂNG NHẬP
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
        </div>

        <!-- Register link -->
        <p class="text-center text-gray-500 text-sm mt-8">
            Chưa có tài khoản? 
            <a href="/foodbooking/views/auth/register.php" class="text-cica-red font-bold hover:underline">Đăng ký miễn phí ngay!</a>
        </p>

    </div>
    
    <!-- Forgot Password Modal (2-Step Real Reset) -->
    <div id="forgot-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full mx-4 transform scale-95 transition-transform duration-300" id="forgot-modal-content">

            <!-- STEP 1: Xác minh Email -->
            <div id="forgot-step-1">
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-lock-open text-cica-red text-2xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2 text-center">Quên Mật Khẩu?</h2>
                <p class="text-sm text-gray-500 mb-6 text-center">Nhập Email đã đăng ký, chúng tôi sẽ xác minh ngay tức thì.</p>

                <div id="forgot-step1-error" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-sm text-red-700 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span id="forgot-step1-error-msg"></span>
                </div>

                <div class="relative mb-5">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="forgot-email" placeholder="email@example.com" autocomplete="email"
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm outline-none focus:border-cica-red focus:bg-white transition">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="hideForgotPassword()"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">Đóng</button>
                    <button type="button" onclick="verifyEmail()"
                            id="verify-btn"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white bg-cica-red shadow-lg shadow-red-200 hover:bg-red-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-right"></i> Xác minh
                    </button>
                </div>
            </div>

            <!-- STEP 2: Đặt mật khẩu mới -->
            <div id="forgot-step-2" class="hidden">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-1 text-center">Đặt Mật Khẩu Mới</h2>
                <p class="text-sm text-gray-400 mb-5 text-center" id="reset-email-display"></p>

                <div id="forgot-step2-error" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-sm text-red-700 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span id="forgot-step2-error-msg"></span>
                </div>

                <div class="space-y-4 mb-5">
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="new-password" placeholder="Mật khẩu mới (ít nhất 6 ký tự)"
                               class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm outline-none focus:border-cica-red focus:bg-white transition">
                        <button type="button" onclick="togglePass('new-password','eye-new')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye-new" class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock-open absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="confirm-password" placeholder="Xác nhận mật khẩu mới"
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm outline-none focus:border-cica-red focus:bg-white transition">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="backToStep1()"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <button type="button" onclick="resetPassword()"
                            id="reset-btn"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white bg-cica-red shadow-lg shadow-red-200 hover:bg-red-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Lưu mật khẩu
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-3"></div>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'far fa-eye' : 'far fa-eye-slash';
}
function togglePass(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const ico = document.getElementById(iconId);
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    if (ico) ico.className = inp.type === 'password' ? 'far fa-eye' : 'far fa-eye-slash';
}

function showForgotPassword() {
    const modal = document.getElementById('forgot-modal');
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    document.getElementById('forgot-modal-content').classList.remove('scale-95');
    // Reset to step 1
    showStep(1);
    document.getElementById('forgot-email').value = '';
    clearError(1); clearError(2);
}
function hideForgotPassword() {
    const modal = document.getElementById('forgot-modal');
    modal.classList.add('opacity-0');
    document.getElementById('forgot-modal-content').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
function showStep(n) {
    document.getElementById('forgot-step-1').classList.toggle('hidden', n !== 1);
    document.getElementById('forgot-step-2').classList.toggle('hidden', n !== 2);
}
function backToStep1() { showStep(1); clearError(2); }
function showError(step, msg) {
    const err = document.getElementById('forgot-step' + step + '-error');
    const txt = document.getElementById('forgot-step' + step + '-error-msg');
    err.classList.remove('hidden');
    txt.textContent = msg;
}
function clearError(step) {
    document.getElementById('forgot-step' + step + '-error')?.classList.add('hidden');
}

// Lưu email & token tạm cho step 2
let _resetEmail = '';
let _resetToken = '';

// STEP 1: Xác minh email
function verifyEmail() {
    const email = document.getElementById('forgot-email').value.trim();
    clearError(1);
    if (!email) { showError(1, 'Vui lòng nhập email.'); return; }

    const btn = document.getElementById('verify-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác minh...';

    fetch('/foodbooking/api/auth/reset_password.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({step: 'verify', email})
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-right"></i> Xác minh';
        if (data.success) {
            _resetEmail = data.email;
            _resetToken = data.token;
            document.getElementById('reset-email-display').textContent = '📧 ' + data.email;
            showStep(2);
            showToast('success', data.message);
        } else {
            showError(1, data.message);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-right"></i> Xác minh';
        showError(1, 'Lỗi kết nối. Vui lòng thử lại.');
    });
}

// STEP 2: Đặt lại mật khẩu
function resetPassword() {
    const newPass  = document.getElementById('new-password').value;
    const confirm  = document.getElementById('confirm-password').value;
    clearError(2);

    if (!newPass || newPass.length < 6) { showError(2, 'Mật khẩu phải có ít nhất 6 ký tự.'); return; }
    if (newPass !== confirm)            { showError(2, 'Mật khẩu xác nhận không khớp.'); return; }

    const btn = document.getElementById('reset-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    fetch('/foodbooking/api/auth/reset_password.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({step: 'reset', email: _resetEmail, token: _resetToken, new_password: newPass, confirm_password: confirm})
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Lưu mật khẩu';
        if (data.success) {
            showToast('success', '🎉 ' + data.message);
            hideForgotPassword();
            // Tự điền email vào form đăng nhập
            const emailInput = document.getElementById('email');
            if (emailInput) emailInput.value = _resetEmail;
            _resetEmail = ''; _resetToken = '';
        } else {
            showError(2, data.message);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Lưu mật khẩu';
        showError(2, 'Lỗi kết nối. Vui lòng thử lại.');
    });
}

// Cho phép Enter submit trong modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !document.getElementById('forgot-modal').classList.contains('hidden')) {
        if (!document.getElementById('forgot-step-1').classList.contains('hidden')) verifyEmail();
        else resetPassword();
    }
});

function showToast(type, msg) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const bg   = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl text-white font-medium text-sm shadow-xl ${bg} transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
</script>
</body>
</html>

