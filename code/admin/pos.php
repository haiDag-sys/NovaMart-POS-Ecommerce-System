<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] === 'nhan_vien') {
    header("Location: ../staff/index.php");
    exit();
}

header("Location: index.php");
exit();
?>
