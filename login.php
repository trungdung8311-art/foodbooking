<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Cicafood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-cica-red {
            background-color: #ee2624;
        }
        .text-cica-red {
            color: #ee2624;
        }
        .login-gradient {
            background: linear-gradient(135deg, #ee2624 0%, #b91c1c 100%);
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-5xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <!-- Cột trái: Hình ảnh & Chào mừng (Chỉ hiện trên desktop) -->
        <div class="hidden md:flex md:w-1/2 login-gradient p-12 text-white flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-12">
                    <div class="w-12 h-12 overflow-hidden rounded-xl bg-white p-1">
                        <img src="image/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" alt="Cicafood Logo" class="w-full h-full object-cover">
                    </div>
                    <span class="text-3xl font-black tracking-tighter">Cicafood</span>
                </div>
                <h1 class="text-5xl font-extrabold leading-tight mb-6">Mừng bạn <br>trở lại!</h1>
                <p class="text-lg opacity-90 max-w-sm">Đăng nhập để tiếp tục khám phá những món ăn ngon nhất quanh bạn và nhận ưu đãi độc quyền.</p>
            </div>
            
            <div class="relative z-10 flex items-center space-x-4">
                <div class="flex -space-x-3">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100" alt="User">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100" alt="User">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100" alt="User">
                </div>
                <p class="text-sm font-medium italic">+10k người đang đặt hàng hôm nay</p>
            </div>

            <!-- Decor elements -->
            <i class="fas fa-hamburger absolute -bottom-10 -left-10 text-[250px] opacity-10 -rotate-12"></i>
        </div>

        <!-- Cột phải: Form đăng nhập -->
        <div class="w-full md:w-1/2 p-8 md:p-16">
            <!-- Logo Mobile -->
            <div class="md:hidden flex flex-col items-center mb-8">
                <div class="w-16 h-16 overflow-hidden rounded-2xl shadow-lg mb-3">
                    <img src="image/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" alt="Cicafood Logo" class="w-full h-full object-cover">
                </div>
                <h2 class="text-2xl font-black text-cica-red tracking-tighter">Cicafood</h2>
            </div>

            <div class="mb-10 text-center md:text-left">
                <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Đăng Nhập</h2>
                <p class="text-gray-500 font-medium">Vui lòng nhập thông tin tài khoản của bạn</p>
            </div>

            <form class="space-y-6">
                <!-- Tài khoản -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Email hoặc Số điện thoại</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="far fa-user"></i>
                        </span>
                        <input type="text" placeholder="username@gmail.com" 
                            class="w-full pl-12 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-cica-red focus:ring-1 focus:ring-cica-red transition-all">
                    </div>
                </div>

                <!-- Mật khẩu -->
                <div>
                    <div class="flex justify-between mb-2">
                        <label class="text-sm font-bold text-gray-700 uppercase tracking-wide">Mật khẩu</label>
                        <a href="#" class="text-xs font-bold text-cica-red hover:underline">Quên mật khẩu?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" placeholder="••••••••" 
                            class="w-full pl-12 pr-12 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-cica-red focus:ring-1 focus:ring-cica-red transition-all">
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 cursor-pointer hover:text-gray-600">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Remember me -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" class="w-4 h-4 text-cica-red border-gray-300 rounded focus:ring-cica-red">
                    <label for="remember" class="ml-2 text-sm text-gray-600 font-medium">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Button Đăng nhập -->
                <button type="submit" class="w-full bg-cica-red text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-red-200 hover:bg-red-700 transition-all active:scale-[0.98]">
                    ĐĂNG NHẬP
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-400 font-medium italic">Hoặc đăng nhập bằng</span>
                </div>
            </div>

            <!-- Social Login -->
            <div class="grid grid-cols-2 gap-4">
                <button class="flex items-center justify-center py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition font-bold text-sm text-gray-700">
                    <img src="image/google.png" class="w-5 h-5 mr-2" alt="Google"> Google
                </button>
                <button class="flex items-center justify-center py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition font-bold text-sm text-gray-700">
                    <i class="fab fa-facebook text-blue-600 text-xl mr-2"></i> Facebook
                </button>
            </div>

            <!-- Footer Link -->
            <p class="mt-10 text-center text-gray-600 font-medium">
                Chưa có tài khoản? 
                <a href="#" class="text-cica-red font-bold hover:underline">Đăng ký ngay</a>
            </p>
        </div>
    </div>

</body>
</html>