<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<?php $currentAdminPage = 'orders'; ?>
<body class="backoffice-body">
<?php include base_path('admin/includes/header.php'); ?>

<div class="container backoffice-page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-secondary m-0"><i class="fas fa-file-invoice"></i> QUẢN LÝ ĐƠN HÀNG</h3>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?php echo e($flashError); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach (order_status_options() as $value => $label): ?>
                            <option value="<?php echo e($value); ?>" <?php echo ($currentStatus === $value) ? 'selected' : ''; ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" name="from" class="form-control" value="<?php echo e($fromDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="<?php echo e($toDate); ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-grow-1">Lọc</button>
                    <a href="orders.php" class="btn btn-outline-secondary">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="table-secondary align-middle">
                <tr>
                    <th class="text-center">Mã HĐ</th>
                    <th>Ngày giờ</th>
                    <th>Nguồn đơn</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Tổng tiền</th>
                    <th class="text-center">Hình thức</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody id="admin-orders-tbody" class="align-middle">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $row): ?>
                    <tr>
                        <td class="text-center fw-bold">#<?php echo (int) $row['hd_id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['hd_ngaylap'])); ?></td>
                        <td><?php echo e($row['nguon_don']); ?></td>
                        <td class="text-center">
                            <span class="badge <?php echo e(order_status_badge($row['hd_trangthai'] ?? 'dang_xu_ly')); ?> px-3 py-2">
                                <?php echo e($row['hd_trangthai_hienthi']); ?>
                            </span>
                        </td>
                        <td class="fw-bold text-success"><?php echo number_format($row['hd_tongtien']); ?> đ</td>
                        <td class="text-center"><span class="badge bg-info text-dark px-3 py-2"><?php echo e($row['hd_hinhthuctt']); ?></span></td>
                        <td class="text-center"><a href="order_details.php?id=<?php echo (int) $row['hd_id']; ?>" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-eye"></i> Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="admin-live-orders-config"
     data-endpoint="orders_poll.php"
     data-scope="orders"
     data-status="<?php echo e($currentStatus); ?>"
     data-from="<?php echo e($fromDate); ?>"
     data-to="<?php echo e($toDate); ?>"
     data-poll-interval="4000"
     data-latest-order-id="<?php echo (int) (empty($orders) ? 0 : max(array_map(static fn($order) => (int) ($order['hd_id'] ?? 0), $orders))); ?>"></div>
<script src="../assets/js/main.js"></script>
</body>
</html>