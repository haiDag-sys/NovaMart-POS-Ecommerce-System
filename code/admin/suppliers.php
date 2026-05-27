
<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'products';
$returnUrl = trim($_GET['return'] ?? $_POST['return_url'] ?? 'stock_in.php');
$allowedReturn = ['stock_in.php', 'products.php'];
if (!in_array($returnUrl, $allowedReturn, true)) {
    $returnUrl = 'stock_in.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name = trim($_POST['ncc_ten'] ?? '');
    $phone = trim($_POST['ncc_sdt'] ?? '');
    $address = trim($_POST['ncc_diachi'] ?? '');

    if ($name === '') {
        set_flash('error', 'Tên nhà cung cấp không được để trống.');
        header('Location: suppliers.php?return=' . urlencode($returnUrl));
        exit();
    }

    $stmtCheck = $conn->prepare('SELECT ncc_id FROM nha_cung_cap WHERE ncc_ten = ? LIMIT 1');
    $stmtCheck->bind_param('s', $name);
    $stmtCheck->execute();
    $exists = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($exists) {
        set_flash('error', 'Nhà cung cấp này đã tồn tại.');
        header('Location: suppliers.php?return=' . urlencode($returnUrl));
        exit();
    }

    $stmt = $conn->prepare('INSERT INTO nha_cung_cap (ncc_ten, ncc_diachi, ncc_sdt) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $address, $phone);
    $stmt->execute();
    $newId = (int) $conn->insert_id;
    $stmt->close();

    set_flash('success', 'Thêm nhà cung cấp thành công.');
    header('Location: ' . $returnUrl . '?ncc_id=' . $newId);
    exit();
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $stmtCount = $conn->prepare('SELECT COUNT(*) AS total FROM phieu_nhap_kho WHERE ncc_id = ?');
    $stmtCount->bind_param('i', $deleteId);
    $stmtCount->execute();
    $countRow = $stmtCount->get_result()->fetch_assoc();
    $stmtCount->close();

    if ((int) ($countRow['total'] ?? 0) > 0) {
        set_flash('error', 'Không thể xóa nhà cung cấp đã có phiếu nhập.');
    } else {
        $stmt = $conn->prepare('DELETE FROM nha_cung_cap WHERE ncc_id = ?');
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Đã xóa nhà cung cấp.');
    }

    header('Location: suppliers.php?return=' . urlencode($returnUrl));
    exit();
}

$suppliers = $conn->query('SELECT n.ncc_id, n.ncc_ten, n.ncc_sdt, n.ncc_diachi, COUNT(p.pnk_id) AS total_receipts FROM nha_cung_cap n LEFT JOIN phieu_nhap_kho p ON p.ncc_id = n.ncc_id GROUP BY n.ncc_id, n.ncc_ten, n.ncc_sdt, n.ncc_diachi ORDER BY n.ncc_ten ASC');
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà cung cấp - NovaMart</title>
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
        <h2 class="backoffice-section-title mb-0">Nhà cung cấp</h2>
        <a href="<?php echo e($returnUrl); ?>" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card backoffice-card">
                <div class="card-body p-4">
                    <h3 class="backoffice-section-title mb-3">Thêm nhà cung cấp</h3>
                    <form method="POST">
                        <input type="hidden" name="return_url" value="<?php echo e($returnUrl); ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên nhà cung cấp</label>
                            <input type="text" name="ncc_ten" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="ncc_sdt" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea name="ncc_diachi" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_supplier" class="btn btn-primary fw-bold w-100">Lưu nhà cung cấp</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card backoffice-card">
                <div class="card-body p-4">
                    <h3 class="backoffice-section-title mb-3">Danh sách nhà cung cấp</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên nhà cung cấp</th>
                                    <th>Số điện thoại</th>
                                    <th>Địa chỉ</th>
                                    <th>Phiếu nhập</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                                    <?php while ($row = $suppliers->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo (int) $row['ncc_id']; ?></td>
                                            <td class="fw-semibold"><?php echo e($row['ncc_ten']); ?></td>
                                            <td><?php echo e($row['ncc_sdt']); ?></td>
                                            <td><?php echo e($row['ncc_diachi']); ?></td>
                                            <td><?php echo (int) $row['total_receipts']; ?></td>
                                            <td class="text-center">
                                                <a href="?delete_id=<?php echo (int) $row['ncc_id']; ?>&return=<?php echo urlencode($returnUrl); ?>" class="btn btn-sm btn-outline-danger" data-confirm-message="Xóa nhà cung cấp này?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có nhà cung cấp nào.</td></tr>
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
