<?php
// includes/footer.php - Footer dùng chung
?>
<!-- ============================================================ -->
<!-- FOOTER -->
<!-- ============================================================ -->
<footer class="bg-gray-950 text-gray-400 mt-20 pt-16 pb-8">
    <div class="max-w-screen-xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            
            <!-- Brand -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl overflow-hidden border border-red-900/30">
                        <img src="/foodbooking/public/images/z7686292944601_a4cd0ae11726520e38367bb70753f8be.jpg" 
                             alt="Cicafood Logo" class="w-full h-full object-cover">
                    </div>
                    <span class="text-2xl font-black text-white tracking-tight">Cicafood</span>
                </div>
                <p class="text-sm leading-relaxed mb-6 text-gray-500">
                    Trải nghiệm đặt đồ ăn trực tuyến nhanh chóng, tiện lợi và an toàn nhất với hàng nghìn quán ngon trên toàn quốc.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 bg-gray-900 border border-gray-800 rounded-full flex items-center justify-center hover:bg-cica-red hover:border-transparent text-sm transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-900 border border-gray-800 rounded-full flex items-center justify-center hover:bg-gradient-to-br hover:from-pink-500 hover:to-orange-400 hover:border-transparent text-sm transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-900 border border-gray-800 rounded-full flex items-center justify-center hover:bg-black hover:border-transparent text-sm transition">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <!-- Khám phá -->
            <div>
                <h5 class="text-white font-bold mb-5 text-xs uppercase tracking-widest">Khám Phá</h5>
                <ul class="space-y-3 text-sm">
                    <li><a href="/foodbooking/" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Trang chủ</a></li>
                    <li><a href="/foodbooking/views/restaurant/list.php" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Nhà hàng</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Chương trình đối tác</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Tuyển shipper</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Tin tức ẩm thực</a></li>
                </ul>
            </div>

            <!-- Hỗ trợ -->
            <div>
                <h5 class="text-white font-bold mb-5 text-xs uppercase tracking-widest">Hỗ Trợ</h5>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Trung tâm trợ giúp</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Câu hỏi thường gặp</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Chính sách bảo mật</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>Điều khoản sử dụng</a></li>
                    <li><a href="#" class="hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-cica-red"></i>An toàn thực phẩm</a></li>
                </ul>
            </div>

            <!-- Tải ứng dụng + Hotline -->
            <div>
                <h5 class="text-white font-bold mb-5 text-xs uppercase tracking-widest">Tải Ứng Dụng</h5>
                <div class="space-y-3 mb-6">
                    <a href="#" class="bg-gray-900 border border-gray-800 p-3 rounded-xl flex items-center gap-3 hover:bg-gray-800 hover:border-gray-700 transition group">
                        <i class="fab fa-apple text-2xl group-hover:text-white"></i>
                        <div>
                            <p class="text-[10px] uppercase text-gray-500">Download on the</p>
                            <p class="text-xs font-bold text-white">App Store</p>
                        </div>
                    </a>
                    <a href="#" class="bg-gray-900 border border-gray-800 p-3 rounded-xl flex items-center gap-3 hover:bg-gray-800 hover:border-gray-700 transition group">
                        <i class="fab fa-google-play text-xl text-green-400 group-hover:text-green-300"></i>
                        <div>
                            <p class="text-[10px] uppercase text-gray-500">Get it on</p>
                            <p class="text-xs font-bold text-white">Google Play</p>
                        </div>
                    </a>
                </div>
                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Hotline hỗ trợ</p>
                    <p class="text-cica-red font-black text-lg">1900-6750</p>
                    <p class="text-xs text-gray-600">Miễn phí · 24/7</p>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-gray-900 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-[11px] uppercase tracking-widest font-bold text-gray-600">
            <p>© <?= date('Y') ?> Cicafood. Designed by Bùi Trung Dũng.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-white transition">Quy chế hoạt động</a>
                <span class="text-gray-800">|</span>
                <a href="#" class="hover:text-white transition">Giấy phép ATTP</a>
                <span class="text-gray-800">|</span>
                <a href="#" class="hover:text-white transition">DMCA</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button id="back-to-top" 
        onclick="window.scrollTo({top:0, behavior:'smooth'})"
        class="fixed bottom-6 right-6 w-11 h-11 bg-cica-red text-white rounded-full shadow-lg shadow-red-200 items-center justify-center text-sm hover:bg-red-700 transition z-50 hidden">
    <i class="fas fa-chevron-up"></i>
</button>

</script>
<script>
async function toggleFavorite(event, restaurantId, btnElement) {
    event.preventDefault(); // Ngăn click vào thẻ <a> bọc ngoài
    try {
        const res = await fetch('/foodbooking/api/restaurant/toggle_favorite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({restaurant_id: restaurantId})
        });
        const data = await res.json();
        if(data.success) {
            const icon = btnElement.querySelector('i');
            if(data.is_favorite) {
                icon.classList.replace('far', 'fas');
                icon.classList.add('text-cica-red');
            } else {
                icon.classList.replace('fas', 'far');
                icon.classList.remove('text-cica-red');
            }
            if(typeof showToast === 'function') {
                showToast('success', data.is_favorite ? 'Đã lưu vào danh sách Yêu thích' : 'Đã bỏ Yêu thích');
            }
        } else {
            if(typeof showToast === 'function') {
                showToast('error', data.message);
            } else {
                alert(data.message);
            }
        }
    } catch(e) { console.error(e); }
}

// Back to top visibility
window.addEventListener('scroll', () => {
    const btn = document.getElementById('back-to-top');
    if (btn) {
        if (window.scrollY > 300) btn.classList.replace('hidden', 'flex');
        else btn.classList.replace('flex', 'hidden');
    }
});
</script>

</body>
</html>
