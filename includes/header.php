<?php
require_once __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RH_RH - Gestion des Congés</title>
    <link rel="stylesheet" href="/rh_rh/assets/css/style.css"> <!-- Chemin corrigé -->
    <?php if (isAdmin()) : ?>
        <link rel="stylesheet" href="/rh_rh/assets/css/">
    <?php endif; ?>
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #3498db;
            --main-bg: #f5f5f5;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #ecf0f1;
            --text-gray: #7f8c8d;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--main-bg);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: var(--text-light);
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .logo {
            text-align: center;
            padding: 20px 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo h1 {
            font-size: 24px;
            color: var(--text-light);
            font-weight: 600;
            margin: 0;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0 10px;
            margin: 0;
        }
        
        .nav-menu li {
            margin-bottom: 5px;
        }
        
        .nav-menu a {
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-menu a.active {
            background-color: var(--sidebar-active);
            font-weight: 500;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .top-bar {
            background-color: var(--card-bg);
            padding: 15px 25px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--sidebar-bg);
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 80%;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                font-size: 24px;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="logo">
            <h1>RH<span>_RH</span></h1>
        </div>
        
        <nav>
            <ul class="nav-menu">
                <?php if (isLoggedIn()) : ?>
                    <?php if (isAdmin()) : ?>
                        <li><a href="/rh_rh/admin/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a></li>
                        <li><a href="/rh_rh/admin/demandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'demandes.php' ? 'active' : '' ?>">Demandes</a></li>
                        <li><a href="/rh_rh/admin/presence.php" class="<?= basename($_SERVER['PHP_SELF']) == 'presence.php' ? 'active' : '' ?>">Présences</a></li>
                        <li><a href="/rh_rh/admin/utilisateurs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'utilisateurs.php' ? 'active' : '' ?>">Utilisateurs</a></li>
                    <?php else : ?>
                        <li><a href="/rh_rh/user/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a></li>
                        <li><a href="/rh_rh/user/mark_presence.php" class="<?= basename($_SERVER['PHP_SELF']) == 'mark_presence.php' ? 'active' : '' ?>">Déclarer présence</a></li>
                        <li><a href="/rh_rh/user/demande_conge.php" class="<?= basename($_SERVER['PHP_SELF']) == 'demande_conge.php' ? 'active' : '' ?>">Demander un congé</a></li>
                        <li><a href="/rh_rh/user/historique.php" class="<?= basename($_SERVER['PHP_SELF']) == 'historique.php' ? 'active' : '' ?>">Historique</a></li>
                    <?php endif; ?>
                    <li><a href="/rh_rh/auth/logout.php">Déconnexion</a></li>
                <?php else : ?>
                    <li><a href="/rh_rh/auth/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content Area -->
    <main class="main-content">
        <header class="top-bar">
            <div class="menu-toggle" style="display: none;">☰</div>
            <div class="user-info">
                <?php if (isLoggedIn()) : ?>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
                    <span><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></span>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="container">