<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'categories';
$uploadDir = '../assets/uploads/categories';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function upload_category_image($categoryId, $fieldName, $uploadDir)
{
    if (!isset($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return true;
    }

    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowedExt, true)) {
        set_flash('error', 'Hình ảnh loại sản phẩm chỉ hỗ trợ jpg, jpeg, png hoặc webp.');
        return false;
    }

    delete_category_image($categoryId);
    $targetPath = rtrim($uploadDir, '/') . '/cat_' . (int) $categoryId . '.' . $ext;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        set_flash('error', 'Không thể tải hình ảnh loại sản phẩm lên hệ thống.');
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['lsp_ten'] ?? '');
    if ($name === '') {
        set_flash('error', 'Tên loại sản phẩm không được để trống.');
        header('Location: categories.php');
        exit();
    }

    $stmtCheck = $conn->prepare('SELECT lsp_id FROM loai_san_pham WHERE lsp_ten = ? LIMIT 1');
    $stmtCheck->bind_param('s', $name);
    $stmtCheck->execute();
    $exists = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($exists) {
        set_flash('error', 'Loại sản phẩm này đã tồn tại.');
        header('Location: categories.php');
        exit();
    }

    $stmt = $conn->prepare('INSERT INTO loai_san_pham (lsp_ten) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $categoryId = (int) $conn->insert_id;
    $stmt->close();

    if (!upload_category_image($categoryId, 'lsp_hinhanh', $uploadDir)) {
        header('Location: categories.php');
        exit();
    }

    set_flash('success', 'Thêm loại sản phẩm thành công.');
    header('Location: categories.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoryId = (int) ($_POST['lsp_id'] ?? 0);
    $name = trim($_POST['lsp_ten'] ?? '');

    if ($categoryId <= 0) {
        set_flash('error', 'Loại sản phẩm không hợp lệ.');
        header('Location: categories.php');
        exit();
    }

    if ($name === '') {
        set_flash('error', 'Tên loại sản phẩm không được để trống.');
        header('Location: categories.php?edit_id=' . $categoryId);
        exit();
    }

    $stmtCheck = $conn->prepare('SELECT lsp_id FROM loai_san_pham WHERE lsp_ten = ? AND lsp_id <> ? LIMIT 1');
    $stmtCheck->bind_param('si', $name, $categoryId);
    $stmtCheck->execute();
    $exists = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($exists) {
        set_flash('error', 'Tên loại sản phẩm này đã được sử dụng.');
        header('Location: categories.php?edit_id=' . $categoryId);
        exit();
    }

    $stmt = $conn->prepare('UPDATE loai_san_pham SET lsp_ten = ? WHERE lsp_id = ?');
    $stmt->bind_param('si', $name, $categoryId);
    $stmt->execute();
    $stmt->close();

    if (!upload_category_image($categoryId, 'lsp_hinhanh', $uploadDir)) {
        header('Location: categories.php?edit_id=' . $categoryId);
        exit();
    }

    set_flash('success', 'Cập nhật loại sản phẩm thành công.');
    header('Location: categories.php');
    exit();
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];

    $stmtCount = $conn->prepare('SELECT COUNT(*) AS total FROM san_pham WHERE lsp_id = ?');
    $stmtCount->bind_param('i', $deleteId);
    $stmtCount->execute();
    $countRow = $stmtCount->get_result()->fetch_assoc();
    $stmtCount->close();

    if ((int) ($countRow['total'] ?? 0) > 0) {
        set_flash('error', 'Không thể xóa loại sản phẩm đang có sản phẩm sử dụng.');
    } else {
        delete_category_image($deleteId);
        $stmt = $conn->prepare('DELETE FROM loai_san_pham WHERE lsp_id = ?');
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Đã xóa loại sản phẩm.');
    }

    header('Location: categories.php');
    exit();
}

$editCategory = null;
if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    if ($editId > 0) {
        $stmtEdit = $conn->prepare('SELECT lsp_id, lsp_ten FROM loai_san_pham WHERE lsp_id = ? LIMIT 1');
        $stmtEdit->bind_param('i', $editId);
        $stmtEdit->execute();
        $editCategory = $stmtEdit->get_result()->fetch_assoc();
        $stmtEdit->close();

        if (!$editCategory) {
            set_flash('error', 'Không tìm thấy loại sản phẩm cần chỉnh sửa.');
            header('Location: categories.php');
            exit();
        }
    }
}

$result = $conn->query('SELECT l.lsp_id, l.lsp_ten, COUNT(s.sp_id) AS total_products FROM loai_san_pham l LEFT JOIN san_pham s ON s.lsp_id = l.lsp_id GROUP BY l.lsp_id, l.lsp_ten ORDER BY l.lsp_ten ASC');
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Loại sản phẩm - NovaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <?php if (!empty($flashSuccess)): ?><div class="alert alert-success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
    <?php if (!empty($flashError)): ?><div class="alert alert-danger"><?php echo e($flashError); ?></div><?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="backoffice-section-title mb-0">Loại sản phẩm</h2>
        <a href="products.php" class="btn btn-outline-secondary">Quay lại sản phẩm</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <?php if ($editCategory): ?>
                <?php $currentEditImage = category_image_url((int) $editCategory['lsp_id'], '../'); ?>
                <div class="card backoffice-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="backoffice-section-title mb-0">Chỉnh sửa loại sản phẩm</h3>
                            <a href="categories.php" class="btn btn-sm btn-outline-secondary">Hủy</a>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="lsp_id" value="<?php echo (int) $editCategory['lsp_id']; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên loại sản phẩm</label>
                                <input type="text" name="lsp_ten" class="form-control" value="<?php echo e($editCategory['lsp_ten']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hình ảnh hiện tại</label><br>
                                <?php if ($currentEditImage): ?>
                                    <img src="<?php echo $currentEditImage; ?>" alt="<?php echo e($editCategory['lsp_ten']); ?>" style="width:96px;height:96px;object-fit:cover;border-radius:16px;">
                                <?php else: ?>
                                    <div class="d-inline-flex align-items-center justify-content-center bg-light border rounded-3" style="width:96px;height:96px;">
                                        <i class="fas fa-layer-group text-secondary fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Đổi hình ảnh</label>
                                <input type="file" name="lsp_hinhanh" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">Bỏ trống nếu muốn giữ hình ảnh hiện tại.</div>
                            </div>
                            <button type="submit" name="update_category" class="btn btn-success fw-bold w-100">Cập nhật loại sản phẩm</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card backoffice-card">
                <div class="card-body p-4">
                    <h3 class="backoffice-section-title mb-3">Thêm loại sản phẩm</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên loại sản phẩm</label>
                            <input type="text" name="lsp_ten" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh</label>
                            <input type="file" name="lsp_hinhanh" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary fw-bold w-100">Lưu loại sản phẩm</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card backoffice-card">
                <div class="card-body p-4">
                    <h3 class="backoffice-section-title mb-3">Danh sách loại sản phẩm</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên loại</th>
                                    <th>Số sản phẩm</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php $imageUrl = category_image_url((int) $row['lsp_id'], '../'); ?>
                                        <tr>
                                            <td>#<?php echo (int) $row['lsp_id']; ?></td>
                                            <td>
                                                <?php if ($imageUrl): ?>
                                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($row['lsp_ten']); ?>" style="width:56px;height:56px;object-fit:cover;border-radius:12px;">
                                                <?php else: ?>
                                                    <div class="d-inline-flex align-items-center justify-content-center bg-light border rounded-3" style="width:56px;height:56px;">
                                                        <i class="fas fa-layer-group text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-semibold"><?php echo e($row['lsp_ten']); ?></td>
                                            <td><?php echo (int) $row['total_products']; ?></td>
                                            <td class="text-center">
                                                <a href="?edit_id=<?php echo (int) $row['lsp_id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Chỉnh sửa loại sản phẩm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete_id=<?php echo (int) $row['lsp_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm-message="Xóa loại sản phẩm này?" title="Xóa loại sản phẩm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có loại sản phẩm nào.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
