<?php
session_start();

if (isset($_SESSION['kh_id'])) {
    unset($_SESSION['kh_id']);
    unset($_SESSION['kh_hoten']);
    unset($_SESSION['kh_avatar']);
}

header("Location: index.php");
exit();
?>
