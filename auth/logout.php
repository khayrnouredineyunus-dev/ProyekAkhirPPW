<?php
// auth/logout.php
require_once __DIR__ . '/../config.php';
startSecureSession();
$_SESSION = [];
session_destroy();
header('Location: ../auth/login.php');
exit;