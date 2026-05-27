<?php
session_start();
include '../includes/db.php';
require_admin();

$currentAdminPage = 'products';

if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $stmtCheck = $conn->prepare('SELECT 1 FROM ct_hoa_don WHERE sp_id = ? LIMIT 1');
    $stmtCheck->bind_param('i', $id);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        echo "<script>alert('Không thể xóa! Sản phẩm đã có trong hóa đơn.'); window.location='products.php';</script>";
        exit();
    }
    $stmtCheck->close();
    $stmtDelete = $conn->prepare('DELETE FROM san_pham WHERE sp_id = ?');
    $stmtDelete->bind_param('i', $id);
    $stmtDelete->execute();
    $stmtDelete->close();
    header('Location: products.php');
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$q = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$where = [];
$types = '';
$values = [];

if ($q !== '') {
    $where[] = 'sp.sp_ten LIKE ?';
    $types .= 's';
    $values[] = '%' . $q . '%';
}

if ($categoryId > 0) {
    $where[] = 'sp.lsp_id = ?';
    $types .= 'i';
    $values[] = $categoryId;
}

$whereSql = !empty($where) ? (' WHERE ' . implode(' AND ', $where)) : '';

$sqlCount = 'SELECT COUNT(*) AS total FROM san_pham sp' . $whereSql;
$stmtCount = $conn->prepare($sqlCount);
if ($types !== '') {
    $bindValues = $values;
    $refs = [];
    foreach ($bindValues as $k => $v) { $refs[$k] = &$bindValues[$k]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmtCount, 'bind_param'], $refs);
}
$stmtCount->execute();
$totalRecords = (int) ($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCount->close();
$totalPages = max(1, (int) ceil($totalRecords / $limit));

$sqlList = 'SELECT sp.*, lsp.lsp_ten FROM san_pham sp LEFT JOIN loai_san_pham lsp ON lsp.lsp_id = sp.lsp_id' . $whereSql . ' ORDER BY sp.sp_id DESC LIMIT ?, ?';
$stmtList = $conn->prepare($sqlList);
$listTypes = $types . 'ii';
$listValues = $values;
$listValues[] = $offset;
$listValues[] = $limit;
$refs = [];
foreach ($listValues as $k => $v) { $refs[$k] = &$listValues[$k]; }
array_unshift($refs, $listTypes);
call_user_func_array([$stmtList, 'bind_param'], $refs);
$stmtList->execute();
$result = $stmtList->get_result();
$categories = $conn->query('SELECT lsp_id, lsp_ten FROM loai_san_pham ORDER BY lsp_ten ASC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm - NovaMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        .product-img { width: 56px; height: 56px; object-fit: cover; border-radius: 12px; border: 1px solid #ddd; background: #fff; }
    </style>
</head>
<body class="backoffice-body">
<?php include 'includes/header.php'; ?>
<div class="container backoffice-page-wrap">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="backoffice-section-title mb-1"><i class="fas fa-boxes-stacked me-2 text-primary"></i>Quản lý sản phẩm</h2>
            <p class="text-muted mb-0">Danh sách sản phẩm, loại sản phẩm, ảnh đại diện và số lượng tồn hiện tại.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="add_product.php" class="btn btn-success fw-bold"><i class="fas fa-plus me-1"></i>Thêm sản phẩm</a>
            <a href="stock_in.php" class="btn btn-primary fw-bold"><i class="fas fa-truck-loading me-1"></i>Nhập kho</a>
            <a href="stock_receipts.php" class="btn btn-outline-primary fw-bold"><i class="fas fa-receipt me-1"></i>Phiếu nhập kho</a>
            <a href="categories.php" class="btn btn-outline-dark fw-bold"><i class="fas fa-layer-group me-1"></i>Loại sản phẩm</a>
        </div>
    </div>

    <div class="card backoffice-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label fw-bold">Tìm tên sản phẩm</label>
                    <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: Sữa, rau, bánh...">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Lọc theo loại</label>
                    <select name="category" class="form-select">
                        <option value="0">Tất cả loại sản phẩm</option>
                        <?php if ($categories): while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo (int) $cat['lsp_id']; ?>" <?php echo $categoryId === (int) $cat['lsp_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['lsp_ten'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold flex-grow-1">Lọc dữ liệu</button>
                    <a href="products.php" class="btn btn-outline-secondary">Làm mới</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card backoffice-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Sản phẩm</th>
                        <th>Loại</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php
                                        if (!empty($row['sp_hinhanh'])) {
                                            $src = '../' . ltrim($row['sp_hinhanh'], '/');
                                        } else {
                                            $folderId = 'SP' . str_pad($row['sp_id'], 2, '0', STR_PAD_LEFT);
                                            $path = '../assets/uploads/' . $folderId . '/';
                                            $images = glob($path . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
                                            $src = !empty($images) ? $images[0] : '../assets/img/logo.jpg';
                                        }
                                        ?>
                                        <img src="<?php echo $src; ?>" class="product-img me-3" alt="<?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['sp_ten'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <small class="text-muted">Đơn vị: <?php echo htmlspecialchars($row['sp_donvi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['lsp_ten'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-success fw-bold"><?php echo number_format((float) $row['sp_giaban']); ?> đ</td>
                                <td>
                                    <?php if ((float) $row['sp_tonkho'] > 0): ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill"><?php echo format_quantity($row['sp_tonkho']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="edit_product.php?id=<?php echo (int) $row['sp_id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                    <a href="?delete_id=<?php echo (int) $row['sp_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm-message="Xóa sản phẩm này?"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Không tìm thấy sản phẩm phù hợp.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-center my-4">
                <nav>
                    <ul class="pagination mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($q); ?>&category=<?php echo $categoryId; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
