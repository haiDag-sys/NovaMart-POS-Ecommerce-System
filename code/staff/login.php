<?php
session_start();
require_once '../bootstrap.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['hoten'], $_SESSION['role']);
}

$_GET['portal'] = 'staff';
$controller = new App\Controllers\AdminAuthController();
$controller->handle();
?>
