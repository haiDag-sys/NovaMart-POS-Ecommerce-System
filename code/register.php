<?php
session_start();
include 'includes/db.php'; 

if (isset($_SESSION['kh_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['btn_dangky'])) {
    $hoten   = mysqli_real_escape_string($conn, $_POST['hoten']);
    $sdt     = mysqli_real_escape_string($conn, $_POST['sdt']);
    $diachi  = mysqli_real_escape_string($conn, $_POST['diachi']);
    $matkhau = $_POST['matkhau'];
    $re_matkhau = $_POST['re_matkhau']; 

    if ($matkhau !== $re_matkhau) {
        $error = "Mật khẩu nhập lại không khớp!";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $matkhau)) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự, bao gồm cả chữ và số!";
    } else {
        $check_sql = "SELECT * FROM khach_hang WHERE kh_sdt = '$sdt'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            $error = "Số điện thoại này đã được đăng ký!";
        } else {
            $matkhau_hash = password_hash($matkhau, PASSWORD_DEFAULT);
            $sql = "INSERT INTO khach_hang (kh_hoten, kh_sdt, kh_matkhau, kh_diachi) 
                    VALUES ('$hoten', '$sdt', '$matkhau_hash', '$diachi')";
            
            if ($conn->query($sql) === TRUE) {
                echo "<script>
                        alert('Đăng ký thành công!'); 
                        window.location='login_member.php';
                      </script>";
            } else {
                $error = "Lỗi hệ thống: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | NovaMart</title>
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
        <span class="ms-3 fs-4 text-dark border-start border-2 ps-3 fw-semibold">Đăng Ký</span>
    </div>
</header>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold" style="color: var(--brand-color);">TẠO TÀI KHOẢN</h3>
                    </div>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger border-0 rounded-3 text-center small py-2 mb-4">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">HỌ VÀ TÊN</label>
                            <input type="text" name="hoten" class="form-control bg-light border-0 py-2" placeholder="Nhập họ tên" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">SỐ ĐIỆN THOẠI</label>
                            <input type="text" name="sdt" class="form-control bg-light border-0 py-2" placeholder="Dùng để đăng nhập" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">MẬT KHẨU</label>
                            <input type="password" name="matkhau" id="matkhau" class="form-control bg-light border-0 py-2" 
                                   placeholder="Nhập mật khẩu" 
                                   pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$"
                                   required>
                            <div class="form-text" style="font-size: 0.7rem;">* Ít nhất 6 ký tự, gồm chữ và số.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">NHẬP LẠI MẬT KHẨU</label>
                            <input type="password" name="re_matkhau" id="re_matkhau" class="form-control bg-light border-0 py-2" 
                                   placeholder="Xác nhận lại mật khẩu" required>
                            <div id="check_match" class="small mt-1 fw-bold"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">ĐỊA CHỈ</label>
                            <textarea name="diachi" class="form-control bg-light border-0 py-2" rows="2" placeholder="Địa chỉ nhận hàng"></textarea>
                        </div>

                        <button type="submit" name="btn_dangky" id="btnSubmit" class="btn w-100 py-3 fw-bold text-white rounded-3 shadow-sm mb-3" style="background-color: var(--brand-color);">ĐĂNG KÝ NGAY</button>
                    </form>

                    <div class="text-center">
                        <span class="text-muted small">Đã có tài khoản? <a href="login_member.php" class="fw-bold ms-1" style="color: var(--brand-color); text-decoration: none;">Đăng nhập</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = ['assets/js/pages/register.js'];
include 'includes/footer.php';
?>