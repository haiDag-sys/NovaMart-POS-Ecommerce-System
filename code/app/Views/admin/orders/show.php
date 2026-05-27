<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết hóa đơn #<?php echo (int) $order['hd_id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        body { background: #f0f2f5; }
        .invoice-card { max-width: 700px; margin: 30px auto; background: white; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .invoice-header { border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 15px; }
        .invoice-footer { border-top: 2px dashed #000; padding-top: 10px; margin-top: 15px; }
        @media print {
            body * { visibility: hidden; }
            .invoice-card, .invoice-card * { visibility: visible; }
            .invoice-card { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; margin: 0; padding: 10px;}
            .no-print { display: none !important; }
        }
    </style>
</head>
<?php $currentAdminPage = 'orders'; ?>
<body class="backoffice-body">
<?php include base_path('admin/includes/header.php'); ?>
    <div class="text-center mt-3 mb-2 no-print">
        <a href="orders.php" class="btn btn-dark btn-sm shadow-sm"><i class="fas fa-arrow-left"></i> Quay lại Danh sách</a>
        <button data-print-window="true" class="btn btn-primary btn-sm fw-bold shadow-sm"><i class="fas fa-print"></i> IN HÓA ĐƠN</button>
    </div>

    <div class="container no-print" style="max-width: 760px;">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger"><?php echo e($flashError); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Xử lý đơn hàng</h5>

                <?php if ($order['la_don_online']): ?>
                    <p class="text-muted mb-3">Đơn online chỉ được chuyển trạng thái theo đúng thứ tự nghiệp vụ.</p>
                <?php else: ?>
                    <p class="text-muted mb-3">Đơn POS được lập tại quầy và thường ở trạng thái hoàn tất.</p>
                <?php endif; ?>

                <form method="POST" class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Trạng thái đơn</label>
                            <select name="hd_trangthai" class="form-select" <?php echo $order['la_don_online'] ? '' : 'disabled'; ?>>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>" <?php echo (($order['hd_trangthai'] ?? '') === $value) ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <?php if ($order['la_don_online']): ?>
                                <button type="submit" name="cap_nhat_trang_thai" class="btn btn-success w-100 fw-bold">Cập nhật trạng thái</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary w-100 fw-bold" disabled>Đơn POS</button>
                            <?php endif; ?>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-header text-center">
            <h3 class="fw-bold m-0 text-uppercase">NovaMart</h3>
            <p class="small mb-1">Địa chỉ: 123 Đường 3/2, Cần Thơ</p>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between"><span class="fw-bold">Hóa đơn:</span><span>#<?php echo (int) $order['hd_id']; ?></span></div>
            <div class="d-flex justify-content-between"><span class="fw-bold">Ngày lập:</span><span><?php echo date('d/m/Y H:i', strtotime($order['hd_ngaylap'])); ?></span></div>
            <div class="d-flex justify-content-between"><span class="fw-bold">Nguồn đơn:</span><span><?php echo e($order['nguon_don']); ?></span></div>
            <div class="d-flex justify-content-between"><span class="fw-bold">Trạng thái:</span><span class="badge <?php echo e(order_status_badge($order['hd_trangthai'] ?? 'dang_xu_ly')); ?>"><?php echo e($order['hd_trangthai_hienthi']); ?></span></div>
            <?php if (!empty($order['kh_hoten'])): ?>
                <div class="d-flex justify-content-between"><span class="fw-bold">Khách hàng:</span><span><?php echo e($order['kh_hoten']); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($order['kh_sdt'])): ?>
                <div class="d-flex justify-content-between"><span class="fw-bold">SĐT:</span><span><?php echo e($order['kh_sdt']); ?></span></div>
            <?php endif; ?>
            <div class="d-flex justify-content-between"><span class="fw-bold">Địa chỉ nhận:</span><span class="text-end"><?php echo e($order['hd_diachinhan'] ?? ($order['kh_diachi'] ?? 'Mua tại quầy')); ?></span></div>
            <?php if (!empty($order['hd_ghichu'])): ?>
                <div class="mt-2">
                    <div class="fw-bold">Ghi chú khách:</div>
                    <div class="text-muted"><?php echo nl2br(e($order['hd_ghichu'])); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <table class="table table-borderless table-sm mb-2">
            <thead class="border-bottom border-dark">
                <tr>
                    <th class="ps-0">Sản phẩm</th>
                    <th class="text-center">SL</th>
                    <th class="text-end pe-0">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="ps-0"><?php echo e($item['sp_ten']); ?><br><small class="text-muted">x <?php echo number_format($item['cthd_dongia']); ?></small></td>
                    <td class="text-center align-middle"><?php echo format_quantity($item['cthd_soluong']); ?></td>
                    <td class="text-end align-middle pe-0 fw-bold"><?php echo number_format($item['cthd_thanhtien']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="invoice-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-5">TỔNG CỘNG:</span>
                <span class="fw-bold fs-4"><?php echo number_format($order['hd_tongtien']); ?> đ</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-muted small">Hình thức TT:</span>
                <span class="fw-bold small"><?php echo mb_strtoupper($order['hd_hinhthuctt'], 'UTF-8'); ?></span>
            </div>
        </div>
        <div class="text-center mt-4 fst-italic"><small>Cảm ơn quý khách & Hẹn gặp lại!</small></div>
    </div>
<script src="../assets/js/main.js"></script>
</body>
</html>