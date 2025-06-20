<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
} else {
    header('Location: auth/login.php');
    exit();
}
?>