<?php
require_once __DIR__ . '/../bootstrap.php';

$conn = db();

if (!function_exists('formatMoney')) {
    function formatMoney($number) {
        return number_format($number, 0, ',', '.') . ' đ';
    }
}
?>