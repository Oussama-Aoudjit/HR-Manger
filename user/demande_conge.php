<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit();
}

$user = getUtilisateurById($user_id);
if (!$user) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $type_conge = $_POST['type_conge'];
    $raison = $_POST['raison'];

    try {
        $stmt = $pdo->prepare("INSERT INTO demandes_conge (utilisateur_id, date_debut, date_fin, type_conge, raison) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $date_debut, $date_fin, $type_conge, $raison]);
        
        $_SESSION['success'] = "Demande de congé envoyée avec succès";
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'envoi de la demande : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Manager - Demande de Congé</title>
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

        .notification-icon {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
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
            padding: clamp(10px, 2vw, 15px) clamp(15px, 3vw, 20px);
        }

        .card-title {
            margin-bottom: 0;
            font-size: clamp(1rem, 3vw, 1.25rem);
        }

        /* Form styles */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: clamp(1rem, 2vw, 1.5rem);
        }

        .form-group label {
            display: block;
            margin-bottom: clamp(0.3rem, 1vw, 0.5rem);
            font-weight: 500;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .form-control, .form-select {
            width: 100%;
            padding: clamp(0.4rem, 1vw, 0.5rem) clamp(0.6rem, 1.5vw, 0.75rem);
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn {
            padding: clamp(0.4rem, 1vw, 0.5rem) clamp(0.8rem, 2vw, 1rem);
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }

        /* Alert styles */
        .alert {
            padding: clamp(0.8rem, 1.5vw, 1rem);
            border-radius: 0.375rem;
            margin-bottom: clamp(0.8rem, 1.5vw, 1rem);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .alert-success {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
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

            .dashboard-card {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .card-body {
                padding: clamp(15px, 3vw, 20px);
            }

            /* Header adjustments for mobile */
            .top-header {
                padding: 0 10px;
            }
            .header-brand {
                font-size: clamp(1rem, 3vw, 1.2rem); /* Match dashboard.php */
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

            .form-container {
                max-width: 100%;
            }

            .btn {
                font-size: clamp(0.8rem, 2vw, 0.9rem);
                padding: clamp(0.3rem, 1vw, 0.4rem) clamp(0.6rem, 1.5vw, 0.8rem);
            }

            .form-group label {
                font-size: clamp(0.8rem, 2vw, 0.9rem);
            }

            .form-control, .form-select {
                font-size: clamp(0.8rem, 2vw, 0.9rem);
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
                            <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link active" href="demande_conge.php">
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
                <div class="main-content py-4 px-3">
                    <div class="form-container">
                        <div class="card dashboard-card border-0 rounded-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="card-title">Nouvelle demande de congé</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" id="leaveRequestForm">
                                    <!-- Date début -->
                                    <div class="mb-3 form-group">
                                        <label for="date_debut" class="form-label">Date de début</label>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut" required min="<?= date('Y-m-d') ?>">
                                    </div>

                                    <!-- Date fin -->
                                    <div class="mb-3 form-group">
                                        <label for="date_fin" class="form-label">Date de fin</label>
                                        <input type="date" class="form-control" id="date_fin" name="date_fin" required min="<?= date('Y-m-d') ?>">
                                    </div>

                                    <!-- Type congé -->
                                    <div class="mb-3 form-group">
                                        <label for="type_conge" class="form-label">Type de congé</label>
                                        <select class="form-select" id="type_conge" name="type_conge" required>
                                            <option value="">Sélectionner...</option>
                                            <option value="Congé annuel">Congé annuel</option>
                                            <option value="Maladie">Maladie</option>
                                            <option value="Maternité/Paternité">Maternité/Paternité</option>
                                            <option value="Formation">Formation</option>
                                            <option value="Autre">Autre</option>
                                        </select>
                                    </div>

                                    <!-- Raison -->
                                    <div class="mb-4 form-group">
                                        <label for="raison" class="form-label">Raison (facultatif)</label>
                                        <textarea class="form-control" id="raison" name="raison" rows="3" placeholder="Vous pouvez ajouter un commentaire..."></textarea>
                                    </div>

                                    <!-- Boutons -->
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="dashboard.php" class="btn btn-outline-secondary">Annuler</a>
                                        <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
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
            // Form validation for date range
            const form = document.getElementById('leaveRequestForm');
            const dateDebutInput = document.getElementById('date_debut');
            const dateFinInput = document.getElementById('date_fin');

            form.addEventListener('submit', function(e) {
                const dateDebut = new Date(dateDebutInput.value);
                const dateFin = new Date(dateFinInput.value);
                const today = new Date('<?= date('Y-m-d') ?>');

                if (dateDebut < today) {
                    e.preventDefault();
                    alert('La date de début ne peut pas être antérieure à aujourd\'hui.');
                    return;
                }

                if (dateFin < dateDebut) {
                    e.preventDefault();
                    alert('La date de fin ne peut pas être antérieure à la date de début.');
                    return;
                }

                if (!form.checkValidity()) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs requis.');
                }
            });

            // Dynamically update date_fin min attribute when date_debut changes
            dateDebutInput.addEventListener('change', function() {
                dateFinInput.min = dateDebutInput.value;
            });
        });
    </script>
</body>
</html>