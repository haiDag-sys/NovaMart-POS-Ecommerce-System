<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="profile.php" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách đơn hàng
                </a>
            </div>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?php echo e($flashError); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" style="color: var(--brand-color);">Chi Tiết Đơn Hàng #<?php echo (int) $order['hd_id']; ?></h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge <?php echo e(order_status_badge($order['hd_trangthai'] ?? 'dang_xu_ly')); ?> px-3 py-2"><?php echo e($order['hd_trangthai_hienthi']); ?></span>
                        <?php if (isset($orderModel) && $orderModel->canCustomerCancelOrder($order['hd_trangthai'] ?? 'dang_xu_ly')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">Hủy đơn</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row mb-4 bg-light p-3 rounded gy-3">
                        <div class="col-sm-6">
                            <p class="mb-1 text-muted small">Ngày đặt hàng:</p>
                            <p class="mb-0 fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['hd_ngaylap'])); ?></p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <p class="mb-1 text-muted small">Hình thức thanh toán:</p>
                            <p class="mb-0 fw-bold"><?php echo mb_strtoupper($order['hd_hinhthuctt'], 'UTF-8'); ?></p>
                        </div>
                        <div class="col-12">
                            <p class="mb-1 text-muted small">Địa chỉ nhận hàng:</p>
                            <p class="mb-0 fw-bold"><?php echo e($order['hd_diachinhan'] ?? ($order['kh_diachi'] ?? 'Chưa có địa chỉ nhận hàng')); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($order['hd_ghichu'])): ?>
                    <div class="alert alert-info">
                        <div class="fw-bold mb-1">Ghi chú đơn hàng</div>
                        <div><?php echo nl2br(e($order['hd_ghichu'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <h6 class="fw-bold mb-3">Sản phẩm đã mua</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle border-bottom">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo e($item['sp_ten']); ?></div>
                                        <div class="text-muted small">Đơn giá: <?php echo number_format($item['cthd_dongia']); ?> đ</div>
                                    </td>
                                    <td class="text-center"><?php echo format_quantity($item['cthd_soluong']); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($item['cthd_thanhtien']); ?> đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <div style="width: 250px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính:</span>
                                <span class="fw-bold"><?php echo number_format($order['hd_tongtien']); ?> đ</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="fw-bold fs-5">Tổng cộng:</span>
                                <span class="fw-bold fs-4 text-danger"><?php echo number_format($order['hd_tongtien']); ?> đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (isset($orderModel) && $orderModel->canCustomerCancelOrder($order['hd_trangthai'] ?? 'dang_xu_ly')): ?>
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Hủy đơn hàng #<?php echo (int) $order['hd_id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="hd_id" value="<?php echo (int) $order['hd_id']; ?>">
                    <label class="form-label fw-bold">Lý do hủy</label>
                    <textarea name="cancel_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="cancel_order" class="btn btn-danger">Xác nhận hủy đơn</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>