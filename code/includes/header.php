<?php
$cart_count = 0;
if (isset($_SESSION['client_cart'])) {
    foreach ($_SESSION['client_cart'] as $item) {
        $cart_count += $item['sl'];
    }
}

$customerNotifications = [];
$customerNotificationUnread = 0;
if (isset($_SESSION['kh_id'])) {
    $notificationModel = new App\Models\NotificationModel();
    $customerNotifications = $notificationModel->getCustomerNotifications((int) $_SESSION['kh_id'], 8);
    $customerNotificationUnread = $notificationModel->getUnreadCount((int) $_SESSION['kh_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaMart - Your Home of Finds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $stylePath): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<header class="ecommerce-header pb-3">
    <div class="top-navbar py-2 border-bottom border-light border-opacity-25">
        <div class="container d-flex justify-content-end align-items-center">
            <div>
                <div class="dropdown d-inline-block me-3 notification-dropdown-wrapper">
                    <a href="#" id="customer-notification-toggle" class="notification-toggle text-white text-decoration-none d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell me-1"></i>Thông báo
                        <span id="customer-notification-badge" class="badge rounded-pill bg-danger ms-2<?php echo empty($customerNotificationUnread) ? ' d-none' : ''; ?>"><?php echo (int) $customerNotificationUnread; ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2 p-0 notification-menu" id="customer-notification-menu">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <span class="fw-bold text-dark">Thông báo</span>
                            <button type="button" id="customer-mark-notifications-read" class="btn btn-link btn-sm text-decoration-none p-0<?php echo empty($customerNotifications) ? ' d-none' : ''; ?>">Đánh dấu đã xem</button>
                        </div>
                        <div class="notification-list" id="customer-notification-list">
                            <?php if (!empty($customerNotifications)): ?>
                                <?php foreach ($customerNotifications as $tb): ?>
                                    <?php $notificationLink = !empty($tb['hd_id']) ? ('order_detail.php?id=' . (int) $tb['hd_id']) : 'profile.php'; ?>
                                    <a href="<?php echo e($notificationLink); ?>" class="dropdown-item px-3 py-3 border-bottom notification-item <?php echo empty($tb['tb_dadoc']) ? 'notification-item-unread' : ''; ?>">
                                        <div class="small text-dark mb-1"><?php echo e($tb['tb_noidung']); ?></div>
                                        <div class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($tb['tb_thoigian'])); ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="px-3 py-4 text-center text-muted small">Chưa có thông báo nào.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <a href="#" class="me-3"><i class="fas fa-question-circle me-1"></i>Hỗ trợ</a>
                <?php if (isset($_SESSION['kh_id'])): ?>
                    <span class="dropdown">
                        <a href="#" class="dropdown-toggle text-white text-decoration-none d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <?php if (!empty($_SESSION['kh_avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['kh_avatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="rounded-circle me-2" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,0.6);">
                            <?php else: ?>
                                <span class="avatar-sm rounded-circle bg-secondary text-white px-2 py-1 me-1 fs-6"><?php echo strtoupper(substr($_SESSION['kh_hoten'], 0, 1)); ?></span>
                            <?php endif; ?>
                            <?php echo $_SESSION['kh_hoten']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li><a class="dropdown-item text-dark" href="profile.php"><i class="fas fa-user-circle me-2 text-muted"></i>Tài khoản của tôi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout_member.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </span>
                <?php else: ?>
                    <a href="register.php" class="me-3 fw-bold">Đăng Ký</a>
                    <span class="me-3 opacity-50">|</span>
                    <a href="login_member.php" class="fw-bold">Đăng Nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-3 col-12 mb-3 mb-md-0 text-center text-md-start">
                <a href="index.php" class="text-decoration-none d-flex align-items-center justify-content-center justify-content-md-start">
                    <img src="assets/img/logo.jpg" alt="NovaMart Logo" class="img-fluid" style="max-height: 50px; border-radius: 8px;">
                    <span class="ms-2 fs-2 fw-bold text-white" style="letter-spacing: 1px; font-family: 'Nunito', sans-serif;">NovaMart</span>
                </a>
            </div>

            <div class="col-lg-7 col-md-7 col-10">
                <form action="index.php" method="GET" class="search-wrapper w-100 rounded-pill shadow-sm pe-1">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 rounded-pill ps-3 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="q" class="form-control border-0 shadow-none bg-transparent" placeholder="TÌM KIẾM SẢN PHẨM, DANH MỤC..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button class="search-btn rounded-pill m-1 px-4 fw-bold" type="submit">Tìm kiếm</button>
                    </div>
                </form>
            </div>

            <div class="col-lg-2 col-md-2 col-2 text-center text-md-end">
                <a href="<?php echo isset($_SESSION['kh_id']) ? 'cart.php' : 'login_member.php'; ?>"
                   class="position-relative d-inline-block p-2 text-white fs-3 text-decoration-none cart-icon-wrapper">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-white text-brand border border-white px-2 py-1 shadow-sm"
                          style="font-size: 0.75rem; color: var(--brand-color); <?php echo ($cart_count > 0) ? '' : 'display: none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
<?php if (isset($_SESSION['kh_id'])): ?>
<div id="customer-notification-config"
     data-endpoint="notifications_poll.php"
     data-mark-read-endpoint="notifications_mark_read.php"
     data-poll-interval="4000"
     hidden></div>
<?php endif; ?>

</header>
