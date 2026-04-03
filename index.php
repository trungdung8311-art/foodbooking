<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cicafood - Đặt Đồ Ăn Trực Tuyến</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .bg-cica-red {
            background-color: #ee2624;
        }
        .text-cica-red {
            color: #ee2624;
        }
        .hero-bg {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 overflow-hidden rounded-xl shadow-sm border border-gray-100">
                    <!-- Sử dụng file ảnh logo người dùng cung cấp -->
                    <img src="image/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" alt="Cicafood Logo" class="w-full h-full object-cover">
                </div>
                <span class="text-2xl font-black text-cica-red tracking-tighter">Cicafood</span>
            </div>

            <!-- Menu Điều Hướng -->
            <nav class="hidden md:flex space-x-8 font-semibold text-gray-600">
                <a href="#" class="hover:text-cica-red transition">Trang chủ</a>
                <a href="#" class="hover:text-cica-red transition">Nhà hàng</a>
                <a href="#" class="hover:text-cica-red transition">Ưu đãi</a>
                <a href="#" class="hover:text-cica-red transition">Tài khoản</a>
            </nav>

            <!-- Giỏ hàng & Mobile Menu -->
            <div class="flex items-center space-x-6">
                <div class="relative group cursor-pointer">
                    <i class="fas fa-shopping-basket text-2xl text-gray-700 group-hover:text-cica-red transition"></i>
                    <span class="absolute -top-2 -right-2 bg-cica-red text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white">0</span>
                </div>
                <button class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section / Tìm kiếm -->
    <section class="hero-bg h-[450px] flex items-center justify-center text-center px-4">
        <div class="max-w-4xl w-full">
            <h1 class="text-white text-4xl md:text-6xl font-extrabold mb-8 drop-shadow-lg">Thèm gì là có, Cicafood giao ngay!</h1>
            <div class="bg-white p-2 md:p-3 rounded-2xl md:rounded-full flex flex-col md:flex-row items-center shadow-2xl space-y-2 md:space-y-0">
                <div class="flex-1 w-full px-6 flex items-center border-b md:border-b-0 md:border-r border-gray-100">
                    <i class="fas fa-search text-gray-400 mr-3"></i>
                    <input type="text" placeholder="Tìm món ngon, quán quen ngay..." class="w-full py-3 outline-none text-gray-700 text-lg">
                </div>
                <div class="flex-1 w-full px-6 flex items-center hidden md:flex">
                    <i class="fas fa-map-marker-alt text-cica-red mr-3"></i>
                    <input type="text" placeholder="Nhập địa chỉ giao hàng..." class="w-full py-3 outline-none text-gray-700">
                </div>
                <button class="w-full md:w-auto bg-cica-red text-white px-10 py-4 rounded-xl md:rounded-full font-bold text-lg hover:bg-red-700 transition-all shadow-lg active:scale-95">
                    TÌM KIẾM
                </button>
            </div>
        </div>
    </section>

    <!-- Nội dung chính -->
    <main class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Cột trái: Danh sách nhà hàng & Món ăn -->
            <div class="lg:col-span-8">
                
                <!-- Nhà hàng nổi bật -->
                <section class="mb-16">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-extrabold text-gray-800">Quán Ngon Nổi Bật</h2>
                        <a href="#" class="text-cica-red font-bold flex items-center hover:underline">
                            Xem tất cả <i class="fas fa-chevron-right ml-2 text-sm"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php
                        $restaurants = [
                            ['name' => 'Phở Gánh Hà Nội', 'rating' => 4.9, 'time' => '12 phút', 'img' => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43'],
                            ['name' => 'Bún Chả Cica Kitchen', 'rating' => 4.8, 'time' => '18 phút', 'img' => 'https://images.unsplash.com/photo-1541529086526-db283c563270'],
                            ['name' => 'Burger Bò Nướng', 'rating' => 4.6, 'time' => '22 phút', 'img' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd'],
                            ['name' => 'Sushi Tươi Sống', 'rating' => 4.9, 'time' => '25 phút', 'img' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c'],
                        ];

                        foreach ($restaurants as $res): ?>
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all card-hover border border-gray-100 group cursor-pointer">
                            <div class="relative h-52 overflow-hidden">
                                <img src="<?= $res['img'] ?>?auto=format&fit=crop&w=800&q=80" alt="<?= $res['name'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[10px] font-black text-cica-red uppercase tracking-wider shadow">Cicafood</div>
                                <div class="absolute bottom-4 right-4 bg-cica-red text-white px-3 py-1 rounded-lg text-xs font-bold shadow-lg">Freeship</div>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-xl text-gray-800 mb-2"><?= $res['name'] ?></h3>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-3">
                                        <span class="flex items-center text-yellow-500 font-bold">
                                            <i class="fas fa-star mr-1"></i> <?= $res['rating'] ?>
                                        </span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-gray-600 font-medium"><i class="far fa-clock mr-1"></i> <?= $res['time'] ?></span>
                                    </div>
                                    <span class="text-gray-400 text-xs">2.5 km</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Món ăn thịnh hành -->
                <section>
                    <h2 class="text-3xl font-extrabold text-gray-800 mb-8">Món Ăn Thịnh Hành</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                        <?php
                        $dishes = [
                            ['name' => 'Phở Bò Đặc Biệt', 'price' => '65.000đ', 'img' => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43'],
                            ['name' => 'Cơm Tấm Sườn Bì', 'price' => '45.000đ', 'img' => 'https://images.unsplash.com/photo-1621252179027-94459d278660'],
                            ['name' => 'Bún Đậu Mắm Tôm', 'price' => '55.000đ', 'img' => 'https://images.unsplash.com/photo-1623341214825-9f4f963727da'],
                            ['name' => 'Mì Quảng Tôm Thịt', 'price' => '40.000đ', 'img' => 'https://images.unsplash.com/photo-1604152135912-04a022e23696'],
                        ];
                        foreach ($dishes as $dish): ?>
                        <div class="bg-white p-4 rounded-2xl border border-gray-100 hover:shadow-md transition-all text-center group cursor-pointer">
                            <div class="relative overflow-hidden rounded-xl mb-4">
                                <img src="<?= $dish['img'] ?>?auto=format&fit=crop&w=400&q=80" class="w-full h-36 object-cover group-hover:scale-110 transition duration-500">
                            </div>
                            <h4 class="font-bold text-gray-800 text-base mb-1 truncate"><?= $dish['name'] ?></h4>
                            <p class="text-cica-red font-black text-lg"><?= $dish['price'] ?></p>
                            <button class="mt-3 w-full border border-cica-red text-cica-red py-2 rounded-lg text-sm font-bold hover:bg-cica-red hover:text-white transition">Thêm</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Cột phải: Bản đồ & Ưu đãi -->
            <div class="lg:col-span-4 space-y-10">
                
                <!-- Bản đồ gần bạn -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-gray-800 text-xl">Gần Bạn</h3>
                        <i class="fas fa-location-arrow text-cica-red"></i>
                    </div>
                    <div class="bg-gray-100 h-72 rounded-2xl relative overflow-hidden group">
                        <!-- Placeholder ảnh bản đồ -->
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-1000">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-cica-red animate-ping rounded-full opacity-20"></div>
                                <i class="fas fa-map-marker-alt text-cica-red text-5xl relative z-10 drop-shadow-lg"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-500 text-center font-medium italic">Đang hiển thị 245 cửa hàng quanh vị trí của bạn</p>
                </div>

                <!-- Banner Khuyến Mãi -->
                <div class="space-y-6">
                    <h3 class="font-extrabold text-gray-800 text-xl">Ưu Đãi Đặc Biệt</h3>
                    
                    <div class="bg-gradient-to-br from-red-600 to-red-500 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
                        <div class="relative z-10">
                            <span class="bg-white/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Chào bạn mới</span>
                            <h4 class="text-4xl font-black my-4">GIẢM 20%</h4>
                            <p class="text-sm opacity-90 mb-6 leading-relaxed">Nhập mã <strong>CICA20</strong> cho đơn hàng đầu tiên của bạn.</p>
                            <button class="bg-white text-cica-red px-8 py-3 rounded-xl font-black text-sm uppercase tracking-tighter shadow-lg hover:shadow-2xl transition-all active:scale-95">Lấy mã ngay</button>
                        </div>
                        <i class="fas fa-utensils absolute -bottom-6 -right-6 text-[180px] opacity-10 rotate-12"></i>
                    </div>

                    <!-- Banner nhỏ -->
                    <div class="bg-orange-500 rounded-2xl p-5 text-white flex items-center justify-between cursor-pointer hover:bg-orange-600 transition shadow-lg">
                        <div>
                            <h5 class="font-bold text-lg">Đồng giá 1k</h5>
                            <p class="text-xs opacity-90">Duy nhất khung giờ vàng 12h-13h</p>
                        </div>
                        <i class="fas fa-bolt text-2xl animate-pulse"></i>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 py-16 mt-20 border-t border-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Cột thông tin chung -->
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 overflow-hidden rounded-lg">
                            <img src="image/z7686292955436_bea3c6d2add3b04e03f1ac1af77bdc4c.jpg" alt="Cicafood Logo" class="w-full h-full object-cover">
                        </div>
                        <span class="text-2xl font-black text-white tracking-tighter">Cicafood</span>
                    </div>
                    <p class="text-sm leading-relaxed mb-6">Trải nghiệm đặt đồ ăn trực tuyến nhanh chóng, tiện lợi và an toàn nhất với hàng nghìn quán ngon trên toàn quốc.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center hover:bg-cica-red hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center hover:bg-cica-red hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center hover:bg-cica-red hover:text-white transition"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Cột liên kết nhanh -->
                <div>
                    <h5 class="text-white font-bold mb-6 text-sm uppercase tracking-widest">Khám Phá</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white transition">Về Cicafood</a></li>
                        <li><a href="#" class="hover:text-white transition">Chương trình đối tác</a></li>
                        <li><a href="#" class="hover:text-white transition">Tuyển dụng shipper</a></li>
                        <li><a href="#" class="hover:text-white transition">Tin tức ẩm thực</a></li>
                    </ul>
                </div>

                <!-- Cột hỗ trợ -->
                <div>
                    <h5 class="text-white font-bold mb-6 text-sm uppercase tracking-widest">Hỗ Trợ</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white transition">Trung tâm trợ giúp</a></li>
                        <li><a href="#" class="hover:text-white transition">Câu hỏi thường gặp</a></li>
                        <li><a href="#" class="hover:text-white transition">Chính sách bảo mật</a></li>
                        <li><a href="#" class="hover:text-white transition">Điều khoản sử dụng</a></li>
                    </ul>
                </div>

                <!-- Cột tải ứng dụng -->
                <div>
                    <h5 class="text-white font-bold mb-6 text-sm uppercase tracking-widest">Tải Ứng Dụng</h5>
                    <div class="space-y-3">
                        <div class="bg-gray-900 border border-gray-800 p-3 rounded-xl flex items-center space-x-3 cursor-pointer hover:bg-gray-800 transition">
                            <i class="fab fa-apple text-2xl"></i>
                            <div>
                                <p class="text-[10px] uppercase">Download on the</p>
                                <p class="text-xs font-bold text-white">App Store</p>
                            </div>
                        </div>
                        <div class="bg-gray-900 border border-gray-800 p-3 rounded-xl flex items-center space-x-3 cursor-pointer hover:bg-gray-800 transition">
                            <i class="fab fa-google-play text-xl"></i>
                            <div>
                                <p class="text-[10px] uppercase">Get it on</p>
                                <p class="text-xs font-bold text-white">Google Play</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dòng bản quyền -->
            <div class="border-t border-gray-900 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center text-[10px] uppercase tracking-widest font-bold">
                <p>&copy; 2024 Cicafood. Designed by Professional Designer.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white">Quy chế hoạt động</a>
                    <a href="#" class="hover:text-white">An toàn thực phẩm</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>