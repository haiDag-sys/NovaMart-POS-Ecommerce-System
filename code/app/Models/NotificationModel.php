<?php
namespace App\Models;

class NotificationModel extends BaseModel
{
    public function createForCustomer(int $khId, string $message, ?int $hdId = null, string $type = 'don_hang'): bool
    {
        $message = trim($message);
        $type = trim($type) !== '' ? trim($type) : 'don_hang';

        if ($khId <= 0 || $message === '') {
            return false;
        }

        $stmt = $this->conn->prepare('INSERT INTO thong_bao (kh_id, hd_id, tb_loai, tb_noidung) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiss', $khId, $hdId, $type, $message);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function createOrderStatusNotification(int $khId, int $hdId, string $status): bool
    {
        $status = order_status_normalize($status);
        $label = order_status_label($status);
        $messageMap = [
            'dang_xu_ly' => 'Đơn hàng #' . $hdId . ' đang được xử lý.',
            'da_xac_nhan' => 'Đơn hàng #' . $hdId . ' đã được xác nhận.',
            'dang_giao' => 'Đơn hàng #' . $hdId . ' đang được giao.',
            'hoan_thanh' => 'Đơn hàng #' . $hdId . ' đã hoàn thành.',
            'da_huy' => 'Đơn hàng #' . $hdId . ' đã bị hủy.',
        ];

        $message = $messageMap[$status] ?? ('Trạng thái đơn hàng #' . $hdId . ' đã được cập nhật: ' . $label . '.');
        return $this->createForCustomer($khId, $message, $hdId, 'don_hang');
    }

    public function createCustomerCancelNotification(int $khId, int $hdId, string $reason): bool
    {
        $message = 'Đơn hàng #' . $hdId . ' đã được hủy.';
        $reason = trim($reason);
        if ($reason !== '') {
            $message .= ' Lý do: ' . $reason;
        }

        return $this->createForCustomer($khId, $message, $hdId, 'don_hang');
    }

    public function getCustomerNotifications(int $khId, int $limit = 8): array
    {
        $khId = (int) $khId;
        $limit = max(1, min(20, (int) $limit));

        $sql = "
            SELECT tb_id, hd_id, tb_loai, tb_noidung, tb_dadoc, tb_thoigian
            FROM thong_bao
            WHERE kh_id = ?
            ORDER BY tb_thoigian DESC, tb_id DESC
            LIMIT {$limit}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $khId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function getUnreadCount(int $khId): int
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM thong_bao WHERE kh_id = ? AND tb_dadoc = 0');
        $stmt->bind_param('i', $khId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    public function markAllAsRead(int $khId): bool
    {
        $stmt = $this->conn->prepare('UPDATE thong_bao SET tb_dadoc = 1 WHERE kh_id = ? AND tb_dadoc = 0');
        $stmt->bind_param('i', $khId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
?>
