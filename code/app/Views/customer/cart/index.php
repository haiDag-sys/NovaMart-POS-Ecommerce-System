<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-shopping-basket text-primary"></i> Giỏ hàng của bạn</h3>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Tiếp tục mua sắm</a>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?php echo e($flashError); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-0">
                    <table class="table table-hover m-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Sản phẩm</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center" width="120">Số lượng</th>
                                <th class="text-end pe-4">Thành tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($cartItems)): ?>
                                <?php foreach ($cartItems as $id => $item): ?>
                                    <?php $thanhTien = ((float) $item['gia']) * ((int) $item['sl']); ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo isset($item['hinh']) ? e($item['hinh']) : ''; ?>" width="60" class="rounded border me-3" onerror="this.src='https://via.placeholder.com/60'">
                                                <span class="fw-bold text-dark"><?php echo e($item['ten']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center text-muted"><?php echo number_format($item['gia']); ?> đ</td>
                                        <td class="text-center fw-bold"><?php echo (int) $item['sl']; ?></td>
                                        <td class="text-end pe-4 fw-bold text-primary"><?php echo number_format($thanhTien); ?> đ</td>
                                        <td class="text-end pe-3">
                                            <a href="cart.php?action=remove&id=<?php echo (int) $id; ?>" class="text-danger" data-confirm-message="Xóa món này?" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Giỏ hàng đang trống.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4 position-sticky" style="top: 20px; align-self: start;">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 fw-bold">Thông tin thanh toán</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tạm tính:</span>
                        <span class="fw-bold"><?php echo number_format($tong_tien); ?> đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                        <span class="fw-bold fs-5">Tổng cộng:</span>
                        <span class="fw-bold fs-4 text-danger"><?php echo number_format($tong_tien); ?> đ</span>
                    </div>

                    <?php if (!empty($cartItems)): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Địa chỉ nhận hàng</label>
                            <textarea class="form-control" rows="3" name="dia_chi_nhan" required><?php echo e($customer['kh_diachi'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Ghi chú cho đơn hàng</label>
                            <textarea class="form-control" rows="2" name="ghi_chu"></textarea>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-primary w-100 py-2 fw-bold text-uppercase" style="background-color: var(--brand-color); border: none;">
                            Đặt Hàng Ngay
                        </button>
                        <a href="cart.php?action=clear" class="btn btn-link text-danger w-100 mt-2 text-decoration-none small" data-confirm-message="Bạn chắc chắn muốn xóa hết?">Xóa giỏ hàng</a>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>