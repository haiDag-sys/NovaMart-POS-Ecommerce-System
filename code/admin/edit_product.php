<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'products';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit();
}

$stmtProduct = $conn->prepare('SELECT * FROM san_pham WHERE sp_id = ? LIMIT 1');
$stmtProduct->bind_param('i', $id);
$stmtProduct->execute();
$sp = $stmtProduct->get_result()->fetch_assoc();
$stmtProduct->close();
if (!$sp) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location='products.php';</script>";
    exit();
}

if (isset($_POST['update_product'])) {
    $ten = trim($_POST['ten_sp'] ?? '');
    $giaBan = (float) ($_POST['gia_ban'] ?? 0);
    $donVi = trim($_POST['don_vi'] ?? '');
    $lspId = (int) ($_POST['loai_sp'] ?? 0);
    $moTa = trim($_POST['sp_mota'] ?? '');

    if ($lspId <= 0) {
        $error = 'Vui lòng chọn loại sản phẩm hợp lệ.';
    } else {
        $stmtUpdate = $conn->prepare('UPDATE san_pham SET sp_ten = ?, sp_giaban = ?, sp_donvi = ?, lsp_id = ?, sp_mota = ? WHERE sp_id = ?');
        $stmtUpdate->bind_param('sdsisi', $ten, $giaBan, $donVi, $lspId, $moTa, $id);
        if ($stmtUpdate->execute()) {
            $stmtUpdate->close();
            if (isset($_FILES['hinh_anh']) && ($_FILES['hinh_anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($fileExtension, $allowedExt, true)) {
                    $uploadDir = '../assets/uploads/products/';

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $newFileName = 'product_' . $id . '_' . time() . '.' . $fileExtension;
                    $targetFile = $uploadDir . $newFileName;

                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetFile)) {
                        if (!empty($sp['sp_hinhanh'])) {
                            $oldImagePath = '../' . ltrim($sp['sp_hinhanh'], '/');
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath);
                            }
                        }

                        $dbImagePath = 'assets/uploads/products/' . $newFileName;

                        $stmtImg = $conn->prepare('UPDATE san_pham SET sp_hinhanh = ? WHERE sp_id = ?');
                        $stmtImg->bind_param('si', $dbImagePath, $id);
                        $stmtImg->execute();
                        $stmtImg->close();
                    }
                }
            }
            echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location='products.php';</script>";
            exit();
        }
        $error = 'Lỗi hệ thống: ' . $conn->error;
    }
}

$resultLoai = $conn->query('SELECT * FROM loai_san_pham ORDER BY lsp_ten ASC');
if (!empty($sp['sp_hinhanh'])) {
    $currentImg = '../' . ltrim($sp['sp_hinhanh'], '/');
} else {
    $folderId = 'SP' . str_pad($id, 2, '0', STR_PAD_LEFT);
    $imgPath = '../assets/uploads/' . $folderId . '/';
    $images = glob($imgPath . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    $currentImg = !empty($images) ? $images[0] : '../assets/img/logo.jpg';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap" style="max-width: 900px;">
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <div class="card backoffice-card">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="backoffice-section-title mb-1">Chỉnh sửa sản phẩm</h2>
                    <p class="text-muted mb-0">Bạn có thể sửa tên, loại, mô tả, giá bán và ảnh đại diện của sản phẩm.</p>
                </div>
                <a href="products.php" class="btn btn-outline-secondary">Quay lại</a>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <img src="<?php echo $currentImg; ?>" alt="Ảnh sản phẩm" class="img-thumbnail rounded-4 shadow-sm" style="width: 220px; height: 220px; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Tên sản phẩm</label>
                                <input type="text" name="ten_sp" class="form-control" value="<?php echo htmlspecialchars($sp['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại sản phẩm</label>
                                <select name="loai_sp" class="form-select" required>
                                    <option value="">-- Chọn loại --</option>
                                    <?php if ($resultLoai): while ($l = $resultLoai->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $l['lsp_id']; ?>" <?php echo ((int) $l['lsp_id'] === (int) $sp['lsp_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Đơn vị tính</label>
                                <select name="don_vi" class="form-select">
                                    <?php foreach (['Hộp','Bịch','Lốc','Chai','Kg','Cái'] as $dv): ?>
                                        <option value="<?php echo $dv; ?>" <?php echo (($sp['sp_donvi'] ?? '') === $dv) ? 'selected' : ''; ?>><?php echo $dv; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" name="gia_ban" class="form-control" value="<?php echo (float) $sp['sp_giaban']; ?>" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tồn kho hiện tại</label>
                                <input type="text" class="form-control bg-light" value="<?php echo format_quantity($sp['sp_tonkho']); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Mô tả sản phẩm</label>
                                <textarea name="sp_mota" class="form-control" rows="4"><?php echo htmlspecialchars($sp['sp_mota'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Thay ảnh đại diện</label>
                                <input type="file" name="hinh_anh" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="products.php" class="btn btn-outline-secondary">Hủy</a>
                    <button type="submit" name="update_product" class="btn btn-primary fw-bold px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
