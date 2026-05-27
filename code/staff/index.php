<?php
session_start();
require_once '../bootstrap.php';
require_staff();
header('Location: pos.php');
exit();
