<?php
session_start();
include '../includes/db.php';
include '../includes/order_control.php';

require_staff();

$user_id = $_SESSION['user_id'];
$vai_tro = $_SESSION['role'];
$ten_nguoi_truc = isset($_SESSION['hoten']) ? $_SESSION['hoten'] : 'Nhân viên';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_POST['add_to_cart'])) {
    $id = intval($_POST['sp_id']);
    $ten = $_POST['sp_ten'];
    $gia = floatval($_POST['sp_giaban']);
    
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['sl']++;
    } else {
        $_SESSION['cart'][$id] = ['ten' => $ten, 'gia' => $gia, 'sl' => 1];
    }

    $query_string = "";
    if (!empty($_GET)) {
        $params = $_GET; 
        unset($params['action']); 
        if (!empty($params)) {
            $query_string = "?" . http_build_query($params);
        }
    }
    
    header("Location: pos.php" . $query_string);
    exit();
}

if (isset($_POST['update_qty'])) {
    $id = intval($_POST['id']);
    $qty = intval($_POST['qty']);
    if ($qty > 0) {
        $_SESSION['cart'][$id]['sl'] = $qty;
    } else {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: pos.php"); exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    unset($_SESSION['cart'][$_GET['id']]);
    header("Location: pos.php"); exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['cart'] = [];
    header("Location: pos.php"); exit();
}

$success_hd_id = 0;
$pay_error_message = '';
if (isset($_POST['pay'])) {
    if (!empty($_SESSION['cart'])) {
        $tong_tien = 0;
        foreach ($_SESSION['cart'] as $item) {
            $tong_tien += $item['gia'] * $item['sl'];
        }
        $nv_id_lap_hd = $user_id;
        $kh_id_mua = NULL;

        $ket_qua = taoHoaDonVaTruKho($conn, $_SESSION['cart'], $tong_tien, 'Tiền mặt', $kh_id_mua, $nv_id_lap_hd, 'hoan_thanh');

        if (is_numeric($ket_qua)) {
            $_SESSION['cart'] = [];
            $success_hd_id = $ket_qua;
        } else {
            $pay_error_message = (string) $ket_qua;
        }
    }
}

$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : "";
$keyword = "%$q%";

$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM san_pham WHERE sp_ten LIKE ?");
$stmt_count->bind_param("s", $keyword); 
$stmt_count->execute();
$total_records = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$stmt_list = $conn->prepare("SELECT * FROM san_pham WHERE sp_ten LIKE ? ORDER BY sp_id DESC LIMIT ?, ?");
$stmt_list->bind_param("sii", $keyword, $offset, $limit);
$stmt_list->execute();
$result = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>POS Nhân Viên - NovaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        body { background-color: #f0f2f5; height: 100vh; overflow: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-container { height: calc(100vh - 80px); }
        .product-card { 
            transition: all 0.2s; border: none; cursor: pointer; 
            background: white; border-radius: 12px; overflow: hidden; position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .img-container { height: 130px; display: flex; align-items: center; justify-content: center; background: #fff; padding: 5px; }
        .product-img { max-height: 100%; max-width: 100%; object-fit: contain; }
        
        .badge-stock { position: absolute; top: 8px; right: 8px; font-size: 0.65rem; padding: 4px 8px; border-radius: 50px; opacity: 0.9; }
        .cart-area { display: flex; flex-direction: column; height: 100%; background: white; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .cart-list { flex-grow: 1; overflow-y: auto; }
        .qty-input { width: 35px; text-align: center; border: none; font-weight: bold; background: transparent; }
        .pos-logout-btn { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 0; flex: 0 0 44px; }
        .pos-logout-btn:focus, .pos-logout-btn:active, .pos-logout-btn:focus-visible { outline: none !important; box-shadow: none !important; }
        @media print { body * { visibility: hidden; } #invoice-print, #invoice-print * { visibility: visible; } #invoice-print { position: absolute; left: 0; top: 0; width: 100%; } }
    </style>
</head>
<body>

    <div class="bg-white shadow-sm p-2 mb-3 px-4 d-flex justify-content-between align-items-center" style="height: 70px;">
        <div class="d-flex align-items-center">
            <img src="../assets/img/logo.jpg" alt="Logo" style="height: 35px; border-radius: 5px; object-fit: cover;" class="me-2" onerror="this.src='https://ui-avatars.com/api/?name=NovaMart&background=ff6600&color=fff'">
            <h4 class="m-0 fw-bold me-3" style="color: #ff6600;">NovaMart</h4>
            <div class="vr mx-2"></div>
            <span class="fw-bold text-dark small">
                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($ten_nguoi_truc); ?> 
            </span>
        </div>
        <div class="d-flex gap-2">
            <form action="" method="GET" class="d-flex border rounded-pill overflow-hidden bg-light" style="width: 350px;">
                <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none ps-3" placeholder="Tìm tên sản phẩm..." value="<?php echo htmlspecialchars($q); ?>">
                <button type="submit" class="btn border-0" style="color: #ff6600;"><i class="fas fa-search"></i></button>
            </form>
            
            <a href="../admin/logout.php" class="btn btn-outline-danger rounded-circle pos-logout-btn" data-confirm-message="Thoát?"><i class="fas fa-power-off"></i></a>
        </div>
    </div>

    <div class="container-fluid px-4 main-container">
        <div class="row h-100 pb-3">
            <div class="col-md-8 d-flex flex-column h-100">
                <div class="flex-grow-1 overflow-auto pe-2">
                    <div class="row g-3">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <div class="col-xl-3 col-lg-4 col-md-4 col-6">
                                <form method="POST" action="" class="h-100">
                                    <div class="card product-card h-100 js-product-submit">
                                        <span class="badge <?php echo $row['sp_tonkho'] > 5 ? 'bg-success' : 'bg-danger'; ?> badge-stock">Tồn: <?php echo format_quantity($row['sp_tonkho']); ?></span>
                                        
                                        <div class="img-container">
                                            <?php
                                            if (!empty($row['sp_hinhanh'])) {
                                                $src = '../' . ltrim($row['sp_hinhanh'], '/');
                                            } else {
                                                $folder_id = "SP" . str_pad($row['sp_id'], 2, '0', STR_PAD_LEFT);
                                                $path = "../assets/uploads/" . $folder_id . "/";
                                                $images = glob($path . "*.{jpg,jpeg,png,webp,PNG,JPG}", GLOB_BRACE);
                                                $src = !empty($images) ? $images[0] : '../assets/img/logo.jpg';
                                            }
                                            ?>
                                            <img src="<?php echo $src; ?>" class="product-img" onerror="this.src='../assets/img/logo.jpg'">
                                        </div>

                                        <div class="card-body text-center p-2">
                                            <div class="fw-bold text-truncate small mb-1" title="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['sp_ten']); ?></div>
                                            <div class="text-danger fw-bold"><?php echo number_format($row['sp_giaban']); ?> đ</div>
                                        </div>
                                        
                                        <input type="hidden" name="sp_id" value="<?php echo $row['sp_id']; ?>">
                                        <input type="hidden" name="sp_ten" value="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="sp_giaban" value="<?php echo $row['sp_giaban']; ?>">
                                        <input type="hidden" name="add_to_cart" value="1">
                                    </div>
                                </form>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted pt-5">
                                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                <p>Không tìm thấy sản phẩm nào khớp với từ khóa.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-center pt-3 mt-auto">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 shadow-sm">
                           <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i==$page)?'active':''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo htmlspecialchars($q); ?>"><?php echo $i; ?></a>
                                </li>
                           <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4 h-100">
                <div class="cart-area">
                    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center rounded-top">
                        <h5 class="m-0 fw-bold"><i class="fas fa-shopping-cart text-primary me-2"></i> ĐƠN HÀNG</h5>
                        <a href="pos.php?action=clear" class="btn btn-sm btn-outline-danger border-0 fw-bold" data-confirm-message="Xóa giỏ hàng?">XÓA HẾT</a>
                    </div>
                    
                    <div class="cart-list p-0">
                        <table class="table table-hover m-0">
                            <tbody class="border-top-0">
                                <?php 
                                $tong_cong = 0;
                                if (!empty($_SESSION['cart'])): 
                                    foreach ($_SESSION['cart'] as $id => $item):
                                        $tt = $item['gia'] * $item['sl'];
                                        $tong_cong += $tt;
                                ?>
                                <tr>
                                    <td class="ps-3 align-middle" style="width: 50%;">
                                        <div class="fw-bold small text-truncate"><?php echo $item['ten']; ?></div>
                                        <small class="text-muted"><?php echo number_format($item['gia']); ?> đ</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <form action="" method="POST" class="d-flex align-items-center justify-content-center border rounded-pill p-1">
                                            <input type="hidden" name="update_qty" value="1">
                                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                                            <button type="submit" name="qty" value="<?php echo $item['sl']-1; ?>" class="btn btn-link btn-sm p-0 text-decoration-none text-dark"><i class="fas fa-minus-circle"></i></button>
                                            <input type="text" value="<?php echo $item['sl']; ?>" class="qty-input" readonly>
                                            <button type="submit" name="qty" value="<?php echo $item['sl']+1; ?>" class="btn btn-link btn-sm p-0 text-decoration-none text-dark"><i class="fas fa-plus-circle"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-end align-middle pe-3 fw-bold text-primary small">
                                        <?php echo number_format($tt); ?>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="3" class="text-center py-5 text-muted opacity-50"><i class="fas fa-shopping-basket fa-3x mb-2"></i><br>Chưa có sản phẩm</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 border-top bg-white rounded-bottom shadow-lg">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-muted">TỔNG CỘNG:</span>
                            <span class="h3 m-0 text-danger fw-bold"><?php echo number_format($tong_cong); ?> <small>đ</small></span>
                        </div>
                        <form method="POST" action="">
                            <button type="submit" name="pay" class="btn btn-primary w-100 py-3 fw-bold shadow" style="background-color: #ff6600; border: none;" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check-double me-2"></i> XUẤT HÓA ĐƠN
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body p-4" id="invoice-print">
                    <div class="text-center mb-3">
                        <h4 class="fw-bold m-0">NovaMart</h4>
                        <small>HÓA ĐƠN BÁN LẺ</small><br>
                        <small>Số HĐ: #<?php echo $success_hd_id; ?></small>
                    </div>
                    <hr class="border-dashed">
                    <table class="table table-sm table-borderless small">
                        <?php 
                        $invoice_delivery_address = 'Mua tại quầy';
                        if($success_hd_id > 0) {
                            $stmt_info = $conn->prepare("SELECT hd_diachinhan FROM hoa_don WHERE hd_id = ? LIMIT 1");
                            if ($stmt_info) {
                                $stmt_info->bind_param("i", $success_hd_id);
                                $stmt_info->execute();
                                $row_info = $stmt_info->get_result()->fetch_assoc();
                                if (!empty($row_info['hd_diachinhan'])) {
                                    $invoice_delivery_address = $row_info['hd_diachinhan'];
                                }
                                $stmt_info->close();
                            }

                            $stmt_bill = $conn->prepare("SELECT b.sp_ten, a.cthd_soluong, a.cthd_thanhtien FROM ct_hoa_don a JOIN san_pham b ON a.sp_id = b.sp_id WHERE a.hd_id = ?");
                            $stmt_bill->bind_param("i", $success_hd_id);
                            $stmt_bill->execute();
                            $res_bill = $stmt_bill->get_result();
                            $total_print = 0;
                            while($rb = $res_bill->fetch_assoc()){
                                $total_print += $rb['cthd_thanhtien'];
                                $qtyText = format_quantity($rb['cthd_soluong']);
                                echo "<tr>
                                    <td>{$rb['sp_ten']} x{$qtyText}</td>
                                    <td class='text-end'>" . number_format($rb['cthd_thanhtien']) . "</td>
                                </tr>";
                            }
                        }
                        ?>
                    </table>
                    <div class="small mt-2 mb-3"><strong>Địa chỉ nhận:</strong> <?php echo htmlspecialchars($invoice_delivery_address ?? 'Mua tại quầy'); ?></div>
                    <hr class="border-dashed">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>TỔNG:</span>
                        <span><?php echo number_format($total_print ?? 0); ?> đ</span>
                    </div>
                    <div class="text-center mt-4 small">Cảm ơn quý khách!</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary w-100" style="background-color: #ff6600; border: none;" data-print-window="true">In ngay</button>
                </div>
            </div>
        </div>
    </div>

    </div>
    <?php if (!empty($pay_error_message)): ?>
    <div id="pos-page-config" data-pay-error-message="<?php echo htmlspecialchars($pay_error_message, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($success_hd_id > 0): ?> data-open-invoice-modal="1"<?php endif; ?> hidden></div>
    <?php else: ?>
    <div id="pos-page-config"<?php if ($success_hd_id > 0): ?> data-open-invoice-modal="1"<?php endif; ?> hidden></div>
    <?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/staff/pos.js"></script>
</body>
</html>