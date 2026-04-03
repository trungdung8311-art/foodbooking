<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Cicafood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fcfcfc;
        }
        .bg-cica-red {
            background-color: #ee2624;
        }
        .text-cica-red {
            color: #ee2624;
        }
        .sidebar-active {
            background-color: #fff1f1;
            color: #ee2624;
            border-right: 4px solid #ee2624;
        }
    </style>
</head>
<body>

    <!-- Header tương tự trang chủ -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 overflow-hidden rounded-lg border border-gray-100">
                    <img src="image/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" alt="Cicafood Logo" class="w-full h-full object-cover">
                </div>
                <span class="text-xl font-black text-cica-red tracking-tighter">Cicafood</span>
            </div>
            <div class="flex items-center space-x-6 text-gray-600">
                <a href="index.php" class="hover:text-cica-red font-medium transition">Quay lại trang chủ</a>
                <div class="relative cursor-pointer">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-cica-red text-white text-[8px] rounded-full w-4 h-4 flex items-center justify-center">2</span>
                </div>
                <div class="flex items-center space-x-2 border-l pl-6">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100" class="w-8 h-8 rounded-full object-cover">
                    <span class="font-bold text-sm hidden md:block">Nguyễn Văn A</span>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sidebar điều hướng -->
            <aside class="w-full lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 text-center border-b border-gray-50">
                        <div class="relative inline-block mb-4">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200" class="w-24 h-24 rounded-full object-cover border-4 border-gray-50">
                            <button class="absolute bottom-0 right-0 bg-white shadow-md p-2 rounded-full text-cica-red hover:bg-gray-50">
                                <i class="fas fa-camera text-xs"></i>
                            </button>
                        </div>
                        <h2 class="font-bold text-lg text-gray-800">Nguyễn Văn A</h2>
                        <p class="text-xs text-gray-400">Thành viên Bạc</p>
                    </div>
                    <nav class="py-4">
                        <a href="#" class="sidebar-active flex items-center space-x-4 px-6 py-4 font-semibold transition">
                            <i class="far fa-user w-5"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                        <a href="#" class="flex items-center space-x-4 px-6 py-4 text-gray-500 hover:bg-gray-50 hover:text-cica-red font-semibold transition">
                            <i class="fas fa-history w-5"></i>
                            <span>Lịch sử đơn hàng</span>
                        </a>
                        <a href="#" class="flex items-center space-x-4 px-6 py-4 text-gray-500 hover:bg-gray-50 hover:text-cica-red font-semibold transition">
                            <i class="fas fa-map-marker-alt w-5"></i>
                            <span>Địa chỉ đã lưu</span>
                        </a>
                        <a href="#" class="flex items-center space-x-4 px-6 py-4 text-gray-500 hover:bg-gray-50 hover:text-cica-red font-semibold transition">
                            <i class="fas fa-wallet w-5"></i>
                            <span>Ví CicaPay</span>
                        </a>
                        <a href="#" class="flex items-center space-x-4 px-6 py-4 text-gray-500 hover:bg-gray-50 hover:text-cica-red font-semibold transition">
                            <i class="fas fa-shield-alt w-5"></i>
                            <span>Bảo mật</span>
                        </a>
                        <div class="border-t border-gray-50 mt-4 pt-4 px-6">
                            <a href="login.php" class="flex items-center space-x-4 py-2 text-red-500 font-bold hover:opacity-80 transition">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </nav>
                </div>
            </aside>

            <!-- Nội dung chính -->
            <section class="flex-1">
                <!-- Card Thông tin cá nhân -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-bold text-gray-800">Thông tin cá nhân</h3>
                        <button class="text-cica-red font-bold text-sm hover:underline">
                            <i class="far fa-edit mr-1"></i> Chỉnh sửa
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Họ và tên</label>
                            <p class="text-gray-800 font-semibold border-b border-gray-50 pb-2">Nguyễn Văn A</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Số điện thoại</label>
                            <p class="text-gray-800 font-semibold border-b border-gray-50 pb-2">0901 234 567</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Email</label>
                            <p class="text-gray-800 font-semibold border-b border-gray-50 pb-2">nguyenvana@gmail.com</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ngày sinh</label>
                            <p class="text-gray-800 font-semibold border-b border-gray-50 pb-2">01 / 01 / 1995</p>
                        </div>
                    </div>

                    <!-- Thống kê nhỏ -->
                    <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-2xl text-center">
                            <p class="text-2xl font-black text-cica-red">12</p>
                            <p class="text-xs text-gray-500 font-bold uppercase mt-1">Đơn hàng</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl text-center">
                            <p class="text-2xl font-black text-gray-800">450k</p>
                            <p class="text-xs text-gray-500 font-bold uppercase mt-1">Điểm tích lũy</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl text-center">
                            <p class="text-2xl font-black text-gray-800">5</p>
                            <p class="text-xs text-gray-500 font-bold uppercase mt-1">Voucher</p>
                        </div>
                    </div>
                </div>

                <!-- Đơn hàng gần đây -->
                <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Đơn hàng gần đây</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-50">
                                    <th class="pb-4">Mã đơn</th>
                                    <th class="pb-4">Nhà hàng</th>
                                    <th class="pb-4">Thời gian</th>
                                    <th class="pb-4 text-right">Tổng tiền</th>
                                    <th class="pb-4 text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    <td class="py-4 font-bold">#CICA-1029</td>
                                    <td class="py-4 font-medium">Bún Chả Cica Kitchen</td>
                                    <td class="py-4 text-gray-500">Hôm nay, 12:30</td>
                                    <td class="py-4 text-right font-bold text-gray-800">125.000đ</td>
                                    <td class="py-4 text-center">
                                        <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-[10px] font-bold">HOÀN THÀNH</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    <td class="py-4 font-bold">#CICA-0988</td>
                                    <td class="py-4 font-medium">Phở Gánh Hà Nội</td>
                                    <td class="py-4 text-gray-500">Hôm qua, 18:45</td>
                                    <td class="py-4 text-right font-bold text-gray-800">65.000đ</td>
                                    <td class="py-4 text-center">
                                        <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-[10px] font-bold">HOÀN THÀNH</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </main>

</body>
</html>