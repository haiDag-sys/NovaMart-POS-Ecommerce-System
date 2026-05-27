<?php
session_start();
include 'includes/db.php';

$limit = 12;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$keyword = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$where = ['sp_tonkho > 0'];
$types = '';
$values = [];

if ($categoryId > 0) {
    $where[] = 'sp.lsp_id = ?';
    $types .= 'i';
    $values[] = $categoryId;
}

if ($keyword !== '') {
    $where[] = 'sp.sp_ten LIKE ?';
    $types .= 's';
    $values[] = '%' . $keyword . '%';
}

$whereClause = ' WHERE ' . implode(' AND ', $where);

$sqlCount = 'SELECT COUNT(*) AS total FROM san_pham sp' . $whereClause;
$stmtCount = $conn->prepare($sqlCount);
if ($types !== '') {
    $countValues = $values;
    $refs = [];
    foreach ($countValues as $k => $v) { $refs[$k] = &$countValues[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmtCount, 'bind_param'], $refs);
}
$stmtCount->execute();
$totalRecords = (int) ($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCount->close();
$totalPages = max(1, (int) ceil($totalRecords / $limit));

$sql = 'SELECT sp.*, lsp.lsp_ten FROM san_pham sp LEFT JOIN loai_san_pham lsp ON lsp.lsp_id = sp.lsp_id' . $whereClause . ' ORDER BY sp.sp_id DESC LIMIT ?, ?';
$stmt = $conn->prepare($sql);
$listTypes = $types . 'ii';
$listValues = $values;
$listValues[] = $offset;
$listValues[] = $limit;
$refs = [];
foreach ($listValues as $k => $v) { $refs[$k] = &$listValues[$k]; }
array_unshift($refs, $listTypes);
call_user_func_array([$stmt, 'bind_param'], $refs);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
$categoryResult = $conn->query('SELECT lsp_id, lsp_ten FROM loai_san_pham ORDER BY lsp_ten ASC');
while ($rowCat = $categoryResult->fetch_assoc()) {
    $categories[] = $rowCat;
}

$selectedCategoryName = '';
foreach ($categories as $cat) {
    if ((int) $cat['lsp_id'] === $categoryId) {
        $selectedCategoryName = $cat['lsp_ten'];
        break;
    }
}


function category_icon_meta($name) {
    $normalized = mb_strtolower(trim((string) $name), 'UTF-8');
    $map = [
        'rau củ' => ['fas fa-carrot', '#22c55e'],
        'rau cu' => ['fas fa-carrot', '#22c55e'],
        'thịt tươi' => ['fas fa-drumstick-bite', '#ef4444'],
        'thit tuoi' => ['fas fa-drumstick-bite', '#ef4444'],
        'đồ uống' => ['fas fa-wine-bottle', '#f97316'],
        'do uong' => ['fas fa-wine-bottle', '#f97316'],
        'bánh kẹo' => ['fas fa-cookie-bite', '#f59e0b'],
        'banh keo' => ['fas fa-cookie-bite', '#f59e0b'],
        'mẹ & bé' => ['fas fa-baby', '#ec4899'],
        'me & be' => ['fas fa-baby', '#ec4899'],
        'đồ gia dụng' => ['fas fa-house', '#64748b'],
        'do gia dung' => ['fas fa-house', '#64748b'],
    ];
    return $map[$normalized] ?? ['fas fa-tags', '#6366f1'];
}


include 'includes/header.php';
?>

<?php if (empty($keyword) && $page === 1 && $categoryId === 0): ?>
<div class="container mt-4">
    <div class="rounded-4 bg-white shadow-sm overflow-hidden mb-4 border-0" style="border-radius: 24px !important;">
        <div class="row g-0 align-items-center bg-white">
            <div class="col-md-7 p-5">
                <h4 class="fw-bold mb-2" style="color: var(--brand-color);">SIÊU TƯƠI SẠCH MỖI NGÀY</h4>
                <h1 class="display-5 fw-bold text-dark mb-3">Thịt Tươi & Rau Củ<br>Giao Tận Nơi</h1>
                <p class="text-muted mb-4 fs-5">Lựa chọn hàng đầu cho bữa ăn gia đình bạn.</p>
                <a href="#product-list" class="btn rounded-pill px-5 py-2 shadow-sm text-white fw-bold fs-5" style="background-color: var(--accent-green);">Mua Ngay</a>
            </div>
            <div class="col-md-5 text-center p-0 h-100">
                <div id="promoCarousel"
                     class="carousel slide promo-carousel h-100"
                     data-bs-ride="carousel"
                     data-bs-interval="3000"
                     data-bs-pause="false">
                    <div class="carousel-indicators promo-carousel-indicators">
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Banner 1"></button>
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="1" aria-label="Banner 2"></button>
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="2" aria-label="Banner 3"></button>
                    </div>

                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100">
                            <img src="assets/img/banner1.jpg" class="d-block w-100 promo-banner-img" alt="Banner 1">
                        </div>
                        <div class="carousel-item h-100">
                            <img src="assets/img/banner2.jpg" class="d-block w-100 promo-banner-img" alt="Banner 2">
                        </div>
                        <div class="carousel-item h-100">
                            <img src="assets/img/banner3.jpg" class="d-block w-100 promo-banner-img" alt="Banner 3">
                        </div>
                    </div>

                    <button class="carousel-control-prev promo-carousel-control" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev" aria-label="Banner trước">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next promo-carousel-control" type="button" data-bs-target="#promoCarousel" data-bs-slide="next" aria-label="Banner tiếp theo">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($categories)): ?>
    <div class="mb-4 category-ribbon-wrapper">
        <div class="category-ribbon" data-category-ribbon>
            <?php foreach ($categories as $cat): ?>
                <?php
                [$iconClass, $iconColor] = category_icon_meta($cat['lsp_ten']);
                $categoryImage = category_image_url((int) $cat['lsp_id']);
                ?>
                <a href="index.php?category=<?php echo (int) $cat['lsp_id']; ?>"
                   class="category-ribbon-item text-decoration-none"
                   data-category-chip
                   data-category-name="<?php echo htmlspecialchars($cat['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-category-url="index.php?category=<?php echo (int) $cat['lsp_id']; ?>"
                   data-category-icon-class="<?php echo htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8'); ?>"
                   data-category-icon-color="<?php echo htmlspecialchars($iconColor, ENT_QUOTES, 'UTF-8'); ?>"
                   data-category-image="<?php echo htmlspecialchars($categoryImage ?: '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($categoryImage): ?>
                        <img src="<?php echo $categoryImage; ?>" alt="<?php echo htmlspecialchars($cat['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?>" class="category-ribbon-image">
                    <?php else: ?>
                        <span class="category-ribbon-icon" style="color: <?php echo $iconColor; ?>;"><i class="<?php echo $iconClass; ?>"></i></span>
                    <?php endif; ?>
                    <span class="category-ribbon-text"><?php echo htmlspecialchars($cat['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>

            <div class="dropdown category-more-wrapper d-none" data-category-more-wrapper>
                <button class="category-more-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">...</button>
                <div class="dropdown-menu dropdown-menu-start category-more-menu shadow-sm border-0" data-category-more-menu></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="container my-4" id="product-list">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-2">
        <?php if ($keyword !== ''): ?>
            <h4 class="fw-bold m-0 text-dark"><i class="fas fa-search text-muted me-2"></i>Kết quả cho "<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"</h4>
        <?php elseif ($selectedCategoryName !== ''): ?>
            <h4 class="fw-bold m-0 text-dark"><i class="fas fa-filter text-muted me-2"></i>Loại sản phẩm: <?php echo htmlspecialchars($selectedCategoryName, ENT_QUOTES, 'UTF-8'); ?></h4>
        <?php else: ?>
            <h4 class="fw-bold m-0" style="color: var(--accent-green);">Khám Phá Sản Phẩm</h4>
        <?php endif; ?>
        <?php if ($categoryId > 0 || $keyword !== ''): ?>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill">Xóa bộ lọc</a>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card shadow-sm h-100 d-flex flex-column position-relative bg-white rounded-3 overflow-hidden">
                    <div class="product-img-wrap" style="height: 200px; overflow: hidden;">
                        <?php
                        if (!empty($row['sp_hinhanh'])) {
                            $mainImg = $row['sp_hinhanh'];
                        } else {
                            $folderName = 'SP' . str_pad($row['sp_id'], 2, '0', STR_PAD_LEFT);
                            $folderPath = 'assets/uploads/' . $folderName . '/';
                            $images = glob($folderPath . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
                            $mainImg = !empty($images) ? $images[0] : 'assets/img/default-product.png';
                        }
                        $daBan = isset($row['sp_daban']) ? $row['sp_daban'] : 0;
                        ?>
                        <a href="detail.php?id=<?php echo (int) $row['sp_id']; ?>">
                            <img src="<?php echo $mainImg; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>">
                        </a>
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <a href="detail.php?id=<?php echo (int) $row['sp_id']; ?>" class="product-title text-decoration-none text-dark fw-bold mb-2 d-block text-truncate"><?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <div class="small text-muted mb-2"><?php echo htmlspecialchars($row['lsp_ten'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="rating-stars mb-2 small text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="mt-auto d-flex justify-content-between align-items-end">
                            <div class="product-price fw-bold text-danger fs-5"><?php echo number_format((float) $row['sp_giaban'], 0, ',', '.'); ?>đ</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Đã bán <?php echo number_format((int) $daBan); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">Rất tiếc, không tìm thấy sản phẩm nào.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link shadow-sm border-0 mx-1 rounded-circle fw-bold" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($keyword); ?>&category=<?php echo $categoryId; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
