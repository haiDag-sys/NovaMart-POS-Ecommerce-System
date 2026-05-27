<?php
$pageStyles = ['assets/css/pages/profile.css'];
$pageScripts = ['assets/js/pages/profile.js'];
include 'includes/header.php';
?>

<div class="container my-5">
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?php echo e($flashError); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 rounded">
                <div class="card-body text-center p-4 border-bottom">
                    <form action="" method="POST" enctype="multipart/form-data" id="form-avatar" class="mb-3">
                        <div id="avatar-trigger" class="avatar-wrapper shadow-sm border border-3 border-white" title="Bấm để đổi ảnh đại diện">
                            <?php
                                $avatarHienTai = (!empty($_SESSION['kh_avatar']))
                                    ? $_SESSION['kh_avatar']
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['kh_hoten']) . '&background=random';
                            ?>
                            <img src="<?php echo e($avatarHienTai); ?>" class="avatar-img" alt="Avatar">

                            <div class="avatar-overlay">
                                <div class="position-relative text-white icon-group">
                                    <i class="fas fa-camera fa-2x"></i>
                                    <i class="fas fa-plus position-absolute" style="font-size: 0.8rem; top: -5px; right: -8px; background-color: #fd7e14; border-radius: 50%; padding: 3px;"></i>
                                </div>
                            </div>
                        </div>

                        <input type="file" name="avatar_upload" id="file-avatar" class="d-none" accept="image/png, image/jpeg, image/jpg">
                    </form>

                    <h5 class="card-title mb-0 fw-bold"><?php echo e($_SESSION['kh_hoten']); ?></h5>
                    <p class="text-muted small mt-1 mb-0">Khách hàng thành viên</p>
                </div>

                <div class="list-group list-group-flush rounded-bottom" id="accountTabs" role="tablist">
                    <a class="list-group-item list-group-item-action active fw-bold py-3" id="thong-tin-tab" data-bs-toggle="tab" href="#thong-tin" role="tab">
                        <i class="fas fa-user-circle me-2"></i> Hồ sơ của tôi
                    </a>
                    <a class="list-group-item list-group-item-action fw-bold py-3" id="don-hang-tab" data-bs-toggle="tab" href="#don-hang" role="tab">
                        <i class="fas fa-file-invoice-dollar me-2 text-primary"></i> Đơn mua
                    </a>
                    <a class="list-group-item list-group-item-action fw-bold py-3" id="mat-khau-tab" data-bs-toggle="tab" href="#mat-khau" role="tab">
                        <i class="fas fa-key me-2 text-warning"></i> Đổi mật khẩu
                    </a>
                    <a href="logout_member.php" class="list-group-item list-group-item-action fw-bold py-3 text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm border-0 rounded">
                <div class="card-body p-4 p-md-5 tab-content" id="accountTabsContent">
                    <div class="tab-pane fade show active" id="thong-tin" role="tabpanel">
                        <h4 class="mb-1 fw-bold" style="color: var(--brand-color);">Hồ Sơ Của Tôi</h4>
                        <p class="text-muted small">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                        <hr class="mb-4">
                        <form action="" method="POST">
                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label fw-bold text-muted">Họ và Tên</label>
                                <div class="col-sm-9">
                                    <input type="text" name="kh_hoten" class="form-control" value="<?php echo e($customer['kh_hoten'] ?? $_SESSION['kh_hoten']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-3 col-form-label fw-bold text-muted">Số điện thoại</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" value="<?php echo e($customer['kh_sdt'] ?? ''); ?>" disabled>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label class="col-sm-3 col-form-label fw-bold text-muted">Địa chỉ</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" rows="3" name="kh_diachi"><?php echo e($customer['kh_diachi'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="save_profile" class="btn btn-primary px-4 fw-bold">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="don-hang" role="tabpanel">
                        <h4 class="mb-4 fw-bold text-dark">Lịch Sử Đơn Mua</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã Đơn</th>
                                        <th>Ngày Lập</th>
                                        <th>Tổng Tiền</th>
                                        <th>Thanh Toán</th>
                                        <th>Trạng Thái</th>
                                        <th>Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($orders)): ?>
                                        <?php foreach ($orders as $don): ?>
                                        <tr>
                                            <td><strong>#<?php echo (int) $don['hd_id']; ?></strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($don['hd_ngaylap'])); ?></td>
                                            <td class="text-danger fw-bold"><?php echo number_format($don['hd_tongtien']); ?> đ</td>
                                            <td><?php echo e(mb_strtoupper($don['hd_hinhthuctt'] ?? 'Tiền mặt', 'UTF-8')); ?></td>
                                            <td>
                                                <span class="badge <?php echo e(order_status_badge($don['hd_trangthai'] ?? 'dang_xu_ly')); ?>">
                                                    <?php echo e($don['hd_trangthai_hienthi']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                <a href="order_detail.php?id=<?php echo (int) $don['hd_id']; ?>" class="btn btn-sm btn-outline-info">Chi tiết</a>
                                                <?php if (isset($orderModel) && $orderModel->canCustomerCancelOrder($don['hd_trangthai'] ?? 'dang_xu_ly')): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?php echo (int) $don['hd_id']; ?>">Hủy đơn</button>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (isset($orderModel) && $orderModel->canCustomerCancelOrder($don['hd_trangthai'] ?? 'dang_xu_ly')): ?>
                                            <div class="modal fade" id="cancelOrderModal<?php echo (int) $don['hd_id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content border-0 shadow">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Hủy đơn hàng #<?php echo (int) $don['hd_id']; ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="hd_id" value="<?php echo (int) $don['hd_id']; ?>">
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
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fs-1 mb-3"></i><br>
                                                Bạn chưa có đơn hàng nào.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="mat-khau" role="tabpanel">
                        <h4 class="mb-4 fw-bold text-dark">Đổi Mật Khẩu</h4>
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" required>
                                <div class="form-text">Ít nhất 6 ký tự, gồm cả chữ và số.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" name="change_password" class="btn btn-warning fw-bold px-4">Cập nhật mật khẩu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>