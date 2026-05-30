<?php
require_once __DIR__ . '/../config.php';
startSecureSession();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;