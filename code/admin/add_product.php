<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'products';

if (isset($_POST['save_product'])) {
    $ten = trim($_POST['ten_sp'] ?? '');
    $giaBan = (float) ($_POST['gia_ban'] ?? 0);
    $tonKho = 0;
    $donVi = trim($_POST['don_vi'] ?? '');
    $lspId = (int) ($_POST['loai_sp'] ?? 0);
    $moTa = trim($_POST['sp_mota'] ?? '');

    $stmt = $conn->prepare('INSERT INTO san_pham (sp_ten, sp_giaban, sp_tonkho, sp_donvi, lsp_id, sp_mota) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sdisis', $ten, $giaBan, $tonKho, $donVi, $lspId, $moTa);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();

        if (isset($_FILES['hinh_anh']) && ($_FILES['hinh_anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $fileExtension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExt, true)) {
                $uploadDir = '../assets/uploads/products/';

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = 'product_' . $newId . '_' . time() . '.' . $fileExtension;
                $targetFile = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetFile)) {
                    $dbImagePath = 'assets/uploads/products/' . $newFileName;

                    $stmtImg = $conn->prepare('UPDATE san_pham SET sp_hinhanh = ? WHERE sp_id = ?');
                    $stmtImg->bind_param('si', $dbImagePath, $newId);
                    $stmtImg->execute();
                    $stmtImg->close();
                }
            }
        }

        echo "<script>alert('Thêm sản phẩm thành công! Bạn có thể tiếp tục vào Nhập kho để tạo lô hàng.'); window.location='products.php';</script>";
        exit();
    }

    $error = 'Lỗi hệ thống: ' . $conn->error;
}

$resultLoai = $conn->query('SELECT * FROM loai_san_pham ORDER BY lsp_ten ASC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap" style="max-width: 860px;">
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <div class="card backoffice-card">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="backoffice-section-title mb-1">Thêm sản phẩm mới</h2>
                </div>
                <a href="products.php" class="btn btn-outline-secondary">Quay lại</a>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="ten_sp" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Loại sản phẩm</label>
                        <select name="loai_sp" class="form-select" required>
                            <option value="">-- Chọn loại --</option>
                            <?php if ($resultLoai): while ($l = $resultLoai->fetch_assoc()): ?>
                                <option value="<?php echo (int) $l['lsp_id']; ?>"><?php echo htmlspecialchars($l['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                        <input type="number" name="gia_ban" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Đơn vị tính</label>
                        <select name="don_vi" class="form-select">
                            <option value="Hộp">Hộp</option>
                            <option value="Bịch">Bịch</option>
                            <option value="Lốc">Lốc</option>
                            <option value="Chai">Chai</option>
                            <option value="Kg">Kg</option>
                            <option value="Cái">Cái</option>
                            <option value="Gói">Gói</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Mô tả sản phẩm</label>
                        <textarea name="sp_mota" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Ảnh đại diện sản phẩm</label>
                        <input type="file" name="hinh_anh" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="products.php" class="btn btn-outline-secondary">Hủy</a>
                    <button type="submit" name="save_product" class="btn btn-success fw-bold px-4">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
