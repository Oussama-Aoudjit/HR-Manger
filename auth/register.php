<?php
require_once '../includes/config.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT)]);

            // Fetch the newly created user
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: ../user/dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la récupération des données utilisateur.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error'] = "Cet email est déjà utilisé.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription : " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inscription</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <section class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="container">
      <div class="row justify-content-center align-items-center">
        <div class="col-lg-10 bg-white shadow rounded-4 p-5">
          <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
              <h2 class="fw-bold mb-4">Créer votre compte</h2>
              <p class="text-muted">
                Remplissez le formulaire pour vous inscrire et rejoindre notre plateforme.
              </p>
            </div>
            <div class="col-md-6">
              <h5 class="mb-4">Formulaire d'inscription</h5>
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
              <?php endif; ?>
              
              <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
              <?php endif; ?>

              <form method="POST" action="">
                <div class="mb-3">
                  <input type="text" name="nom" class="form-control form-control-lg" placeholder="Nom" required />
                </div>

                <div class="mb-3">
                  <input type="text" name="prenom" class="form-control form-control-lg" placeholder="Prénom" required />
                </div>

                <div class="mb-3">
                  <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required />
                </div>

                <div class="mb-3">
                  <input type="password" name="password" class="form-control form-control-lg" placeholder="Mot de passe" required />
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">S'inscrire</button>
              </form>

              <div class="text-center mt-3">
                <p class="text-muted">
                  Déjà inscrit ? <a href="login.php" class="text-primary">Connectez-vous</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

