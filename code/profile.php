<?php
session_start();
require_once 'bootstrap.php';

$controller = new App\Controllers\ProfileController();
$controller->index();
?>