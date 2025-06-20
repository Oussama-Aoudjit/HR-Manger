<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user']['id'];
$user = getUtilisateurById($user_id);

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY nom, prenom");
$utilisateurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Manager - Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Header styles */
        .top-header {
            height: 60px;
            border-bottom: 1px solid #e9ecef;
            background-color: white;
            display: flex;
            align-items: center;
            padding: 0 15px;
            justify-content: space-between;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-brand {
            font-weight: 600;
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: #212529;
        }

        .header-icons {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .user-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .user-icon .fas.fa-user {
            width: 35px;
            height: 35px;
            background-color: rgb(24, 68, 135);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .user-text {
            display: flex;
            flex-direction: column;
            max-width: 150px;
            overflow: hidden;
        }

        .user-text span:first-child {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-text .role {
            font-size: 0.9rem;
            color: #6c757d;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Sidebar styles */
        .sidebar {
            width: 280px;
            background-color: white;
            height: 100vh;
            border-right: 1px solid #e9ecef;
            padding-top: 20px;
            position: fixed;
            top: 60px;
            left: 0;
            transition: transform 0.3s ease;
            z-index: 999;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .nav-link {
            color: #212529;
            padding: clamp(10px, 2vw, 15px) clamp(15px, 3vw, 20px);
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 0;
            cursor: pointer;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .nav-link:hover {
            background-color: #f8f9fa;
        }

        .nav-link.active {
            background-color: #f1f3f5;
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .main-content {
            padding: clamp(10px, 2vw, 20px);
            margin-top: 60px;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .main-content.full-width {
            margin-left: 0;
        }

        .content-wrapper {
            min-height: calc(100vh - 60px);
            overflow-y: auto;
        }

        /* Card styles */
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: clamp(15px, 3vw, 20px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: clamp(15px, 3vw, 20px);
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
            background-color: rgba(0,0,0,.03);
            padding: clamp(10px, 2vw, 15px) clamp(15px, 3vw, 20px);
        }

        .card-title {
            margin-bottom: 0;
            font-size: clamp(1.2rem, 3vw, 1.25rem);
            font-weight: 600;
            color: #212529;
        }

        /* Table styles */
        .table-responsive {
            border-radius: 8px;
            width: 100%;
        }

        .table {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
            margin-bottom: 0;
        }

        .table th {
            padding: clamp(10px, 2vw, 15px);
            text-align: left;
            color: #212529;
            font-weight: 600;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            white-space: nowrap;
        }

        .table td {
            padding: clamp(8px, 2vw, 12px) clamp(5px, 1.5vw, 10px);
            color: #212529;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            white-space: nowrap;
        }

        /* Status badges */
        .badge-admin {
            background-color: #6f42c1;
            color: white;
            padding: clamp(3px, 1vw, 5px) clamp(5px, 1.5vw, 10px);
            border-radius: 20px;
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            font-weight: 500;
        }

        .badge-user {
            background-color: #20c997;
            color: white;
            padding: clamp(3px, 1vw, 5px) clamp(5px, 1.5vw, 10px);
            border-radius: 20px;
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            font-weight: 500;
        }

        /* Button styles */
        .btn-sm {
            padding: clamp(0.15rem, 0.8vw, 0.25rem) clamp(0.3rem, 1vw, 0.5rem);
            font-size: clamp(0.7rem, 1.5vw, 0.875rem);
            white-space: nowrap;
            min-width: 80px;
            text-align: center;
        }

        .btn-refuse {
            background-color: #dc3545;
            color: white;
        }

        .btn-refuse:hover {
            background-color: #c82333;
            color: white;
        }

        /* Ensure buttons are visible on mobile */
        .btn-group.btn-group-sm {
            display: inline-flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Hamburger menu button */
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #212529;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .card-title {
                font-size: clamp(1rem, 2.5vw, 1.2rem);
            }

            .table th,
            .table td {
                font-size: clamp(0.75rem, 1.5vw, 0.85rem);
                padding: clamp(6px, 1.5vw, 8px) clamp(5px, 1vw, 8px);
            }

            .btn-sm {
                font-size: clamp(0.65rem, 1.5vw, 0.75rem);
                padding: clamp(0.1rem, 0.5vw, 0.2rem) clamp(0.2rem, 0.8vw, 0.4rem);
                min-width: 70px;
            }

            .top-header {
                padding: 0 10px;
            }

            .header-brand {
                font-size: clamp(1rem, 3vw, 1.2rem);
            }

            .header-icons {
                max-width: 200px;
                flex-shrink: 0;
            }

            .user-icon {
                gap: 8px;
            }

            .user-text {
                max-width: 120px;
                font-size: 0.9rem;
            }

            .user-text .role {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }

            .table-responsive {
                overflow-x: hidden; /* Remove horizontal scrolling */
            }

            .table {
                display: block; /* Switch to block layout */
                width: 100%;
                min-width: auto; /* Remove minimum width */
            }

            .table thead {
                display: none; /* Hide table headers on mobile */
            }

            .table tbody,
            .table tr {
                display: block;
                width: 100%;
            }

            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px;
                border-bottom: 1px solid #dee2e6;
                width: 100%;
                box-sizing: border-box;
            }

            .table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 10px;
                min-width: 100px;
                color: #212529;
            }

            .table td:nth-child(1)::before { content: "ID"; }
            .table td:nth-child(2)::before { content: "Nom"; }
            .table td:nth-child(3)::before { content: "Prénom"; }
            .table td:nth-child(4)::before { content: "Email"; }
            .table td:nth-child(5)::before { content: "Rôle"; }
            .table td:nth-child(6)::before { content: "Inscrit le"; }
            .table td:nth-child(7)::before { content: "Actions"; }

            .table td:last-child {
                border-bottom: none;
            }

            .btn-group.btn-group-sm {
                display: block; /* Stack buttons vertically */
                width: 100%;
            }

            .btn-group.btn-group-sm a {
                width: 100%; /* Full width buttons */
                display: block;
                text-align: center;
                margin-bottom: 5px;
            }

            .btn-sm {
                font-size: clamp(0.6rem, 1.5vw, 0.7rem);
                padding: clamp(0.05rem, 0.3vw, 0.15rem) clamp(0.15rem, 0.5vw, 0.3rem);
                min-width: 50px; /* Reduced for mobile */
            }

            .card-title {
                font-size: clamp(0.9rem, 2vw, 1rem);
            }

            .header-icons {
                max-width: 150px;
            }

            .user-text {
                max-width: 100px;
                font-size: 0.85rem;
                line-height: 1.2;
            }

            .user-text .role {
                font-size: 0.75rem;
            }

            .user-icon .fas.fa-user {
                width: 32px;
                height: 32px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-list menu-toggle me-3" aria-label="Toggle menu"></i>
            <div class="header-brand">HR Manager - Gestion des Utilisateurs</div>
        </div>
        <div class="header-icons">
            <div class="user-icon">
                <i class="fas fa-user" aria-label="User profile"></i>
                <div class="user-text">
                    <span><?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?></span>
                    <span class="role"><?= htmlspecialchars($user['role']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-auto">
                <div class="sidebar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-house-door"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="utilisateurs.php">
                                <i class="bi bi-people"></i>
                                Employés
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./demandes.php">
                                <i class="bi bi-list-check"></i>
                                Demandes de congé
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col content-wrapper">
                <div class="main-content">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title">Gestion des Utilisateurs</h5>
                        </div>
                        <div class="card-body">
                            <!-- Affichage des messages de succès ou d'erreur -->
                            <?php
                            if (isset($_GET['success']) && $_GET['success'] === 'user_deleted') {
                                echo '<div class="alert alert-success">Utilisateur supprimé avec succès.</div>';
                            } elseif (isset($_GET['error'])) {
                                $error_message = '';
                                switch ($_GET['error']) {
                                    case 'invalid_id':
                                        $error_message = 'ID invalide.';
                                        break;
                                    case 'cannot_delete_self':
                                        $error_message = 'Vous ne pouvez pas supprimer votre propre compte.';
                                        break;
                                    case 'no_user_found':
                                        $error_message = 'Utilisateur non trouvé.';
                                        break;
                                    case 'delete_failed':
                                        $error_message = 'Échec de la suppression. Veuillez réessayer.';
                                        break;
                                    default:
                                        $error_message = 'Une erreur inconnue est survenue.';
                                }
                                echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                            }
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>Inscrit le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($utilisateurs as $user) : ?>
                                        <tr>
                                            <td data-label="ID"><?= $user['id'] ?></td>
                                            <td data-label="Nom"><?= htmlspecialchars($user['nom']) ?></td>
                                            <td data-label="Prénom"><?= htmlspecialchars($user['prenom']) ?></td>
                                            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                                            <td data-label="Rôle">
                                                <span class="badge rounded-pill <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                                    <?= $user['role'] ?>
                                                </span>
                                            </td>
                                            <td data-label="Inscrit le"><?= date('d/m/Y H:i', strtotime($user['date_inscription'])) ?></td>
                                            <td data-label="Actions">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($user['id'] != $_SESSION['user']['id']) : ?>
                                                        <a href="delete_user.php?id=<?= $user['id'] ?>" 
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" 
                                                           class="btn btn-refuse">
                                                           <i class="bi bi-trash"></i> Supprimer
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            // Function to update sidebar visibility based on screen size
            function updateSidebarVisibility() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('collapsed', 'active');
                    mainContent.classList.remove('full-width');
                } else {
                    sidebar.classList.add('collapsed');
                    sidebar.classList.remove('active');
                    mainContent.classList.add('full-width');
                }
            }
            // Set initial state
            updateSidebarVisibility();
            // Toggle sidebar on menu button click
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('full-width');
            });
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992 && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('full-width');
                }
            });
            // Adjust sidebar state on window resize
            window.addEventListener('resize', updateSidebarVisibility);
        });
    </script>
</body>
</html>