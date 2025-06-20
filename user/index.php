<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}
header('Location: dashboard.php');
exit();
?>