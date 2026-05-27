<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'stock_receipts';
$q = trim($_GET['q'] ?? '');
$dateFrom = trim($_GET['from'] ?? '');
$dateTo = trim($_GET['to'] ?? '');

$where = [];
$types = '';
$values = [];

if ($q !== '') {
    $where[] = '(n.ncc_ten LIKE ? OR a.ad_hoten LIKE ? OR p.pnk_id = ?)';
    $types .= 'ssi';
    $values[] = '%' . $q . '%';
    $values[] = '%' . $q . '%';
    $values[] = (int) $q;
}
if ($dateFrom !== '') {
    $where[] = 'DATE(p.pnk_ngaylap) >= ?';
    $types .= 's';
    $values[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[] = 'DATE(p.pnk_ngaylap) <= ?';
    $types .= 's';
    $values[] = $dateTo;
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
$sql = "SELECT p.pnk_id, p.pnk_ngaylap, p.pnk_tongtien, p.pnk_hinhthuctt,
               n.ncc_ten, a.ad_hoten,
               COUNT(c.ctpn_id) AS tong_dong,
               COALESCE(SUM(c.ctpn_soluong), 0) AS tong_so_luong
        FROM phieu_nhap_kho p
        LEFT JOIN nha_cung_cap n ON n.ncc_id = p.ncc_id
        LEFT JOIN admin a ON a.ad_id = p.ad_id
        LEFT JOIN ct_phieu_nhap c ON c.pnk_id = p.pnk_id"
        . $whereSql .
        " GROUP BY p.pnk_id, p.pnk_ngaylap, p.pnk_tongtien, p.pnk_hinhthuctt, n.ncc_ten, a.ad_hoten
          ORDER BY p.pnk_id DESC";
$stmt = $conn->prepare($sql);
if ($stmt && $types !== '') {
    $bindValues = $values;
    $refs = [];
    foreach ($bindValues as $k => $v) { $refs[$k] = &$bindValues[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu nhập kho - NovaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="backoffice-section-title mb-1"><i class="fas fa-receipt me-2 text-primary"></i>Lưu trữ phiếu nhập kho</h2>
            <p class="text-muted mb-0">Tất cả phiếu nhập đã lưu đều nằm ở đây, có thể xem chi tiết từng lô hàng đã nhập.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="stock_in.php" class="btn btn-success fw-bold"><i class="fas fa-plus me-1"></i>Lập phiếu mới</a>
            <a href="products.php" class="btn btn-outline-secondary">Quay lại sản phẩm</a>
        </div>
    </div>

    <div class="card backoffice-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Tìm phiếu nhập / nhà cung cấp / người lập</label>
                    <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold flex-grow-1">Lọc</button>
                    <a href="stock_receipts.php" class="btn btn-outline-secondary">Mới</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card backoffice-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã phiếu</th>
                        <th>Ngày lập</th>
                        <th>Nhà cung cấp</th>
                        <th>Người lập</th>
                        <th>Số dòng</th>
                        <th>Tổng SL</th>
                        <th>Tổng tiền</th>
                        <th class="text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo (int) $row['pnk_id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['pnk_ngaylap'])); ?></td>
                                <td><?php echo htmlspecialchars($row['ncc_ten'] ?? 'Chưa có', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['ad_hoten'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $row['tong_dong']; ?></td>
                                <td><?php echo format_quantity($row['tong_so_luong']); ?></td>
                                <td class="text-success fw-bold"><?php echo number_format((float) $row['pnk_tongtien']); ?> đ</td>
                                <td class="text-center"><a href="stock_receipt_show.php?id=<?php echo (int) $row['pnk_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">Chưa có phiếu nhập kho nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
