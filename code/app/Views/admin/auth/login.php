<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="card login-card bg-white">
        <div class="card-header">
            <h3 class="m-0 fw-bold"><i class="fas fa-store"></i> HỆ THỐNG QUẢN LÝ</h3>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tài khoản</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Nhập tài khoản..." required autofocus value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold text-uppercase shadow-sm">
                    Đăng Nhập
                </button>
            </form>
        </div>
        <div class="card-footer text-center bg-light py-3 small text-muted">
            Hệ thống quản lý bán hàng v1.0
        </div>
    </div>
</body>
</html>