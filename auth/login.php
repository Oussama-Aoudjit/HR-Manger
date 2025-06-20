<?php
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: ../user/dashboard.php');
                }
                exit();
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
           
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2) !important;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
    
    </style>
</head>
<body>
<section class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-10 login-card shadow-lg p-5">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h2 class="fw-bold mb-4 text-dark">Simplifiez votre gestion RH</h2>
                        <p class="text-muted">
                            Automatisez le recrutement, la formation et la gestion de vos employés avec notre solution tout-en-un pour les RH. Gagnez du temps et améliorez l’efficacité.
                        </p>
                        <ul class="list-unstyled mt-4">
                            <li><i class="fa fa-check text-primary me-2"></i> Suivi des talents</li>
                            <li><i class="fa fa-check text-primary me-2"></i> Intégration simplifiée</li>
                            <li><i class="fa fa-check text-primary me-2"></i> Statistiques en temps réel</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-4 text-indigo-600">Se connecter</h5>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form class="p-2" method="POST" action="">
                            <div class="mb-4 form-floating">
                                <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Adresse email" required>
                                <label for="email">Adresse email</label>
                            </div>
                            <div class="mb-4 form-floating">
                                <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Mot de passe" required>
                                <label for="password">Mot de passe</label>
                            </div>
                            <button class="btn btn-primary btn-lg w-100 mb-3" type="submit">Se connecter</button>
                            <div class="text-center">
                                <p class="fs-6 text-muted">
                                    Pas encore de compte ? 
                                    <a href="./register.php" class="text-primary fw-semibold text-decoration-none">Créer un compte</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>