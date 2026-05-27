<?php
session_start();
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'method_not_allowed']);
    exit;
}

if (!isset($_SESSION['kh_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'unauthorized']);
    exit;
}

$model = new App\Models\NotificationModel();
$ok = $model->markAllAsRead((int) $_SESSION['kh_id']);

echo json_encode([
    'success' => (bool) $ok,
    'unread_count' => 0,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
