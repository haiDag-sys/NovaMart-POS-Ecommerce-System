<?php
$tenNguoiTruc = $_SESSION['hoten'] ?? 'Nhân viên';
?>
<nav class="navbar navbar-expand-lg backoffice-navbar navbar-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="../assets/img/logo.jpg" alt="NovaMart" style="height: 38px; width: 38px; object-fit: cover; border-radius: 10px;">
            <span>NovaMart POS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#staffNavbar" aria-controls="staffNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="staffNavbar">
            <ul class="navbar-nav ms-lg-4 me-auto mb-3 mb-lg-0 gap-lg-2">
                <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-cash-register me-2"></i>Bán hàng tại quầy</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="backoffice-user-badge"><i class="fas fa-user-tie"></i><?php echo htmlspecialchars($tenNguoiTruc, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../admin/logout.php" class="btn btn-danger btn-sm fw-bold rounded-pill px-3" data-confirm-message="Bạn muốn đăng xuất khỏi POS?"><i class="fas fa-right-from-bracket me-1"></i>Đăng xuất</a>
            </div>
        </div>
    </div>
</nav>
