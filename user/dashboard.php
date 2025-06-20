<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$user = getUtilisateurById($user_id);
$demandes = getDemandesUtilisateur($user_id);
$notifications = getNotifications($user_id);

// Compter les demandes par statut
$stats = [
    'total' => count($demandes),
    'approuve' => 0,
    'refuse' => 0,
    'en_attente' => 0
];

foreach ($demandes as $demande) {
    $stats[$demande['statut']]++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Employé</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
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
            flex-shrink: 0; /* Prevent shrinking */
        }
        .user-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0; /* Prevent icon and text from shrinking */
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
            max-width: 150px; /* Limit width to prevent overflow */
            overflow: hidden;
        }
        .user-text span:first-child {
            overflow: hidden;
            text-overflow: ellipsis; /* Truncate long names */
        }
        .user-text .role {
            font-size: 0.9rem;
            color: #6c757d;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 15px;
            height: 15px;
            font-size: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
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
            padding: 12px 20px;
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
            padding: 20px;
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
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            background: white;
        }

        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
            background-color: rgba(0,0,0,.03);
            padding: 15px 20px;
        }

        .card-title {
            margin-bottom: 0;
            font-size: clamp(1rem, 3vw, 1.25rem);
        }

        /* Stat cards */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            color: #6c757d;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 600;
            color: #212529;
        }

        /* Status badges */
        .badge-en_attente {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-approuve {
            background-color: #28a745;
        }

        .badge-refuse {
            background-color: #dc3545;
        }

        /* Table styles */
        .table th {
            font-weight: 500;
            color: #6c757d;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        }

        .table td {
            vertical-align: middle;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        }

        /* Notification styles */
        .notification.unread {
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }

        .notification.read {
            opacity: 0.8;
        }

        .see-all {
            display: block;
            text-align: right;
            margin-bottom: 10px;
            color: #6c757d;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
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

            .stat-card {
                margin-bottom: 15px;
            }

            .dashboard-card {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }

            .table th, .table td {
                white-space: nowrap;
            }

            .header-brand {
                font-size: clamp(1rem, 3vw, 1.2rem);
            }

            .top-header {
                padding: 0 10px;
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

            .main-content {
                padding: 15px;
            }
        }

        @media (max-width: 576px) {
            .table {
                display: block;
                overflow-x: auto;
            }

            .table thead {
                display: none;
            }

            .table tbody, .table tr {
                display: block;
            }

            .table tr {
                margin-bottom: 15px;
                border-bottom: 1px solid #dee2e6;
                padding: 10px 0;
            }

            .table td {
                display: block;
                text-align: left;
                padding: 5px 10px;
                position: relative;
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            }

            .table td::before {
                content: attr(data-label);
                font-weight: bold;
                display: inline-block;
                width: 40%;
                padding-right: 10px;
            }

            .header-icons {
                max-width: 150px; /* Adjusted for very small screens */
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

            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-list menu-toggle me-3" aria-label="Toggle menu"></i>
            <div class="header-brand">HR Manager - Employé</div>
        </div>
        <div class="header-icons">
            <div class="user-icon">
                <i class="fas fa-user"></i>
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
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-house-door"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mon_profile.php">
                                <i class="bi bi-person"></i>
                                Mon Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="demande_conge.php">
                                <i class="bi bi-briefcase"></i>
                                Demande de Congé
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="historique.php">
                                <i class="bi bi-clock-history"></i>
                                Historique
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
                    <h2>Bienvenue, <?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?>!</h2>
                    <p class="text-muted">Votre tableau de bord personnel</p>

                    <div class="row mt-4">
                        <div class="col-12 col-md-6 col-lg-3 mb-3">
                            <div class="stat-card">
                                <h3>Demandes totales</h3>
                                <div class="number"><?= $stats['total'] ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-3">
                            <div class="stat-card">
                                <h3>Approuvées</h3>
                                <div class="number"><?= $stats['approuve'] ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-3">
                            <div class="stat-card">
                                <h3>Refusées</h3>
                                <div class="number"><?= $stats['refuse'] ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-3">
                            <div class="stat-card">
                                <h3>En attente</h3>
                                <div class="number"><?= $stats['en_attente'] ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Mes dernières demandes</h5>
                                    <a href="demande_conge.php" class="btn btn-primary btn-sm">Nouvelle demande</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Période</th>
                                                    <th>Type</th>
                                                    <th>Statut</th>
                                                    <th>Date Demande</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($demandes, 0, 5) as $demande) : ?>
                                                <tr>
                                                    <td data-label="Période"><?= date('d/m/Y', strtotime($demande['date_debut'])) ?> - <?= date('d/m/Y', strtotime($demande['date_fin'])) ?></td>
                                                    <td data-label="Type"><?= $demande['type_conge'] ?></td>
                                                    <td data-label="Statut">
                                                        <span class="badge rounded-pill bg-<?= $demande['statut'] === 'approuve' ? 'success' : ($demande['statut'] === 'refuse' ? 'danger' : 'warning') ?>">
                                                            <?= str_replace('_', ' ', $demande['statut']) ?>
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
                        <div class="col-12 col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Notifications récentes</h5>
                                    <?php if (count($notifications) > 3) : ?>
                                        <a href="historique.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($notifications, 0, 3) as $notif) : ?>
                                        <div class="list-group-item <?= $notif['vue'] ? 'read' : 'unread' ?>" data-id="<?= $notif['id'] ?>">
                                            <div class="d-flex justify-content-between flex-wrap">
                                                <p class="mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['date_notification'])) ?></small>
                                            </div>
                                            <?php if ($notif['lien']) : ?>
                                                <a href="<?= $notif['lien'] ?>" class="btn btn-sm btn-link p-0">Voir</a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
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
            // Marquer les notifications comme lues au clic
            document.querySelectorAll('.notification.unread').forEach(notification => {
                notification.addEventListener('click', function() {
                    const notificationId = this.getAttribute('data-id');
                    this.classList.remove('unread');
                    this.classList.add('read');
                });
            });
        });
    </script>
</body>
</html>