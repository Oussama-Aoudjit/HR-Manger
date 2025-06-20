<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}
// Redirection vers le tableau de bord admin
header('Location: dashboard.php');
exit();
?>