<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'stock_in';

if (isset($_POST['luu_phieu'])) {
    $nccId = (int) ($_POST['ncc_id'] ?? 0);
    $adId = (int) $_SESSION['user_id'];
    $hinhThuc = trim($_POST['hinh_thuc'] ?? 'Tiền mặt');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn->begin_transaction();

        $stmtPnk = $conn->prepare('INSERT INTO phieu_nhap_kho (ad_id, ncc_id, pnk_hinhthuctt, pnk_tongtien, pnk_ngaylap) VALUES (?, ?, ?, 0, NOW())');
        $stmtPnk->bind_param('iis', $adId, $nccId, $hinhThuc);
        $stmtPnk->execute();
        $pnkId = $conn->insert_id;
        $stmtPnk->close();

        $tongTienPhieu = 0;
        $stmtCt = $conn->prepare('INSERT INTO ct_phieu_nhap (pnk_id, sp_id, ctpn_soluong, ctpn_soluongton, ctpn_dongia, ctpn_thanhtien, ctpn_hansudung) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmtUpdateSp = $conn->prepare('UPDATE san_pham SET sp_tonkho = sp_tonkho + ? WHERE sp_id = ?');

        if (isset($_POST['sp_id']) && is_array($_POST['sp_id'])) {
            for ($i = 0; $i < count($_POST['sp_id']); $i++) {
                $spId = (int) $_POST['sp_id'][$i];
                $soLuong = (float) $_POST['soluong'][$i];
                $donGia = (float) $_POST['dongia'][$i];
                $hanSuDung = trim($_POST['hansudung'][$i] ?? '');
                if ($spId <= 0 || $soLuong <= 0) {
                    continue;
                }
                $thanhTien = $soLuong * $donGia;
                $tongTienPhieu += $thanhTien;

                $stmtCt->bind_param('iidddds', $pnkId, $spId, $soLuong, $soLuong, $donGia, $thanhTien, $hanSuDung);
                $stmtCt->execute();

                $stmtUpdateSp->bind_param('di', $soLuong, $spId);
                $stmtUpdateSp->execute();
            }
        }

        $stmtCt->close();
        $stmtUpdateSp->close();

        $stmtUpdatePnk = $conn->prepare('UPDATE phieu_nhap_kho SET pnk_tongtien = ? WHERE pnk_id = ?');
        $stmtUpdatePnk->bind_param('di', $tongTienPhieu, $pnkId);
        $stmtUpdatePnk->execute();
        $stmtUpdatePnk->close();

        $conn->commit();
        echo "<script>alert('Lập phiếu nhập kho thành công!'); window.location='stock_receipt_show.php?id=$pnkId';</script>";
        exit();
    } catch (Throwable $e) {
        $conn->rollback();
        $error = 'Lỗi khi lưu phiếu nhập: ' . $e->getMessage();
    }
}

$nccList = $conn->query('SELECT * FROM nha_cung_cap ORDER BY ncc_ten ASC');
$spList = $conn->query('SELECT sp_id, sp_ten FROM san_pham ORDER BY sp_ten ASC');
$spOptions = "<option value=''>-- Chọn Sản Phẩm --</option>";
while ($sp = $spList->fetch_assoc()) {
    $spOptions .= "<option value='" . (int) $sp['sp_id'] . "'>" . htmlspecialchars($sp['sp_ten'], ENT_QUOTES, 'UTF-8') . "</option>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lập phiếu nhập kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="backoffice-section-title mb-1"><i class="fas fa-truck-loading me-2 text-primary"></i>Lập phiếu nhập kho</h2>
            
        </div>
        <a href="products.php" class="btn btn-outline-secondary">Quay lại sản phẩm</a>
    </div>

    <form action="" method="POST">
        <template id="stock-in-options"><?php echo $spOptions; ?></template>
        <div class="card backoffice-card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nhà cung cấp</label>
                        <?php $selectedNccId = (int) ($_GET['ncc_id'] ?? 0); ?>
                        <div class="d-flex gap-2">
                            <select name="ncc_id" class="form-select" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php while ($ncc = $nccList->fetch_assoc()): ?>
                                    <option value="<?php echo (int) $ncc['ncc_id']; ?>" <?php echo $selectedNccId === (int) $ncc['ncc_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ncc['ncc_ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <a href="suppliers.php?return=stock_in.php" class="btn btn-outline-primary text-nowrap">Thêm mới</a>
                            <a href="stock_receipts.php" class="btn btn-outline-secondary text-nowrap">Xem phiếu nhập</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hình thức thanh toán</label>
                        <select name="hinh_thuc" class="form-select">
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Chuyển khoản">Chuyển khoản</option>
                            <option value="Công nợ">Ghi công nợ</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card backoffice-card">
            <div class="card-header bg-dark text-white fw-bold py-3">Chi tiết lô hàng nhập</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="tableChiTiet">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="35%">Tên sản phẩm</th>
                            <th width="15%">Số lượng</th>
                            <th width="20%">Giá nhập 1 đơn vị</th>
                            <th width="20%">Hạn sử dụng</th>
                            <th width="10%">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="chiTietBody">
                        <tr>
                            <td><select name="sp_id[]" class="form-select" required><?php echo $spOptions; ?></select></td>
                            <td><input type="number" name="soluong[]" class="form-control text-center" required min="0.01" step="0.01" value="1"></td>
                            <td><input type="number" name="dongia[]" class="form-control text-end" required min="0"></td>
                            <td><input type="date" name="hansudung[]" class="form-control" required></td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove">Xóa</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnAddRow">+ Thêm sản phẩm khác</button>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" name="luu_phieu" class="btn btn-success btn-lg fw-bold px-5 shadow">Lưu phiếu nhập kho</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin/stock-in.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
