<?php
namespace App\Models;

use Exception;

class OrderModel extends BaseModel
{
    private $statusStorageMapCache = null;

    public function hasStatusColumn()
    {
        return true;
    }

    public function hasNoteColumn()
    {
        return true;
    }

    public function hasDeliveryAddressColumn()
    {
        return true;
    }

    public function canCustomerCancelOrder($status)
    {
        $status = order_status_normalize($status);
        return in_array($status, ['dang_xu_ly', 'da_xac_nhan'], true);
    }

    public function createOrderAndDeductStock(array $cart, $paymentMethod = 'Tiền mặt', $khId = null, $nvId = null, $status = null, $note = null, $deliveryAddress = null)
    {
        if (empty($cart)) {
            throw new Exception('Giỏ hàng trống.');
        }

        $khId = $khId === null ? null : (int) $khId;
        $nvId = $nvId === null ? null : (int) $nvId;
        $status = $status ?: (($nvId !== null && $khId === null) ? 'hoan_thanh' : 'dang_xu_ly');
        $status = order_status_normalize($status);
        $note = $note !== null ? trim($note) : null;
        $note = $note === '' ? null : $note;
        $deliveryAddress = $deliveryAddress !== null ? trim((string) $deliveryAddress) : null;
        if ($deliveryAddress === '') {
            $deliveryAddress = null;
        }
        if ($nvId !== null && $deliveryAddress === null) {
            $deliveryAddress = 'Mua tại quầy';
        }


        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn->begin_transaction();

            $items = [];
            $total = 0.0;

            $stmtProduct = $this->conn->prepare('SELECT sp_ten, sp_giaban, sp_tonkho FROM san_pham WHERE sp_id = ? FOR UPDATE');

            foreach ($cart as $spId => $item) {
                $spId = (int) $spId;
                $qty = isset($item['sl']) ? (int) $item['sl'] : 0;

                if ($spId <= 0 || $qty <= 0) {
                    throw new Exception('Dữ liệu sản phẩm trong giỏ hàng không hợp lệ.');
                }

                $stmtProduct->bind_param('i', $spId);
                $stmtProduct->execute();
                $resultProduct = $stmtProduct->get_result();

                if ($resultProduct->num_rows === 0) {
                    throw new Exception('Sản phẩm không tồn tại.');
                }

                $product = $resultProduct->fetch_assoc();

                if ((float) $product['sp_tonkho'] < $qty) {
                    throw new Exception('Sản phẩm ' . $product['sp_ten'] . ' không đủ tồn kho.');
                }

                $price = (float) $product['sp_giaban'];
                $lineTotal = $price * $qty;
                $total += $lineTotal;

                $items[] = [
                    'sp_id' => $spId,
                    'ten' => $product['sp_ten'],
                    'gia' => $price,
                    'sl' => $qty,
                    'thanhtien' => $lineTotal,
                    'tonkho_moi' => (float) $product['sp_tonkho'] - $qty,
                ];
            }

            $stmtProduct->close();


            $stmtOrder = $this->conn->prepare('INSERT INTO hoa_don (hd_tongtien, hd_hinhthuctt, nv_id, kh_id, hd_trangthai, hd_ghichu, hd_diachinhan) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $storageStatus = $this->toStorageStatus($status);
            $stmtOrder->bind_param('dsiisss', $total, $paymentMethod, $nvId, $khId, $storageStatus, $note, $deliveryAddress);
            $stmtOrder->execute();
            $hdId = $this->conn->insert_id;
            $stmtOrder->close();

            $stmtOrderDetail = $this->conn->prepare('INSERT INTO ct_hoa_don (hd_id, sp_id, cthd_soluong, cthd_dongia, cthd_thanhtien) VALUES (?, ?, ?, ?, ?)');
            $stmtUpdateProduct = $this->conn->prepare('UPDATE san_pham SET sp_tonkho = ? WHERE sp_id = ?');
            $stmtBatch = $this->conn->prepare('SELECT ctpn_id, ctpn_soluongton FROM ct_phieu_nhap WHERE sp_id = ? AND ctpn_soluongton > 0 ORDER BY ctpn_hansudung ASC, ctpn_id ASC FOR UPDATE');
            $stmtUpdateBatch = $this->conn->prepare('UPDATE ct_phieu_nhap SET ctpn_soluongton = ? WHERE ctpn_id = ?');

            foreach ($items as $item) {
                $spId = (int) $item['sp_id'];
                $qty = (int) $item['sl'];
                $price = (float) $item['gia'];
                $lineTotal = (float) $item['thanhtien'];
                $newStock = (float) $item['tonkho_moi'];

                $stmtOrderDetail->bind_param('iiidd', $hdId, $spId, $qty, $price, $lineTotal);
                $stmtOrderDetail->execute();

                $stmtUpdateProduct->bind_param('di', $newStock, $spId);
                $stmtUpdateProduct->execute();

                $stmtBatch->bind_param('i', $spId);
                $stmtBatch->execute();
                $resultBatch = $stmtBatch->get_result();

                $remaining = $qty;
                while ($batch = $resultBatch->fetch_assoc()) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $batchId = (int) $batch['ctpn_id'];
                    $stockInBatch = (float) $batch['ctpn_soluongton'];

                    if ($stockInBatch >= $remaining) {
                        $updatedBatchStock = $stockInBatch - $remaining;
                        $remaining = 0;
                    } else {
                        $updatedBatchStock = 0;
                        $remaining -= $stockInBatch;
                    }

                    $stmtUpdateBatch->bind_param('di', $updatedBatchStock, $batchId);
                    $stmtUpdateBatch->execute();
                }

                if ($remaining > 0) {
                    throw new Exception('Sản phẩm ' . $item['ten'] . ' không đủ hàng trong các lô nhập.');
                }
            }

            $stmtOrderDetail->close();
            $stmtUpdateProduct->close();
            $stmtBatch->close();
            $stmtUpdateBatch->close();

            if ($this->isCompletedStatus($status)) {
                $this->applySoldCountDelta($items, 1);
            }

            $this->conn->commit();
            return $hdId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getAdminOrders($status = '', $fromDate = '', $toDate = '')
    {
        $where = [];
        $types = '';
        $values = [];

        if ($this->hasStatusColumn() && $status !== '') {
            $where[] = 'hd.hd_trangthai = ?';
            $types .= 's';
            $values[] = $this->toStorageStatus($status);
        }

        if ($fromDate !== '') {
            $where[] = 'DATE(hd.hd_ngaylap) >= ?';
            $types .= 's';
            $values[] = $fromDate;
        }

        if ($toDate !== '') {
            $where[] = 'DATE(hd.hd_ngaylap) <= ?';
            $types .= 's';
            $values[] = $toDate;
        }

        $statusSelect = 'hd.hd_trangthai,';
        $noteSelect = 'hd.hd_ghichu,';
        $deliveryAddressSelect = 'hd.hd_diachinhan,';

        $sql = "
            SELECT
                hd.hd_id,
                hd.hd_tongtien,
                hd.hd_hinhthuctt,
                hd.hd_ngaylap,
                hd.kh_id,
                hd.nv_id,
                {$statusSelect}
                {$noteSelect}
                {$deliveryAddressSelect}
                nv.nv_hoten,
                kh.kh_hoten
            FROM hoa_don hd
            LEFT JOIN nhan_vien nv ON hd.nv_id = nv.nv_id
            LEFT JOIN khach_hang kh ON hd.kh_id = kh.kh_id
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY hd.hd_ngaylap DESC';

        $stmt = $this->conn->prepare($sql);
        if ($types !== '') {
            $this->bindParams($stmt, $types, $values);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            $row['nguon_don'] = $this->resolveOrderSource($row);
            $row['hd_trangthai'] = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');
            $row['hd_trangthai_hienthi'] = order_status_label($row['hd_trangthai']);
        }

        return $rows;
    }

    public function getAdminOrderById($hdId)
    {
        $hdId = (int) $hdId;
        $statusSelect = 'hd.hd_trangthai,';
        $noteSelect = 'hd.hd_ghichu,';
        $deliveryAddressSelect = 'hd.hd_diachinhan,';

        $sql = "
            SELECT
                hd.hd_id,
                hd.hd_tongtien,
                hd.hd_hinhthuctt,
                hd.hd_ngaylap,
                hd.kh_id,
                hd.nv_id,
                {$statusSelect}
                {$noteSelect}
                {$deliveryAddressSelect}
                nv.nv_hoten,
                kh.kh_hoten,
                kh.kh_sdt,
                kh.kh_diachi
            FROM hoa_don hd
            LEFT JOIN nhan_vien nv ON hd.nv_id = nv.nv_id
            LEFT JOIN khach_hang kh ON hd.kh_id = kh.kh_id
            WHERE hd.hd_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $hdId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $row['nguon_don'] = $this->resolveOrderSource($row);
        $row['hd_trangthai'] = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');
        $row['hd_trangthai_hienthi'] = order_status_label($row['hd_trangthai']);
        $row['la_don_online'] = !empty($row['kh_id']) && empty($row['nv_id']);

        return $row;
    }

    public function getOrderItems($hdId)
    {
        $hdId = (int) $hdId;
        $sql = '
            SELECT ct.*, sp.sp_ten
            FROM ct_hoa_don ct
            JOIN san_pham sp ON ct.sp_id = sp.sp_id
            WHERE ct.hd_id = ?
            ORDER BY ct.sp_id ASC
        ';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $hdId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $items;
    }

    public function updateStatus($hdId, $status)
    {
        $status = order_status_normalize($status);
        $options = order_status_options();
        if (!isset($options[$status])) {
            throw new Exception('Trạng thái đơn hàng không hợp lệ.');
        }

        $hdId = (int) $hdId;
        if ($hdId <= 0) {
            throw new Exception('Mã đơn hàng không hợp lệ.');
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn->begin_transaction();

            $sql = 'SELECT hd_id, kh_id, nv_id, hd_trangthai FROM hoa_don WHERE hd_id = ? LIMIT 1 FOR UPDATE';
            $stmtOrder = $this->conn->prepare($sql);
            $stmtOrder->bind_param('i', $hdId);
            $stmtOrder->execute();
            $order = $stmtOrder->get_result()->fetch_assoc();
            $stmtOrder->close();

            if (!$order) {
                throw new Exception('Không tìm thấy đơn hàng.');
            }

            $currentStatus = order_status_normalize($order['hd_trangthai'] ?? 'dang_xu_ly');
            if ($currentStatus === $status) {
                $this->conn->commit();
                return true;
            }

            $this->assertValidStatusTransition($currentStatus, $status);

            $items = $this->getOrderItems($hdId);
            if (empty($items)) {
                throw new Exception('Đơn hàng chưa có sản phẩm chi tiết.');
            }

            if (!$this->isCompletedStatus($currentStatus) && $this->isCompletedStatus($status)) {
                $this->applySoldCountDelta($items, 1);
            }

            if ($this->isCompletedStatus($currentStatus) && !$this->isCompletedStatus($status)) {
                $this->applySoldCountDelta($items, -1);
            }

            if ($this->isCanceledStatus($status)) {
                $this->restoreInventoryForItems($items);
            }

            $storageStatus = $this->toStorageStatus($status);
            $stmt = $this->conn->prepare('UPDATE hoa_don SET hd_trangthai = ? WHERE hd_id = ?');
            $stmt->bind_param('si', $storageStatus, $hdId);
            $stmt->execute();
            $stmt->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }


    public function getAllowedNextStatuses($currentStatus)
    {
        $currentStatus = order_status_normalize($currentStatus);
        $options = order_status_options();
        $allowed = [];

        foreach ($this->getStatusTransitionMap()[$currentStatus] ?? [] as $status) {
            if (isset($options[$status])) {
                $allowed[$status] = $options[$status];
            }
        }

        return $allowed;
    }

    public function getCustomerOrders($khId)
    {
        $khId = (int) $khId;
        $statusSelect = 'hd_trangthai';
        $noteSelect = 'hd_ghichu';
        $deliveryAddressSelect = 'hd_diachinhan';
        $sql = "SELECT hd_id, hd_ngaylap, hd_tongtien, hd_hinhthuctt, {$statusSelect}, {$noteSelect}, {$deliveryAddressSelect} FROM hoa_don WHERE kh_id = ? ORDER BY hd_ngaylap DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $khId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            $row['hd_trangthai'] = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');
            $row['hd_trangthai_hienthi'] = order_status_label($row['hd_trangthai']);
        }

        return $rows;
    }

    public function getCustomerOrderById($hdId, $khId)
    {
        $hdId = (int) $hdId;
        $khId = (int) $khId;
        $statusSelect = 'hd_trangthai';
        $noteSelect = 'hd_ghichu';
        $deliveryAddressSelect = 'hd_diachinhan';

        $sql = "SELECT hd.hd_id, hd.hd_ngaylap, hd.hd_tongtien, hd.hd_hinhthuctt, {$statusSelect}, {$noteSelect}, {$deliveryAddressSelect}, kh.kh_diachi FROM hoa_don hd LEFT JOIN khach_hang kh ON hd.kh_id = kh.kh_id WHERE hd.hd_id = ? AND hd.kh_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $hdId, $khId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $row['hd_trangthai'] = order_status_normalize($row['hd_trangthai'] ?? 'dang_xu_ly');
        $row['hd_trangthai_hienthi'] = order_status_label($row['hd_trangthai']);
        return $row;
    }

    public function cancelCustomerOrder($hdId, $khId, $reason)
    {
        $hdId = (int) $hdId;
        $khId = (int) $khId;
        $reason = trim((string) $reason);

        if ($hdId <= 0 || $khId <= 0) {
            throw new Exception('Thông tin đơn hàng không hợp lệ.');
        }

        if ($reason === '') {
            throw new Exception('Vui lòng nhập lý do hủy đơn hàng.');
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn->begin_transaction();

            $statusSelect = 'hd_trangthai';
            $noteSelect = 'hd_ghichu';
            $sqlOrder = "SELECT hd_id, kh_id, nv_id, {$statusSelect}, {$noteSelect} FROM hoa_don WHERE hd_id = ? AND kh_id = ? LIMIT 1 FOR UPDATE";
            $stmtOrder = $this->conn->prepare($sqlOrder);
            $stmtOrder->bind_param('ii', $hdId, $khId);
            $stmtOrder->execute();
            $order = $stmtOrder->get_result()->fetch_assoc();
            $stmtOrder->close();

            if (!$order) {
                throw new Exception('Không tìm thấy đơn hàng để hủy.');
            }

            $status = order_status_normalize($order['hd_trangthai'] ?? 'dang_xu_ly');
            if (!$this->canCustomerCancelOrder($status)) {
                throw new Exception('Đơn hàng này không còn có thể hủy.');
            }

            $items = $this->getOrderItems($hdId);
            $this->restoreInventoryForItems($items);

            $existingNote = trim((string) ($order['hd_ghichu'] ?? ''));
            $cancelNote = 'Lý do khách hủy đơn: ' . $reason;
            $newNote = $existingNote !== '' ? ($existingNote . "\n\n" . $cancelNote) : $cancelNote;

            $storageStatus = $this->toStorageStatus('da_huy');
            $stmtUpdateOrder = $this->conn->prepare('UPDATE hoa_don SET hd_trangthai = ?, hd_ghichu = ? WHERE hd_id = ? AND kh_id = ?');
            $stmtUpdateOrder->bind_param('ssii', $storageStatus, $newNote, $hdId, $khId);

            $stmtUpdateOrder->execute();
            $stmtUpdateOrder->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getDashboardTransactions($fromDate, $toDate)
    {
        return $this->getAdminOrders('', $fromDate, $toDate);
    }


    public function getPendingOnlineCount()
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM hoa_don
            WHERE kh_id IS NOT NULL
              AND (nv_id IS NULL OR nv_id = 0)
              AND hd_trangthai = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $status = $this->toStorageStatus('dang_xu_ly');
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    public function getLatestOnlineOrderId()
    {
        $sql = "
            SELECT hd_id
            FROM hoa_don
            WHERE kh_id IS NOT NULL
              AND (nv_id IS NULL OR nv_id = 0)
            ORDER BY hd_id DESC
            LIMIT 1
        ";

        $result = $this->conn->query($sql);
        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();
        return (int) ($row['hd_id'] ?? 0);
    }


    private function getStatusTransitionMap()
    {
        return [
            'dang_xu_ly' => ['da_xac_nhan', 'da_huy'],
            'da_xac_nhan' => ['dang_giao', 'da_huy'],
            'dang_giao' => ['hoan_thanh', 'da_huy'],
            'hoan_thanh' => [],
            'da_huy' => [],
        ];
    }

    private function assertValidStatusTransition($currentStatus, $nextStatus)
    {
        $currentStatus = order_status_normalize($currentStatus);
        $nextStatus = order_status_normalize($nextStatus);
        $options = order_status_options();

        if (!isset($options[$currentStatus])) {
            throw new Exception('Trạng thái hiện tại của đơn hàng không hợp lệ.');
        }

        if (!isset($options[$nextStatus])) {
            throw new Exception('Trạng thái đơn hàng không hợp lệ.');
        }

        if ($this->isCanceledStatus($currentStatus)) {
            throw new Exception('Đơn hàng đã hủy và không thể cập nhật lại trạng thái.');
        }

        if ($this->isCompletedStatus($currentStatus)) {
            throw new Exception('Đơn hàng đã hoàn thành và không thể cập nhật lại trạng thái.');
        }

        $allowedNextStatuses = $this->getStatusTransitionMap()[$currentStatus] ?? [];
        if (!in_array($nextStatus, $allowedNextStatuses, true)) {
            $currentLabel = order_status_label($currentStatus);
            $nextLabel = order_status_label($nextStatus);
            throw new Exception('Không thể chuyển trạng thái từ ' . $currentLabel . ' sang ' . $nextLabel . '.');
        }
    }

    private function getStatusStorageMap()
    {
        if ($this->statusStorageMapCache !== null) {
            return $this->statusStorageMapCache;
        }

        $map = [
            'dang_xu_ly' => 'dang_xu_ly',
            'da_xac_nhan' => 'da_xac_nhan',
            'dang_giao' => 'dang_giao',
            'hoan_thanh' => 'hoan_thanh',
            'da_huy' => 'da_huy',
        ];

        if (!$this->hasStatusColumn()) {
            $this->statusStorageMapCache = $map;
            return $this->statusStorageMapCache;
        }

        $columnType = strtolower((string) $this->getColumnType('hoa_don', 'hd_trangthai'));
        preg_match_all("/'([^']+)'/", $columnType, $matches);
        $allowed = $matches[1] ?? [];

        if (!empty($allowed)) {
            if (!in_array('da_xac_nhan', $allowed, true) && in_array('da_thanh_toan', $allowed, true)) {
                $map['da_xac_nhan'] = 'da_thanh_toan';
            }
            if (!in_array('hoan_thanh', $allowed, true) && in_array('hoan_tat', $allowed, true)) {
                $map['hoan_thanh'] = 'hoan_tat';
            }
            foreach ($map as $canonical => $stored) {
                if (!in_array($stored, $allowed, true) && in_array($canonical, $allowed, true)) {
                    $map[$canonical] = $canonical;
                }
            }
        }

        $this->statusStorageMapCache = $map;
        return $this->statusStorageMapCache;
    }

    private function toStorageStatus($status)
    {
        $status = order_status_normalize($status);
        $map = $this->getStatusStorageMap();
        return $map[$status] ?? $status;
    }

    private function resolveOrderSource(array $row)
    {
        if (!empty($row['nv_hoten'])) {
            return $row['nv_hoten'] . ' (POS)';
        }

        if (!empty($row['kh_hoten'])) {
            return $row['kh_hoten'] . ' (Online)';
        }

        return 'Không xác định';
    }

    private function applySoldCountDelta(array $items, int $direction)
    {
        if ($direction === 0 || empty($items)) {
            return;
        }

        if ($direction > 0) {
            $stmt = $this->conn->prepare('UPDATE san_pham SET sp_daban = COALESCE(sp_daban, 0) + ? WHERE sp_id = ?');
        } else {
            $stmt = $this->conn->prepare('UPDATE san_pham SET sp_daban = GREATEST(COALESCE(sp_daban, 0) - ?, 0) WHERE sp_id = ?');
        }

        foreach ($items as $item) {
            $qty = (int) ($item['cthd_soluong'] ?? $item['sl'] ?? 0);
            $spId = (int) ($item['sp_id'] ?? 0);
            if ($qty <= 0 || $spId <= 0) {
                continue;
            }
            $stmt->bind_param('ii', $qty, $spId);
            $stmt->execute();
        }

        $stmt->close();
    }

    private function restoreInventoryForItems(array $items)
    {
        if (empty($items)) {
            return;
        }

        $stmtUpdateProduct = $this->conn->prepare('UPDATE san_pham SET sp_tonkho = sp_tonkho + ? WHERE sp_id = ?');
        $stmtFindBatch = $this->conn->prepare('SELECT ctpn_id FROM ct_phieu_nhap WHERE sp_id = ? ORDER BY ctpn_hansudung ASC, ctpn_id ASC LIMIT 1 FOR UPDATE');
        $stmtRestoreBatch = $this->conn->prepare('UPDATE ct_phieu_nhap SET ctpn_soluongton = ctpn_soluongton + ? WHERE ctpn_id = ?');

        foreach ($items as $item) {
            $spId = (int) ($item['sp_id'] ?? 0);
            $qty = (int) ($item['cthd_soluong'] ?? $item['sl'] ?? 0);
            if ($spId <= 0 || $qty <= 0) {
                continue;
            }

            $stmtUpdateProduct->bind_param('di', $qty, $spId);
            $stmtUpdateProduct->execute();

            $stmtFindBatch->bind_param('i', $spId);
            $stmtFindBatch->execute();
            $batch = $stmtFindBatch->get_result()->fetch_assoc();

            if ($batch && isset($batch['ctpn_id'])) {
                $batchId = (int) $batch['ctpn_id'];
                $stmtRestoreBatch->bind_param('di', $qty, $batchId);
                $stmtRestoreBatch->execute();
            }
        }

        $stmtUpdateProduct->close();
        $stmtFindBatch->close();
        $stmtRestoreBatch->close();
    }

    private function isCompletedStatus($status)
    {
        return order_status_normalize($status) === 'hoan_thanh';
    }

    private function isCanceledStatus($status)
    {
        return order_status_normalize($status) === 'da_huy';
    }

    private function bindParams($stmt, $types, array $values)
    {
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
}
?>
