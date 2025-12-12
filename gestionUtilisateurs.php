<?php
session_start();
require_once 'conf/bd_conf.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit; 
}

if (!isset($_GET['login']) || empty($_GET['login'])) {
    http_response_code(400);
    exit("Erreur: Aucun utilisateur spécifié.");
}


$title = "Page Gestion utilisateur";
$css = "gestionUtilisateurs";  
$description = "Page dedie a la gestion Utilisateur";

$cookieConsent = $_COOKIE['cookieConsent'] ?? null;
$style = "light";

if (isset($_GET["style"]) && in_array($_GET["style"], ["light","dark"], true)) {
    $style = $_GET["style"];
    if ($cookieConsent === 'true') {
        setcookie("style", $style, time() + 60*60*24*30, "/");
    }
} elseif ($cookieConsent === 'true' && isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light','dark'], true)) {
    $style = $_COOKIE['style'];
}

if ($cookieConsent === 'true' && isset($_COOKIE["date_last_visit"])) {
    setcookie("date_last_visit", time(), time() + 60*60*24*30, "/");
}

$bascule = ($style === "light") ? "dark" : "light";




$targetLogin = $_GET['login'];
$errorMessage = '';
$successMessage = '';


if (!isset($pdo)) {
    http_response_code(500);
    exit("Erreur: connexion à la base de données indisponible.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        
        // --- NOUVELLE VÉRIFICATION DE SÉCURITÉ ---
        if ($targetLogin === ($_SESSION['login'] ?? null)) {
             $errorMessage = "Vous ne pouvez pas supprimer votre propre compte depuis cette interface.";
        } else {
             // --- SUPPRESSION DE L'UTILISATEUR ---
             $sqlDelete = "DELETE FROM users WHERE login = ?";
             $stmt = $pdo->prepare($sqlDelete);
             
             if ($stmt->execute([$targetLogin])) {
                 header('Location: admin.php?deleted=success'); 
                 exit;
             } else {
                 $errorMessage = "Erreur lors de la suppression de l'utilisateur.";
             }
        }
    }
     elseif ($action === 'update') {
        // --- MODIFICATION DES DONNÉES DE L'UTILISATEUR ---
        
        $nom_user = trim($_POST['nom_user'] ?? '');
        $prenom_user = trim($_POST['prenom_user'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? '');

        // Validation simple
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $errorMessage = "L'adresse e-mail est invalide.";
        } else {
            $sqlCheckEmail = "SELECT COUNT(*) FROM users WHERE email = ? AND login != ?";
            $stmtCheck = $pdo->prepare($sqlCheckEmail);
            $stmtCheck->execute([$email, $targetLogin]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                 $errorMessage = "Cet e-mail est déjà utilisé par un autre compte.";
            } 
            
            // Validation 2: Rôle valide
            elseif (!in_array($role, ['user', 'admin', 'pending'])) { 
                 $errorMessage = "Rôle invalide.";
            } 
            
            // Succès de toutes les validations : Exécution de l'UPDATE
            else {
                 $sqlUpdate = "UPDATE users SET nom_user = ?, prenom_user = ?, email = ?, role = ? WHERE login = ?";
                 $stmt = $pdo->prepare($sqlUpdate);
                 
                 if ($stmt->execute([$nom_user, $prenom_user, $email, $role, $targetLogin])) {
                     $successMessage = "Les informations de l'utilisateur ont été mises à jour.";
                 } else {
                     $errorMessage = "Erreur lors de la mise à jour des données.";
                 }
            }
        }
    }
}

$sqlFetch = "SELECT login, nom_user, prenom_user, email, role, status FROM users WHERE login = ?";
$stmt = $pdo->prepare($sqlFetch);
$stmt->execute([$targetLogin]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    exit("Utilisateur non trouvé.");
}
include "includes/pageParts/header.php";
?>
<div class="page-wrapper">
<div class="manage-container">
        <h1>Gestion de l'Utilisateur : <?= htmlspecialchars($user['login']) ?></h1>

        <?php if ($errorMessage): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="update">

            <div>
                <label for="nom_user">Nom :</label>
                <input type="text" id="nom_user" name="nom_user" value="<?= htmlspecialchars($user['nom_user']) ?>" required>
            </div>

            <div>
                <label for="prenom_user">Prénom :</label>
                <input type="text" id="prenom_user" name="prenom_user" value="<?= htmlspecialchars($user['prenom_user']) ?>" required>
            </div>
            
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div>
                <label for="role">Rôle :</label>
                <select id="role" name="role" required>
                    <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>Utilisateur Standard</option>
                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                    <option value="pending" <?= ($user['role'] == 'pending' || !in_array($user['role'], ['user', 'admin'])) ? 'selected' : '' ?>>En attente</option>
                </select>
            </div>
            
            <button type="submit">Mettre à jour</button>
        </form>
        
        <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? Cette action est irréversible.');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="delete-btn">Supprimer l'Utilisateur</button>
        </form>

        <p><a href="admin.php">Retour au Tableau de Bord</a></p>
</div>
</div>
<?php include "includes/pageParts/footer.php"; ?>
</html>