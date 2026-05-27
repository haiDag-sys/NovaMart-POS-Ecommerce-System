<?php
if (!isset($currentAdminPage)) {
    $currentAdminPage = '';
}
$tenNguoiTruc = $_SESSION['hoten'] ?? 'Quản trị viên';
?>
<nav class="navbar navbar-expand-lg backoffice-navbar navbar-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="../assets/img/logo.jpg" alt="NovaMart" style="height: 38px; width: 38px; object-fit: cover; border-radius: 10px;">
            <span>NovaMart ADMIN</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-lg-4 me-auto mb-3 mb-lg-0 gap-lg-2">
                <li class="nav-item"><a class="nav-link <?php echo $currentAdminPage === 'dashboard' ? 'active' : ''; ?>" href="index.php"><i class="fas fa-chart-line me-2"></i>Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link <?php echo in_array($currentAdminPage, ['products', 'categories', 'stock_in', 'stock_receipts', 'stock_receipt_show'], true) ? 'active' : ''; ?>" href="products.php"><i class="fas fa-boxes-stacked me-2"></i>Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $currentAdminPage === 'orders' ? 'active' : ''; ?>" href="orders.php"><i class="fas fa-file-invoice-dollar me-2"></i>Đơn hàng <span id="admin-orders-badge" class="badge rounded-pill bg-danger ms-1 d-none">0</span></a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="backoffice-user-badge"><i class="fas fa-user-shield"></i><?php echo htmlspecialchars($tenNguoiTruc, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold rounded-pill px-3" data-confirm-message="Bạn muốn đăng xuất khỏi trang quản trị?"><i class="fas fa-right-from-bracket me-1"></i>Đăng xuất</a>
            </div>
        </div>
    </div>
</nav>
