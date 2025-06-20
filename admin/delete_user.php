<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: utilisateurs.php?error=invalid_id');
    exit();
}

$user_id = (int)$_GET['id'];

if ($user_id == $_SESSION['user']['id']) {
    header('Location: utilisateurs.php?error=cannot_delete_self');
    exit();
}

try {
    // Préparer et exécuter la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);

    // Vérifier si la suppression a réussi
    if ($stmt->rowCount() > 0) {
        header('Location: utilisateurs.php?success=user_deleted');
    } else {
        header('Location: utilisateurs.php?error=no_user_found');
    }
    exit();
} catch (PDOException $e) {
    error_log("Échec de suppression : " . $e->getMessage());
    header('Location: utilisateurs.php?error=delete_failed');
    exit();
}
?>