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

// Récupérer les statistiques
$nombre_utilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nombre_demandes = $pdo->query("SELECT COUNT(*) FROM demandes_conge")->fetchColumn();
// Dernières demandes (5 plus récentes)
$demandes_recentes = $pdo->query("SELECT d.*, u.nom, u.prenom 
                                 FROM demandes_conge d
                                 JOIN utilisateurs u ON d.utilisateur_id = u.id
                                 ORDER BY d.date_demande DESC 
                                 LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
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

        /* Content sections */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Card styles */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: clamp(15px, 3vw, 20px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: clamp(15px, 3vw, 20px);
            transition: transform 0.3s ease;
            text-align: center; /* Center text */
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: #6c757d;
            margin-bottom: clamp(5px, 1vw, 10px);
            font-weight: 500;
        }

        .stat-card .number {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 600;
            color: #212529;
            margin-bottom: clamp(10px, 2vw, 15px);
        }

        .stat-card a {
            color: #3498db;
            text-decoration: none;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .stat-card a:hover {
            text-decoration: underline;
        }

        /* Table styles */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        .table th {
            padding: clamp(10px, 2vw, 15px);
            text-align: left;
            color: #212529;
            font-weight: 600;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
        }

        .table td {
            padding: clamp(8px, 2vw, 12px) clamp(10px, 2.5vw, 15px);
            color: #212529;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
        }

        /* Status badges */
        .status {
            padding: clamp(3px, 1vw, 5px) clamp(5px, 1.5vw, 10px);
            border-radius: 20px;
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            font-weight: 500;
        }

        .en_attente {
            background-color: #fff3cd;
            color: #856404;
        }

        .approuve {
            background-color: #d4edda;
            color: #155724;
        }

        .refuse {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Section titles */
        .dashboard-title {
            color: #212529;
            margin-bottom: clamp(15px, 3vw, 25px);
            font-size: clamp(1.5rem, 4vw, 1.8rem);
            font-weight: 600;
        }

        .section-title {
            color: #212529;
            margin: clamp(15px, 3vw, 25px) 0 clamp(10px, 2vw, 15px);
            font-size: clamp(1.2rem, 3vw, 1.4rem);
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

            .dashboard-title {
                font-size: clamp(1.2rem, 3vw, 1.5rem);
            }

            .section-title {
                font-size: clamp(1rem, 2.5vw, 1.2rem);
            }

            /* Responsive table styles */
            .table-container {
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
                margin-bottom: 15px; /* Add space between requests */
                border-bottom: 1px solid #dee2e6; /* Add separator line */
                padding-bottom: 10px; /* Ensure space below separator */
            }

            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 10px; /* Slightly adjusted padding */
                border-bottom: 1px solid #e9ecef; /* Subtle border for each field */
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

            .table td:nth-child(1)::before { content: "Utilisateur"; }
            .table td:nth-child(2)::before { content: "Période"; }
            .table td:nth-child(3)::before { content: "Type"; }
            .table td:nth-child(4)::before { content: "Statut"; }
            .table td:nth-child(5)::before { content: "Date Demande"; }

            .table td:last-child {
                border-bottom: none; /* Remove border for last field in a request */
            }

            .table tr:last-child {
                border-bottom: none; /* Remove border for last request */
            }

            .status {
                font-size: clamp(0.6rem, 1.5vw, 0.7rem);
                padding: clamp(2px, 0.8vw, 4px) clamp(4px, 1.2vw, 8px);
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
            <div class="header-brand">HR Manager</div>
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
                            <a class="nav-link active" data-section="dashboard">
                                <i class="bi bi-house-door"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="utilisateurs.php">
                                <i class="bi bi-people"></i>
                                Employés
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-section="demandes" href="demandes.php">
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
                    <!-- Dashboard Section -->
                    <div id="dashboard" class="content-section active">
                        <h1 class="dashboard-title">Tableau de Bord Administrateur</h1>

                        <div class="row stats-grid justify-content-around">
                            <div class="col-md-3 col-12">
                                <div class="stat-card">
                                    <h3>Utilisateurs</h3>
                                    <div class="number"><?= $nombre_utilisateurs ?></div>
                                    <a href="utilisateurs.php">
                                        <i class="bi bi-people-fill"></i> Voir la liste
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3 col-12">
                                <div class="stat-card">
                                    <h3>Demandes totales</h3>
                                    <div class="number"><?= $nombre_demandes ?></div>
                                    <a href="demandes.php">
                                        <i class="bi bi-list-check"></i> Voir les demandes
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3 col-12">
                                <div class="stat-card">
                                    <h3>En attente</h3>
                                    <div class="number"><?= $pdo->query("SELECT COUNT(*) FROM demandes_conge WHERE statut = 'en_attente'")->fetchColumn() ?></div>
                                    <a href="demandes.php?filter=en_attente">
                                        <i class="bi bi-list-check"></i> Traiter
                                    </a>
                                </div>
                            </div>
                        </div>

                        <h2 class="section-title">Dernières Demandes</h2>
                        <div class="table-container table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Période</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Date Demande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($demandes_recentes as $demande): ?>
                                    <tr>
                                        <td data-label="Utilisateur"><?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></td>
                                        <td data-label="Période"><?= date('d/m/Y', strtotime($demande['date_debut'])) ?> - <?= date('d/m/Y', strtotime($demande['date_fin'])) ?></td>
                                        <td data-label="Type"><?= htmlspecialchars($demande['type_conge']) ?></td>
                                        <td data-label="Statut">
                                            <span class="status <?= $demande['statut'] ?>">
                                                <?= $demande['statut'] ?>
                                            </span>
                                        </td>
                                        <td data-label="Date Demande"><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></td>
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
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
            // Get all sidebar links
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            // Add click event to each sidebar link
            sidebarLinks.forEach(link => {
                if (!link.hasAttribute('href')) {
                    link.addEventListener('click', function() {
                        sidebarLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                        const sectionId = this.getAttribute('data-section');
                        document.querySelectorAll('.content-section').forEach(section => {
                            section.classList.remove('active');
                        });
                        document.getElementById(sectionId).classList.add('active');
                    });
                }
            });
        });
    </script>
</body>
</html>