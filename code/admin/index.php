<?php
session_start();
require_once '../bootstrap.php';

require_admin();

$orderModel = new App\Models\OrderModel();

$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

$data_rows = $orderModel->getDashboardTransactions($from_date, $to_date);
$tong_doanh_thu = 0;
$don_online_dang_xu_ly = 0;

foreach ($data_rows as $row) {
    $trang_thai = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');

    if ($trang_thai === 'hoan_thanh') {
        $tong_doanh_thu += (float) $row['hd_tongtien'];
    }

    if (!empty($row['kh_id']) && empty($row['nv_id']) && $trang_thai === 'dang_xu_ly') {
        $don_online_dang_xu_ly++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Tổng Quan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-header-custom { background: linear-gradient(to right, #2c3e50, #3498db); color: white; }
    </style>
</head>
<?php $currentAdminPage = 'dashboard'; ?>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body py-3">
            <form method="GET" action="" class="row align-items-center gx-3 gy-2">
                <div class="col-auto">
                    <span class="fw-bold text-muted"><i class="fas fa-filter"></i> Lọc báo cáo:</span>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">Từ ngày</span>
                        <input type="date" name="from" class="form-control" value="<?php echo e($from_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">Đến ngày</span>
                        <input type="date" name="to" class="form-control" value="<?php echo e($to_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">XEM BÁO CÁO</button>
                    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div id="dashboard-revenue-card" class="card shadow bg-success text-white border-0 rounded-4 overflow-hidden h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <h5 class="opacity-75 mb-3 fw-normal text-uppercase"><i class="fas fa-wallet"></i> TỔNG DOANH THU GIAO DỊCH</h5>
                    <h2 class="display-5 fw-bold mb-0"><span data-dashboard-revenue><?php echo number_format($tong_doanh_thu, 0, ',', '.'); ?></span> <span class="fs-4">VNĐ</span></h2>
                    <hr class="opacity-25 my-3">
                    <small class="opacity-75">Dữ liệu từ <b><?php echo date('d/m/Y', strtotime($from_date)); ?></b> đến <b><?php echo date('d/m/Y', strtotime($to_date)); ?></b><br></small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div id="dashboard-pending-card" class="card shadow bg-warning border-0 rounded-4 overflow-hidden h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <h5 class="opacity-75 mb-3 fw-normal text-uppercase"><i class="fas fa-clock"></i> ĐƠN ONLINE ĐANG XỬ LÝ</h5>
                    <h2 class="display-5 fw-bold mb-0" data-dashboard-pending><?php echo number_format($don_online_dang_xu_ly); ?></h2>
                    <hr class="opacity-25 my-3">
                    <small class="opacity-75">Các đơn cần admin xác nhận/xử lý.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div id="dashboard-total-card" class="card shadow bg-primary text-white border-0 rounded-4 overflow-hidden h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <h5 class="opacity-75 mb-3 fw-normal text-uppercase"><i class="fas fa-receipt"></i> TỔNG GIAO DỊCH</h5>
                    <h2 class="display-5 fw-bold mb-0" data-dashboard-total><?php echo number_format(count($data_rows)); ?></h2>
                    <hr class="opacity-25 my-3">
                    <small class="opacity-75">Bao gồm đơn online và đơn POS.</small>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header card-header-custom fw-bold py-3">
                    <i class="fas fa-file-invoice-dollar me-2"></i> CHI TIẾT GIAO DỊCH TRONG KỲ
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 460px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top shadow-sm">
                                <tr>
                                    <th class="ps-4">Mã HĐ</th>
                                    <th>Thời gian</th>
                                    <th>Nguồn đơn</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-end">Thành tiền</th>
                                    <th class="text-center pe-4">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-orders-tbody">
                                <?php if (!empty($data_rows)): ?>
                                    <?php foreach($data_rows as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">#<?php echo (int) $row['hd_id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['hd_ngaylap'])); ?></td>
                                        <td><?php echo e($row['nguon_don']); ?></td>
                                        <td class="text-center"><span class="badge <?php echo e(order_status_badge($row['hd_trangthai'] ?? 'dang_xu_ly')); ?>"><?php echo e($row['hd_trangthai_hienthi']); ?></span></td>
                                        <td class="text-end fw-bold text-success"><?php echo number_format($row['hd_tongtien']); ?> đ</td>
                                        <td class="text-center pe-4">
                                            <a href="order_details.php?id=<?php echo (int) $row['hd_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">Xem</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-box-open fa-2x mb-3 text-light"></i><br>Không có giao dịch nào trong khoảng thời gian này.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="admin-live-orders-config"
     data-endpoint="orders_poll.php?scope=dashboard"
     data-scope="dashboard"
     data-from="<?php echo e($from_date); ?>"
     data-to="<?php echo e($to_date); ?>"
     data-poll-interval="4000"
     data-latest-order-id="<?php echo (int) $orderModel->getLatestOnlineOrderId(); ?>"></div>
<script src="../assets/js/main.js"></script>
</body>
</html>