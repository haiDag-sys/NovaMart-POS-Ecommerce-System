<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include 'includes/db.php';

if (!isset($_SESSION['kh_id'])) {
    $redirect = 'login_member.php?redirect=' . rawurlencode($_SERVER['HTTP_REFERER'] ?? 'index.php');
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập trước khi thêm sản phẩm vào giỏ hàng.',
        'requires_login' => true,
        'redirect' => $redirect
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['sp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    exit();
}

$sp_id = intval($_POST['sp_id']);
if ($sp_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
    exit();
}

$stmt = $conn->prepare("SELECT sp_id, sp_ten, sp_giaban, sp_tonkho, sp_hinhanh FROM san_pham WHERE sp_id = ? LIMIT 1");
$stmt->bind_param("i", $sp_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
    exit();
}

if ((float)$product['sp_tonkho'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng.']);
    exit();
}

if (!isset($_SESSION['client_cart'])) {
    $_SESSION['client_cart'] = [];
}

$current_qty = isset($_SESSION['client_cart'][$sp_id]) ? intval($_SESSION['client_cart'][$sp_id]['sl']) : 0;
$new_qty = $current_qty + 1;

if ($new_qty > (float)$product['sp_tonkho']) {
    echo json_encode(['success' => false, 'message' => 'Số lượng thêm vượt quá tồn kho.', 'total_items' => array_sum(array_column($_SESSION['client_cart'], 'sl'))]);
    exit();
}

if (!empty($product['sp_hinhanh'])) {
    $main_img = $product['sp_hinhanh'];
} else {
    $folder_name = "SP" . str_pad($product['sp_id'], 2, '0', STR_PAD_LEFT);
    $folder_path = "assets/uploads/" . $folder_name . "/";
    $images = glob($folder_path . "*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}", GLOB_BRACE);
    $main_img = !empty($images) ? $images[0] : 'assets/img/default-product.png';
}

$_SESSION['client_cart'][$sp_id] = [
    'ten' => $product['sp_ten'],
    'gia' => (float)$product['sp_giaban'],
    'hinh' => $main_img,
    'sl' => $new_qty
];

$cart_count = 0;
foreach ($_SESSION['client_cart'] as $item) {
    $cart_count += intval($item['sl']);
}

echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng.', 'total_items' => $cart_count]);
?>
