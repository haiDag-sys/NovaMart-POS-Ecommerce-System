<?php
session_start();
include 'includes/db.php';

$redirectAfterLogin = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');
if ($redirectAfterLogin === '') {
    $redirectAfterLogin = 'index.php';
}
if (preg_match('/^(https?:)?\/\//i', $redirectAfterLogin)) {
    $redirectAfterLogin = 'index.php';
}
if (strpos($redirectAfterLogin, 'javascript:') === 0) {
    $redirectAfterLogin = 'index.php';
}

if (isset($_SESSION['kh_id'])) {
    header("Location: " . $redirectAfterLogin);
    exit();
}

if (isset($_POST['btn_login'])) {
    $sdt = trim($_POST['sdt'] ?? '');
    $matkhau = $_POST['matkhau'] ?? '';

    $stmt = $conn->prepare("SELECT kh_id, kh_hoten, kh_matkhau, kh_avatar FROM khach_hang WHERE kh_sdt = ? LIMIT 1");
    $stmt->bind_param('s', $sdt);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($matkhau, $row['kh_matkhau'])) {
            $_SESSION['kh_id'] = (int) $row['kh_id'];
            $_SESSION['kh_hoten'] = $row['kh_hoten'];
            $_SESSION['kh_avatar'] = $row['kh_avatar'] ?? null;
            header("Location: " . $redirectAfterLogin);
            exit();
        }
        $error = "Mật khẩu không chính xác!";
    } else {
        $error = "Số điện thoại này chưa đăng ký!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | NovaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background-color: #f5f5f5;">

<header class="bg-white py-3 shadow-sm mb-4">
    <div class="container d-flex align-items-center">
        <a href="index.php" class="text-decoration-none d-flex align-items-center">
            <img src="assets/img/logo.jpg" alt="NovaMart Logo" class="img-fluid" style="max-height: 50px; border-radius: 8px;">
            <span class="ms-2 fs-2 fw-bold" style="color: var(--brand-color); letter-spacing: 1px;">NovaMart</span>
        </a>
        <span class="ms-3 fs-4 text-dark border-start border-2 ps-3 fw-semibold">Đăng Nhập</span>
    </div>
</header>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center" style="background: var(--brand-gradient); position: relative;">
                        <div class="text-center text-white p-4">
                            <div class="bg-white d-inline-block p-3 rounded-circle shadow-sm mb-4" style="width: 100px; height: 100px;">
                                <img src="assets/img/logo.jpg" alt="NovaMart Logo" class="img-fluid" style="max-height: 65px;">
                            </div>
                            <h2 class="fw-bold mb-3" style="letter-spacing: 2px;">NovaMart</h2>
                            <p class="opacity-90 fw-light">Nền tảng mua sắm trực tuyến hàng đầu. Mua sắm thông minh, giao hàng siêu tốc!</p>
                        </div>
                    </div>
                    
                    <div class="col-md-7 bg-white">
                        <div class="card-body p-4 p-md-5">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark mb-1">Đăng Nhập</h3>
                                <p class="text-muted small">Chào mừng bạn quay trở lại với NovaMart</p>
                            </div>
                            
                            <?php if(isset($error)): ?>
                                <div class="alert alert-danger border-0 rounded-3 text-center small py-2 mb-4">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectAfterLogin, ENT_QUOTES, "UTF-8"); ?>">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">SỐ ĐIỆN THOẠI</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="text" name="sdt" class="form-control bg-light border-0 shadow-none py-2" placeholder="Nhập số điện thoại" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">MẬT KHẨU</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="matkhau" class="form-control bg-light border-0 shadow-none py-2" placeholder="********" required>
                                    </div>
                                </div>
                                
                                <button type="submit" name="btn_login" class="btn w-100 py-3 fw-bold text-white rounded-3 shadow-sm" style="background-color: var(--brand-color); transition: all 0.3s;">
                                    ĐĂNG NHẬP
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <span class="text-muted small">Bạn chưa có tài khoản? 
                                    <a href="register.php" class="fw-bold ms-1" style="color: var(--brand-color); text-decoration: none;">Đăng ký ngay</a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
