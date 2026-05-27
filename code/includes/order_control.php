<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\OrderModel;

function taoHoaDonVaTruKho($conn, $gio_hang, $tong_tien_hd = 0, $hinh_thuc = 'Tiền mặt', $kh_id = null, $nv_id = null, $trang_thai = null, $ghi_chu = null, $dia_chi_nhan = null) {
    try {
        $model = new OrderModel();
        return $model->createOrderAndDeductStock((array) $gio_hang, $hinh_thuc, $kh_id, $nv_id, $trang_thai, $ghi_chu, $dia_chi_nhan);
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
?>