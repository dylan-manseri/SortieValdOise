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


// Check if the user is logged in AND if their role is 'admin'

$targetLogin = $_GET['login'];
$errorMessage = '';
$successMessage = '';

// Configuration PDO (reprise de votre code précédent)

if (!isset($pdo)) {
    http_response_code(500);
    exit("Erreur: connexion à la base de données indisponible.");
}


// =========================================================================
// TRAITEMENT DU FORMULAIRE (Modification et Suppression)
// =========================================================================

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


// =========================================================================
// RÉCUPÉRATION DES DONNÉES ACTUELLES DE L'UTILISATEUR
// Cette requête doit s'exécuter après le POST pour obtenir les données MAJ
// =========================================================================

$sqlFetch = "SELECT login, nom_user, prenom_user, email, role, status FROM users WHERE login = ?";
$stmt = $pdo->prepare($sqlFetch);
$stmt->execute([$targetLogin]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    exit("Utilisateur non trouvé.");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion de l'Utilisateur: <?= htmlspecialchars($user['login']) ?></title>
    <style>
        /* Styles de la page de Login pour centrer et styliser le conteneur */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: rgb(220, 211, 187);
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .manage-container {
            background-color: #fff1d9;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-align: center;
            width: 100%;
            max-width: 450px;
        }

        /* Titre */
        .manage-container h1 {
            margin-bottom: 25px;
            font-size: 1.8rem;
            color: #333;
        }

        /* Messages */
        .error-message, .success-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            text-align: left;
            background-color: transparent; 
            border: none;
        }
        .error-message { 
            color: #dc3545;
        }
        .success-message { 
            color: #28a745;
        }

        /* Formulaire et Champs */
        form div { 
            margin-bottom: 15px; 
            text-align: left;
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
            color: #333;
        }
        input[type="text"], input[type="email"], select { 
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        /* Bouton de soumission principal (Mettre à Jour) */
        button[type="submit"]:not(.delete-btn) {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background-color: #7e9ad7;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        button[type="submit"]:not(.delete-btn):hover {
            background-color: #7789b1; 
            opacity: 0.9;
        }
        
        /* Bouton de suppression - AJOUT DE MARGE */
        .delete-btn { 
            width: 100%;
            margin-top: 25px; /* Sépare ce bouton du bouton "Mettre à Jour" */
            background-color: #dc3545;
            color: white; 
            padding: 10px; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px; 
            font-size: 1rem;
            transition: 0.3s;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        
        /* Lien de retour */
        p a {
            display: block;
            margin-top: 20px;
            color: #7e9ad7;
            text-decoration: none;
            font-size: 0.9em;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

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
            
            <button type="submit">Mettre à Jour</button>
        </form>
        
        <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? Cette action est irréversible.');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="delete-btn">Supprimer l'Utilisateur</button>
        </form>

        <p><a href="admin.php">Retour au Tableau de Bord</a></p>
    </div>

</body>
</html>