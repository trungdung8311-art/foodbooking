<?php
/* merchant/layout_footer.php - Đóng layout, global JS */
?>
    </div><!-- end .content-area -->
</div><!-- end .main-content -->

<!-- Global Scripts -->
<script>
function showToast(type, msg, duration = 3500) {
    const c = document.getElementById('toast-container');
    const icons = { success: 'fa-check-circle', error: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]||'fa-bell'}"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => {
        t.style.transition = 'all 0.3s';
        t.style.opacity = '0';
        t.style.transform = 'translateX(40px)';
        setTimeout(() => t.remove(), 300);
    }, duration);
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Confirm delete
function confirmDelete(form, msg = 'Bạn có chắc muốn xóa?') {
    if (confirm(msg)) form.submit();
    return false;
}

// Format price
function formatVND(n) {
    return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
}

<?php if (isset($flashToast)): ?>
document.addEventListener('DOMContentLoaded', function() {
    showToast('<?= $flashToast['type'] ?>', '<?= addslashes($flashToast['message']) ?>');
});
<?php endif; ?>
</script>
</body>
</html>
