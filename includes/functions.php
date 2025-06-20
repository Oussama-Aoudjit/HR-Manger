<?php
require_once 'config.php';

function getUtilisateurById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getDemandesUtilisateur($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM demandes_conge WHERE utilisateur_id = ? ORDER BY date_demande DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getNotifications($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE utilisateur_id = ? ORDER BY date_notification DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function marquerNotificationLue($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET vue = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}
function getServiceName($service_id) {
    global $pdo;
    
    if (!$service_id) return 'Non attribué';
    
    try {
        $stmt = $pdo->prepare("SELECT nom FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        return $service ? $service['nom'] : 'Non attribué';
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du service: " . $e->getMessage());
        return 'Erreur';
    }
}
?>