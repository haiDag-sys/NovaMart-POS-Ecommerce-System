<?php
namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\OrderModel;
use Exception;

class AdminOrderController
{
    private $orderModel;
    private $notificationModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        require_admin();

        $status = trim($_GET['status'] ?? '');
        $fromDate = trim($_GET['from'] ?? '');
        $toDate = trim($_GET['to'] ?? '');

        $orders = $this->orderModel->getAdminOrders($status, $fromDate, $toDate);

        view('admin/orders/index', [
            'orders' => $orders,
            'ten_nguoi_truc' => $_SESSION['hoten'] ?? 'Quản lý',
            'currentStatus' => $status,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'flashSuccess' => get_flash('success'),
            'flashError' => get_flash('error'),
        ]);
    }


    public function poll()
    {
        require_admin();

        header('Content-Type: application/json; charset=UTF-8');

        $scope = trim((string) ($_GET['scope'] ?? 'orders'));
        $status = trim((string) ($_GET['status'] ?? ''));
        $fromDate = trim((string) ($_GET['from'] ?? ''));
        $toDate = trim((string) ($_GET['to'] ?? ''));

        $response = [
            'success' => true,
            'scope' => $scope,
            'pending_online_count' => $this->orderModel->getPendingOnlineCount(),
            'latest_online_order_id' => $this->orderModel->getLatestOnlineOrderId(),
        ];

        if ($scope === 'dashboard') {
            $transactions = $this->orderModel->getDashboardTransactions($fromDate, $toDate);
            $tongDoanhThu = 0.0;
            $donOnlineDangXuLy = 0;

            foreach ($transactions as $row) {
                $trangThai = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');

                if ($trangThai === 'hoan_thanh') {
                    $tongDoanhThu += (float) ($row['hd_tongtien'] ?? 0);
                }

                if (!empty($row['kh_id']) && empty($row['nv_id']) && $trangThai === 'dang_xu_ly') {
                    $donOnlineDangXuLy++;
                }
            }

            $response['summary'] = [
                'tong_doanh_thu' => $tongDoanhThu,
                'tong_doanh_thu_hienthi' => number_format($tongDoanhThu, 0, ',', '.'),
                'don_online_dang_xu_ly' => $donOnlineDangXuLy,
                'tong_giao_dich' => count($transactions),
            ];
            $response['transactions'] = array_map([$this, 'serializeOrderRow'], $transactions);
        } else {
            $orders = $this->orderModel->getAdminOrders($status, $fromDate, $toDate);
            $response['orders'] = array_map([$this, 'serializeOrderRow'], $orders);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    public function show()
    {
        require_admin();

        $hdId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($hdId <= 0) {
            die('Không tìm thấy mã hóa đơn!');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cap_nhat_trang_thai'])) {
            $status = trim($_POST['hd_trangthai'] ?? '');
            $currentOrder = $this->orderModel->getAdminOrderById($hdId);

            try {
                $this->orderModel->updateStatus($hdId, $status);
                $updatedStatus = order_status_normalize($status);

                if ($currentOrder && !empty($currentOrder['kh_id']) && order_status_normalize($currentOrder['hd_trangthai'] ?? 'dang_xu_ly') !== $updatedStatus) {
                    $this->notificationModel->createOrderStatusNotification((int) $currentOrder['kh_id'], $hdId, $updatedStatus);
                }

                set_flash('success', 'Cập nhật trạng thái đơn hàng thành công.');
            } catch (Exception $e) {
                set_flash('error', $e->getMessage());
            }

            redirect('order_details.php?id=' . $hdId);
        }

        $order = $this->orderModel->getAdminOrderById($hdId);
        if (!$order) {
            die('Hóa đơn không tồn tại.');
        }

        $items = $this->orderModel->getOrderItems($hdId);

        $statusOptions = [];
        $currentStatus = order_status_normalize($order['hd_trangthai'] ?? 'dang_xu_ly');
        $statusOptions[$currentStatus] = order_status_label($currentStatus);
        foreach ($this->orderModel->getAllowedNextStatuses($currentStatus) as $value => $label) {
            $statusOptions[$value] = $label;
        }

        view('admin/orders/show', [
            'order' => $order,
            'items' => $items,
            'statusOptions' => $statusOptions,
            'flashSuccess' => get_flash('success'),
            'flashError' => get_flash('error'),
        ]);
    }
    private function serializeOrderRow(array $row): array
    {
        $status = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');

        return [
            'hd_id' => (int) ($row['hd_id'] ?? 0),
            'hd_ngaylap' => (string) ($row['hd_ngaylap'] ?? ''),
            'hd_ngaylap_hienthi' => !empty($row['hd_ngaylap']) ? date('d/m/Y H:i', strtotime($row['hd_ngaylap'])) : '',
            'nguon_don' => (string) ($row['nguon_don'] ?? ''),
            'hd_trangthai' => $status,
            'hd_trangthai_hienthi' => order_status_label($status),
            'hd_trangthai_badge' => order_status_badge($status),
            'hd_tongtien' => (float) ($row['hd_tongtien'] ?? 0),
            'hd_tongtien_hienthi' => number_format((float) ($row['hd_tongtien'] ?? 0), 0, ',', '.'),
            'hd_hinhthuctt' => (string) ($row['hd_hinhthuctt'] ?? ''),
            'order_details_url' => 'order_details.php?id=' . (int) ($row['hd_id'] ?? 0),
        ];
    }

}
?>
