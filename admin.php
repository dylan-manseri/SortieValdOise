<?php
// =========================================================================
// 1. DÉMARRAGE DE SESSION ET VÉRIFICATION D'AUTORISATION (Sécurité)
// =========================================================================

session_start();
require_once 'conf/bd_conf.php';
$successMessage = '';

if (isset($_SESSION['flash_message'])) {
  $successMessage = $_SESSION['flash_message'];
  unset($_SESSION['flash_message']); // Efface le message de la session
}

// Check if the user is logged in AND if their role is 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: connexion.php');
  exit; // Stop script execution immediately
}

// =========================================================================
// 2. CONNEXION À LA BASE DE DONNÉES (Ressource)
// =========================================================================

try {
  // Connexion effectuée SEULEMENT si l'utilisateur est un admin validé
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  // En cas d'échec de la connexion, mieux vaut loguer l'erreur et afficher un message générique.
  error_log("Erreur de connexion PDO: " . $e->getMessage()); 
  http_response_code(500);
  exit("Erreur interne du serveur.");
}

try {
// =========================================================================
// 3. SQL QUERIES FOR STATISTICS
// =========================================================================

// Define date periods
$oneMonthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));
$twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-2 weeks'));

// --------------------------------------------------
// A. Nombre d'utilisateurs inscrits le dernier mois
// --------------------------------------------------
$sqlUsersLastMonth = "SELECT COUNT(*) FROM users WHERE registration_date >= :oneMonthAgo";
$stmt = $pdo->prepare($sqlUsersLastMonth);
$stmt->execute(['oneMonthAgo' => $oneMonthAgo]);
$usersLastMonth = $stmt->fetchColumn();

// --------------------------------------------------
// B. Total des visites du site (Toutes les visites)
// --------------------------------------------------
$sqlTotalVisits = "SELECT COUNT(*) FROM visits";
$stmt = $pdo->query($sqlTotalVisits);
$totalVisits = $stmt->fetchColumn();

// --------------------------------------------------
// C. Visites acceptées les 2 dernières semaines
// --------------------------------------------------
$sqlAcceptedVisits = "
  SELECT COUNT(*) FROM visits 
  WHERE visit_date >= :twoWeeksAgo AND status = 'accepted'
";
$stmt = $pdo->prepare($sqlAcceptedVisits);
$stmt->execute(['twoWeeksAgo' => $twoWeeksAgo]);
$acceptedVisits = $stmt->fetchColumn();

// --------------------------------------------------
// D. Les 10 derniers utilisateurs enregistrés
// --------------------------------------------------
$sqlLastUsers = "
  SELECT 
    login, nom_user, prenom_user, email, role, 
    DATE_FORMAT(registration_date, '%d/%m/%Y %H:%i') AS formatted_date
  FROM users 
  ORDER BY registration_date DESC 
  LIMIT 10
";
$lastUsers = $pdo->query($sqlLastUsers)->fetchAll();

} catch(\PDOException $e) {
  error_log("Erreur d'exécution de requête SQL: " . $e->getMessage()); 
    http_response_code(500);
    exit("Erreur interne du serveur lors de la récupération des données.");
}

// =========================================================================
// 4. HTML AND DISPLAY
// =========================================================================
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de Bord Admin</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .stats-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .header-actions {
        display: flex;
        justify-content: space-between; /* Pour pousser le bouton à droite */
        align-items: center;
        margin-bottom: 20px;
    }
    .logout-button {
        background-color: #dc3545; /* Rouge pour l'action de sortie */
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .logout-button:hover {
        background-color: #c82333;
    }
  </style>
</head>
<body>

  <h1>Statistiques du Site</h1>
  <a href="logout.php" class="logout-button">Déconnexion</a>
  <a href="rajouterAdmin.php" class="logout-button">rajouter un administrateur</a>

  <div class="stats-grid">

    <?php if (!empty($successMessage)): ?>
    <div id="flash-notification" 
         style="padding: 15px; margin-bottom: 20px; border: 1px solid green; background-color: #e6ffe6; color: green; font-weight: bold; text-align: center; border-radius: 5px;">
        <?= htmlspecialchars($successMessage) ?>
    </div>
<?php endif; ?>

    <div class="stats-box">
      <h2>Utilisateurs (Dernier Mois)</h2>
      <p style="font-size: 2em; color: #007bff;"><?= htmlspecialchars($usersLastMonth) ?></p>
    </div>

    <div class="stats-box">
      <h2>Total des Visites</h2>
      <p style="font-size: 2em; color: #28a745;"><?= htmlspecialchars($totalVisits) ?></p>
    </div>

    <div class="stats-box">
      <h2>Visites Acceptées (2 Semaines)</h2>
      <p style="font-size: 2em; color: #ffc107;"><?= htmlspecialchars($acceptedVisits) ?></p>
    </div>
  </div>

  <hr>

  <h2>Derniers 10 Utilisateurs Enregistrés</h2>

  <?php if ($lastUsers): ?>
    <table>
      <thead>
        <tr>
          <th>Login</th>
          <th>Nom Prénom</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Date d'Inscription</th>
          <th>Actions</th>         </tr>
      </thead>
      <tbody>
        <?php foreach ($lastUsers as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['login']) ?></td>
          <td><?= htmlspecialchars($user['nom_user']) . ' ' . htmlspecialchars($user['prenom_user']) ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td><?= htmlspecialchars($user['role']) ?></td>
          <td><?= htmlspecialchars($user['formatted_date']) ?></td>
          <td>
            <a href="gestionUtilisateurs.php?login=<?= urlencode($user['login']) ?>">Gérer touts les utilisateurs</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Aucun utilisateur trouvé.</p>
  <?php endif; ?>

</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionne l'élément de notification
        const notification = document.getElementById('flash-notification');

        // Vérifie si la notification existe (elle n'existe que si $successMessage n'était pas vide)
        if (notification) {
            // Définit un minuteur pour masquer l'élément après 10 secondes (10000 millisecondes)
            setTimeout(function() {
                // Utilise la propriété CSS display = 'none' pour le masquer
                notification.style.display = 'none';
            }, 10000); // 10 secondes
        }
    });
</script>
</html>