<?php
session_start();
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['kh_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'unauthorized']);
    exit;
}

$model = new App\Models\NotificationModel();
$khId = (int) $_SESSION['kh_id'];
$notifications = $model->getCustomerNotifications($khId, 8);
$unread = $model->getUnreadCount($khId);

$items = array_map(static function (array $tb): array {
    $hdId = isset($tb['hd_id']) ? (int) $tb['hd_id'] : 0;
    return [
        'tb_id' => (int) ($tb['tb_id'] ?? 0),
        'hd_id' => $hdId,
        'tb_loai' => (string) ($tb['tb_loai'] ?? 'don_hang'),
        'tb_noidung' => (string) ($tb['tb_noidung'] ?? ''),
        'tb_dadoc' => !empty($tb['tb_dadoc']) ? 1 : 0,
        'tb_thoigian' => (string) ($tb['tb_thoigian'] ?? ''),
        'tb_thoigian_hienthi' => !empty($tb['tb_thoigian']) ? date('d/m/Y H:i', strtotime($tb['tb_thoigian'])) : '',
        'link' => $hdId > 0 ? 'order_detail.php?id=' . $hdId : 'profile.php',
    ];
}, $notifications);

echo json_encode([
    'success' => true,
    'unread_count' => $unread,
    'notifications' => $items,
    'server_time' => date('c'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
