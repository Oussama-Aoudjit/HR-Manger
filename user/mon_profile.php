<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Activer le mode debug (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$user = getUtilisateurById($user_id);
$notifications = getNotifications($user_id);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($email)) {
        $_SESSION['error_message'] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "L'email n'est pas valide.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $updates = ['email' => $email];
            $params = [$email, $user_id];
            
            if (!empty($password)) {
                $updates['password'] = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE utilisateurs SET email = ?, password = ? WHERE id = ?";
                $params = [$email, $updates['password'], $user_id];
            } else {
                $query = "UPDATE utilisateurs SET email = ? WHERE id = ?";
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            // Update session user data
            $_SESSION['user']['email'] = $email;
            $_SESSION['success_message'] = "Profil mis à jour avec succès.";
            header('Location: mon_profile.php');
            exit();
        } catch (PDOException $e) {
            error_log("Erreur PDO: " . $e->getMessage());
            if ($e->getCode() == '23000') { // Duplicate email
                $_SESSION['error_message'] = "Échec de la modification : Cet email est déjà utilisé.";
            } else {
                $_SESSION['error_message'] = "Échec de la modification : " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Employé</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Font Awesome for user icon -->
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
            transform: translateX(0); /* Visible by default */
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

        /* Profile card styles */
        .profile-card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            background: white;
            padding: clamp(15px, 3vw, 30px);
            margin-bottom: clamp(15px, 3vw, 30px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: clamp(15px, 3vw, 30px);
        }

        .profile-avatar {
            width: clamp(100px, 20vw, 150px);
            height: clamp(100px, 20vw, 150px);
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: clamp(10px, 2vw, 15px);
            border: 5px solid #f8f9fa;
        }

        .profile-name {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            font-weight: 600;
            margin-bottom: clamp(5px, 1vw, 10px);
        }

        .profile-title {
            color: #6c757d;
            margin-bottom: clamp(10px, 2vw, 20px);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .profile-details {
            margin-bottom: clamp(15px, 3vw, 30px);
        }

        .detail-item {
            margin-bottom: clamp(10px, 2vw, 15px);
            padding-bottom: clamp(10px, 2vw, 15px);
            border-bottom: 1px solid #f1f3f5;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: clamp(5px, 1vw, 5px);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .detail-value {
            color: #212529;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .btn-edit {
            width: 100%;
            font-size: clamp(0.9rem, 2vw, 1rem);
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

            .profile-card {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .profile-avatar {
                width: clamp(80px, 20vw, 100px);
                height: clamp(80px, 20vw, 100px);
            }

            .profile-name {
                font-size: clamp(1rem, 3vw, 1.2rem);
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

            .form-control {
                font-size: clamp(0.8rem, 2vw, 0.9rem);
            }

            .btn-lg {
                font-size: clamp(0.9rem, 2vw, 1rem);
                padding: 8px;
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
            <div class="header-brand">HR Manager - Mon Profil</div>
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
                            <a class="nav-link active" href="mon_profile.php">
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
                <div class="main-content container py-5">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8">
                            <!-- Profile Card -->
                            <div class="card profile-card border-0 rounded-3 shadow-lg mb-5">
                                <div class="card-body text-center">
                                    <!-- Profile Avatar -->
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['prenom'] . ' ' . $user['nom']) ?>&background=random"
                                         alt="Avatar"
                                         class="rounded-circle mb-4 profile-avatar">
                                    <h2 class="fw-bold"><?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?></h2>
                                    <p class="text-muted"><?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                                    <p class="text-muted small">Inscrit le <?= date('d/m/Y à H:i', strtotime($user['date_inscription'])) ?></p>
                                </div>

                                <!-- Horizontal Line -->
                                <hr class="my-4">

                                <!-- Profile Details -->
                                <div class="card-body text-dark">
                                    <!-- Messages -->
                                    <?php if (isset($_SESSION['error_message'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= htmlspecialchars($_SESSION['error_message']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        <?php unset($_SESSION['error_message']); ?>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['success_message'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= htmlspecialchars($_SESSION['success_message']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        <?php unset($_SESSION['success_message']); ?>
                                    <?php endif; ?>

                                    <h5 class="fw-semibold mb-4">Mes informations</h5>
                                    <ul class="list-group list-group-flush mb-4">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>ID :</strong> <span class="text-muted"><?= htmlspecialchars($user['id']) ?></span>
                                            <i class="bi bi-person-check text-primary"></i>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Email :</strong> <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                                            <i class="bi bi-envelope text-primary"></i>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Rôle :</strong> <span class="text-muted"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                                            <i class="bi bi-briefcase text-primary"></i>
                                        </li>
                                    </ul>

                                    <!-- Edit Profile Form -->
                                    <h5 class="fw-semibold mb-4">Modifier mes informations</h5>
                                    <form method="POST" action="mon_profile.php" id="profileForm">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Nouvel email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nouveau mot de passe</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
                                        </div>
                                        <div class="mb-4">
                                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">Mettre à jour</button>
                                        </div>
                                    </form>
                                </div>
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
            // Form validation
            const form = document.getElementById('profileForm');
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const email = document.getElementById('email').value;

                if (!email) {
                    e.preventDefault();
                    alert('L\'email est requis.');
                    return;
                }

                if (password && password !== confirmPassword) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                    return;
                }
            });
        });
    </script>
</body>
</html>