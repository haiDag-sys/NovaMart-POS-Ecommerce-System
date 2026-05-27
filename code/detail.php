<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$sp_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (isset($_SESSION['kh_id'])) {
        $kh_id = intval($_SESSION['kh_id']);
        $dg_sao = intval($_POST['dg_sao']);
        $dg_noidung = mysqli_real_escape_string($conn, trim($_POST['dg_noidung']));
        
        if (!empty($dg_noidung) && $dg_sao >= 1 && $dg_sao <= 5) {
            $insert_sql = "INSERT INTO danh_gia (sp_id, kh_id, dg_sao, dg_noidung) 
                           VALUES ('$sp_id', '$kh_id', '$dg_sao', '$dg_noidung')";
            $conn->query($insert_sql);
            
            header("Location: detail.php?id=$sp_id");
            exit();
        }
    }
}

$sql = "SELECT * FROM san_pham WHERE sp_id = $sp_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location='index.php';</script>";
    exit();
}
$row = $result->fetch_assoc();

$avg_sql = "SELECT AVG(dg_sao) as avg_sao, COUNT(dg_id) as total_dg FROM danh_gia WHERE sp_id = $sp_id";
$avg_result = $conn->query($avg_sql)->fetch_assoc();
$total_dg = $avg_result['total_dg'];
$avg_sao = $total_dg > 0 ? round($avg_result['avg_sao'], 1) : 5.0;

$reviews_sql = "SELECT dg.*, kh.kh_hoten 
                FROM danh_gia dg
                JOIN khach_hang kh ON dg.kh_id = kh.kh_id
                WHERE dg.sp_id = $sp_id 
                ORDER BY dg.dg_thoigian DESC";
$reviews_result = $conn->query($reviews_sql);

if (!empty($row['sp_hinhanh'])) {
    $main_img = $row['sp_hinhanh'];
} else {
    $folder_name = "SP" . str_pad($row['sp_id'], 2, '0', STR_PAD_LEFT); 
    $folder_path = "assets/uploads/" . $folder_name . "/";
    $images = glob($folder_path . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);
    $main_img = !empty($images) ? $images[0] : 'assets/img/default-product.png';
}
$da_ban = isset($row['sp_daban']) ? $row['sp_daban'] : 0;

include 'includes/header.php';
?>

<style>

.star-rating { direction: rtl; display: inline-block; }
.star-rating input[type=radio] { display: none; }
.star-rating label { color: #ddd; font-size: 28px; padding: 0 2px; cursor: pointer; transition: color 0.2s; }
.star-rating input[type=radio]:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color: #ffc107; }
</style>

<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $row['sp_ten']; ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
        <div class="row g-0">
            <div class="col-md-5 p-4 text-center d-flex align-items-center justify-content-center" style="background-color: #f8f9fa;">
                <img src="<?php echo $main_img; ?>" class="img-fluid rounded-3 shadow-sm" alt="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height: 400px; object-fit: cover;">
            </div>
            
            <div class="col-md-7 p-4 p-md-5">
                <h2 class="fw-bold text-dark mb-3"><?php echo $row['sp_ten']; ?></h2>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <?php 

                        for($i=1; $i<=5; $i++) {
                            if($i <= $avg_sao) echo '<i class="fas fa-star"></i>';
                            elseif($i - 0.5 <= $avg_sao) echo '<i class="fas fa-star-half-alt"></i>';
                            else echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <span class="text-muted border-end pe-3 me-3 fw-bold"><?php echo $avg_sao; ?>/5</span>
                    <span class="text-muted border-end pe-3 me-3"><?php echo $total_dg; ?> Đánh giá</span>
                    <span class="text-muted">Đã bán <b><?php echo number_format($da_ban, 0, ',', '.'); ?></b></span>
                </div>

                <div class="bg-light p-3 rounded-3 mb-4">
                    <h2 class="fw-bold mb-0" style="color: var(--brand-color);">
                        <?php echo number_format($row['sp_giaban'], 0, ',', '.'); ?> đ
                    </h2>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark">Mô tả sản phẩm:</h6>
                    <p class="text-muted lh-lg">
                        <?php echo !empty($row['sp_mota']) ? nl2br($row['sp_mota']) : "Sản phẩm đang được cập nhật mô tả chi tiết..."; ?>
                    </p>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <h6 class="fw-bold text-dark me-3 mb-0">Tình trạng:</h6>
                    <?php if((float) $row['sp_tonkho'] > 0): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2">Còn hàng (<?php echo format_quantity($row['sp_tonkho']); ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-danger rounded-pill px-3 py-2">Hết hàng</span>
                    <?php endif; ?>
                </div>

                <?php if((float) $row['sp_tonkho'] > 0): ?>
                <div class="d-flex gap-3 mt-4">
                    <button type="button" class="btn text-white fw-bold px-5 py-3 rounded-3 shadow-sm add-to-cart-btn w-100 fs-5" 
                            style="background-color: var(--brand-color); transition: all 0.3s;"
                            data-id="<?php echo $row['sp_id']; ?>"
                            data-ten="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-gia="<?php echo $row['sp_giaban']; ?>"
                            data-hinh="<?php echo $main_img; ?>">
                        <i class="fas fa-cart-plus me-2"></i> THÊM VÀO GIỎ HÀNG
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex gap-4 text-muted small">
                        <span><i class="fas fa-truck text-success me-1"></i> Giao hàng siêu tốc 2H</span>
                        <span><i class="fas fa-shield-alt text-primary me-1"></i> Cam kết 100% tươi sạch</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
        <h4 class="fw-bold mb-4 border-bottom pb-3"><i class="far fa-comments me-2 text-muted"></i>Đánh giá sản phẩm</h4>
        
        <div class="row">
            <div class="col-lg-5 mb-5 mb-lg-0 pe-lg-5">
                <h6 class="fw-bold mb-3">Gửi nhận xét của bạn</h6>
                
                <?php if(isset($_SESSION['kh_id'])): ?>
                    <form action="" method="POST" class="bg-light p-4 rounded-4">
                        <div class="mb-3 text-center">
                            <label class="form-label text-dark fw-bold mb-0">Bạn chấm sản phẩm này mấy sao?</label><br>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="dg_sao" value="5" checked /><label for="star5" title="5 sao"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="dg_sao" value="4" /><label for="star4" title="4 sao"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="dg_sao" value="3" /><label for="star3" title="3 sao"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="dg_sao" value="2" /><label for="star2" title="2 sao"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="dg_sao" value="1" /><label for="star1" title="1 sao"><i class="fas fa-star"></i></label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <textarea name="dg_noidung" class="form-control border-0 shadow-sm rounded-3" rows="4" placeholder="Chia sẻ cảm nhận của bạn về chất lượng sản phẩm..." required></textarea>
                        </div>
                        
                        <button type="submit" name="submit_review" class="btn text-white w-100 rounded-pill py-2 fw-bold shadow-sm" style="background-color: var(--accent-green);">
                            Gửi Đánh Giá
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert text-center p-4 rounded-4" style="background-color: #f8f9fa; border: 1px dashed #ccc;">
                        <i class="fas fa-lock fs-1 text-muted mb-3 opacity-50"></i>
                        <h6 class="fw-bold text-dark">Bạn cần đăng nhập</h6>
                        <p class="text-muted small mb-3">Vui lòng đăng nhập để có thể để lại đánh giá cho sản phẩm này.</p>
                        <a href="login_member.php" class="btn btn-outline-dark rounded-pill fw-bold px-4">Đăng Nhập Ngay</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-7 border-start-lg ps-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Khách hàng nói gì (<?php echo $total_dg; ?>)</h6>
                </div>
                
                <?php if($reviews_result->num_rows > 0): ?>
                    <div class="review-list pe-2" style="max-height: 500px; overflow-y: auto;">
                        <?php while($rv = $reviews_result->fetch_assoc()): ?>
                            <div class="mb-4 border-bottom pb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle text-white d-flex justify-content-center align-items-center me-2 fw-bold" style="width: 35px; height: 35px; background-color: var(--brand-color);">
                                            <?php echo strtoupper(substr($rv['kh_hoten'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold m-0 text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($rv['kh_hoten']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><i class="far fa-clock me-1"></i><?php echo date('H:i - d/m/Y', strtotime($rv['dg_thoigian'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-warning my-2" style="font-size: 0.85rem;">
                                    <?php 
                                    for($i=1; $i<=5; $i++){
                                        if($i <= $rv['dg_sao']) echo '<i class="fas fa-star"></i>';
                                        else echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <p class="text-dark m-0 bg-light p-3 rounded-3" style="font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($rv['dg_noidung'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 bg-light rounded-4 border-0">
                        <i class="far fa-comment-dots fs-1 text-muted mb-3 opacity-25"></i>
                        <p class="text-muted m-0 fw-medium">Sản phẩm này chưa có đánh giá nào.<br>Hãy là người đầu tiên nhận xét nhé!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>