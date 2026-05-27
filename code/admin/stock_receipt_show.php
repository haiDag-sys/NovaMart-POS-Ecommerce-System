<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'stock_receipt_show';
$pnkId = (int) ($_GET['id'] ?? 0);
if ($pnkId <= 0) {
    header('Location: stock_receipts.php');
    exit();
}

$stmtHeader = $conn->prepare("SELECT p.*, n.ncc_ten, n.ncc_sdt, n.ncc_diachi, a.ad_hoten
                              FROM phieu_nhap_kho p
                              LEFT JOIN nha_cung_cap n ON n.ncc_id = p.ncc_id
                              LEFT JOIN admin a ON a.ad_id = p.ad_id
                              WHERE p.pnk_id = ? LIMIT 1");
$stmtHeader->bind_param('i', $pnkId);
$stmtHeader->execute();
$receipt = $stmtHeader->get_result()->fetch_assoc();
$stmtHeader->close();

if (!$receipt) {
    header('Location: stock_receipts.php');
    exit();
}

$stmtItems = $conn->prepare("SELECT c.*, s.sp_ten
                             FROM ct_phieu_nhap c
                             INNER JOIN san_pham s ON s.sp_id = c.sp_id
                             WHERE c.pnk_id = ?
                             ORDER BY c.ctpn_id ASC");
$stmtItems->bind_param('i', $pnkId);
$stmtItems->execute();
$items = $stmtItems->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết phiếu nhập #<?php echo (int) $pnkId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
        <div>
            <h2 class="backoffice-section-title mb-1"><i class="fas fa-file-invoice me-2 text-primary"></i>Chi tiết phiếu nhập #<?php echo (int) $receipt['pnk_id']; ?></h2>
            <p class="text-muted mb-0">Xem lại thông tin nhà cung cấp, người lập và các lô hàng đã nhập.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="stock_receipts.php" class="btn btn-outline-secondary">Quay lại danh sách phiếu nhập</a>
            <a href="products.php" class="btn btn-outline-dark">Quay lại sản phẩm</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card backoffice-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Thông tin phiếu nhập</h5>
                    <p class="mb-2"><strong>Ngày lập:</strong> <?php echo date('d/m/Y H:i', strtotime($receipt['pnk_ngaylap'])); ?></p>
                    <p class="mb-2"><strong>Hình thức thanh toán:</strong> <?php echo htmlspecialchars($receipt['pnk_hinhthuctt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-0"><strong>Người lập:</strong> <?php echo htmlspecialchars($receipt['ad_hoten'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card backoffice-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Nhà cung cấp</h5>
                    <p class="mb-2"><strong>Tên NCC:</strong> <?php echo htmlspecialchars($receipt['ncc_ten'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-2"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($receipt['ncc_sdt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($receipt['ncc_diachi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card backoffice-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Sản phẩm</th>
                        <th>Số lượng nhập</th>
                        <th>Số lượng còn</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <th>Hạn sử dụng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items && $items->num_rows > 0): ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($item['sp_ten'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo format_quantity($item['ctpn_soluong']); ?></td>
                                <td><?php echo format_quantity($item['ctpn_soluongton']); ?></td>
                                <td><?php echo number_format((float) $item['ctpn_dongia']); ?> đ</td>
                                <td class="text-success fw-bold"><?php echo number_format((float) $item['ctpn_thanhtien']); ?> đ</td>
                                <td><?php echo !empty($item['ctpn_hansudung']) ? date('d/m/Y', strtotime($item['ctpn_hansudung'])) : 'Không có'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Phiếu nhập chưa có dòng hàng nào.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Tổng tiền phiếu nhập</th>
                        <th class="text-success"><?php echo number_format((float) $receipt['pnk_tongtien']); ?> đ</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
