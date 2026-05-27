<?php
session_start();
require_once 'bootstrap.php';

$controller = new App\Controllers\CartController();
$controller->index();
?>