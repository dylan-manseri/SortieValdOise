<?php
session_start();
require_once 'conf/bd_conf.php';
$successMessage = '';

if (isset($_SESSION['flash_message'])) {
    $successMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); 
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit; 
}

if (isset($_GET['action']) && isset($_GET['id_prop'])) {
    
    $id_prop = (int)$_GET['id_prop']; 
    $action = $_GET['action']; 

    try {
        if ($action === 'accept') {
            $sql = "UPDATE propositions SET status = 'accepted' WHERE id_prop = :id_prop";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_prop' => $id_prop]);
            
            $_SESSION['flash_message'] = "Proposition ID {$id_prop} acceptée avec succès.";
            
        } elseif ($action === 'delete') {
            $sql = "DELETE FROM propositions WHERE id_prop = :id_prop";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_prop' => $id_prop]);
            
            $_SESSION['flash_message'] = "Proposition ID {$id_prop} supprimée avec succès.";
        }
        
        header('Location: admin.php');
        exit;
        
    } catch (\PDOException $e) {
        error_log("Erreur d'exécution de requête de gestion de proposition: " . $e->getMessage()); 
        $_SESSION['flash_message'] = "Erreur lors de l'exécution de l'action.";
        header('Location: admin.php');
        exit;
    }
}


try {
    $oneMonthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));
    $sqlUsersLastMonth = "SELECT COUNT(*) FROM users WHERE registration_date >= :oneMonthAgo";
    $stmt = $pdo->prepare($sqlUsersLastMonth);
    $stmt->execute(['oneMonthAgo' => $oneMonthAgo]);
    $usersLastMonth = $stmt->fetchColumn();

    $sqlTotalPropositions = "SELECT COUNT(*) FROM propositions";
    $totalPropositions = $pdo->query($sqlTotalPropositions)->fetchColumn();

    $sqlPendingPropositionsList = "
        SELECT 
            id_prop, titre, description, adresse, villev, user_login, 
            DATE_FORMAT(date, '%d/%m/%Y') AS formatted_date
        FROM propositions 
        WHERE status = 'pending'
        ORDER BY date DESC 
        LIMIT 20";
    $pendingPropositions = $pdo->query($sqlPendingPropositionsList)->fetchAll();
    $pendingPropCount = count($pendingPropositions);

    $sqlAcceptedAndPendingList = "
    SELECT 
        id_prop, titre, description, adresse, villev, user_login, status,
        DATE_FORMAT(date, '%d/%m/%Y') AS formatted_date
    FROM propositions 
    WHERE status IN ('pending', 'accepted')  /* <-- C'est la clé */
    ORDER BY date DESC 
    LIMIT 50";
$acceptedAndPendingPropositions = $pdo->query($sqlAcceptedAndPendingList)->fetchAll();
$acceptedAndPendingPropCount = count($acceptedAndPendingPropositions);
    
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tableau de Bord Admin</title>
<style>
 body { font-family: Arial, sans-serif; padding: 0; margin: 0; background-color: #fcf5ef; }
  /* Container centré */
 #main-container {
    width: 95%; 
    max-width: 1200px; /* Limite la largeur pour le centrage */
    margin: 50px auto; /* Centre le contenu */
    padding: 50px;
    background-color: #fff2e6;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-radius: 20px;
  }

  /* Styles d'origine conservés */
 .stats-box { 
  border: 1px solid #ccc;
  padding: 15px; 
  margin-bottom: 20px; 
  border-radius: 30px;
  text-align: center; 
}
 .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;}
table { 
    width: 100%; 
    border-collapse: collapse; /* Gardé pour éviter les espaces entre les cellules */
    margin-top: 20px; 
    /* Ajout de coins arrondis à l'ensemble du tableau et masquage des débordements */
    border-radius: 10px; 
    overflow: hidden; /* Important pour que border-radius fonctionne sur les enfants */
    border: none; /* Supprime la bordure extérieure du tableau */
}
th, td { 
    border: none; /* Supprime les bordures de cellule */
    padding: 12px 8px; 
    text-align: center; /* Centre le contenu horizontalement dans les cellules */
    border-bottom: 1px solid #eee; /* Ajoute une ligne de séparation légère entre les lignes */
}
th { 
    background-color: #c3a990; /* Changé pour une couleur plus vive (bleu) */
    color: white; /* Texte blanc pour le contraste */
    font-weight: bold;
    text-align: center; /* Centrage des en-têtes */
}
tr:last-child td {
    border-bottom: none;
}
 .logout-button {
  background-color: #c3a990; 
  color: white;
  padding: 10px 15px;
  text-decoration: none;
  border-radius: 10px;
  font-weight: bold;
  transition: background-color 0.3s;
 }
 .logout-button:hover {
  background-color: #917a65;
 }
  .header-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
  }
  
  /* Styles pour le système d'onglets */
  .tab-nav {
    display: table; 
    width: 100%;
    border-collapse: separate; /* Changé à separate pour permettre l'espace entre les boutons */
    margin-top: 20px;
    background-color: transparent; /* Pas de fond sur le conteneur des boutons */
    table-layout: fixed; /* Pour que les cellules soient de même largeur */
    border-spacing: 5px 0;  
  }
  .tab-header {
    background-color: #c3a990; 
    display: table-cell;
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: bold;
    cursor: pointer;
    border: none; 
    border-bottom: 3px solid transparent;
    transition: background-color 0.2s, border-bottom 0.2s;
    border-radius: 10px 10px 0 0;
  }
  .tab-header:hover, .tab-header.active {
    background-color: #917a65; 
  }
  .tab-container {
  padding: 0 5px; /* Compense l'espacement des boutons */
  margin-top: -1px; /* Remonte légèrement pour éviter un double espace */
 }
  .tab-content {
    display: none; 
    padding: 20px 0;
    border-top: 1px solid #ccc;
    border-top: none;
  }
  .tab-content.active {
    display: block; 
    background-color: #917a65;
  }
  
  .tab-content table {
    margin-top: 0;
  }
    /* Style spécifique pour le statut dans le tableau */
    .status-pending { color: #ffc107; font-weight: bold; }
    .status-accepted { color: #28a745; font-weight: bold; }

</style>
</head>
<body>

  
  <div id="main-container">
    <div class="header-actions">
        <a href="logout.php" class="logout-button">Déconnexion</a>
        <a href="rajouterAdmin.php" class="logout-button" >Ajouter Admin</a>
    </div>
    <h1>Tableau de Bord Admin</h1>

    <?php if (!empty($successMessage)): ?>
    <div id="flash-notification" 
     style="padding: 15px; margin-bottom: 20px; border: 1px solid green; background-color: #e6ffe6; color: green; font-weight: bold; text-align: center; border-radius: 5px;">
     <?= htmlspecialchars($successMessage) ?>
    </div>
    <?php endif; ?>

    <h2 style="text-align: center;">Statistiques Clés</h2>
    <div class="stats-grid">
    <div class="stats-box">
    <h2>Utilisateurs (Dernier Mois)</h2>
    <p style="font-size: 2em; color: #007bff;"><?= htmlspecialchars($usersLastMonth) ?></p>
    </div>

    <div class="stats-box">
    <h2>Total Propositions</h2>
    <p style="font-size: 2em; color: #28a745;"><?= htmlspecialchars($totalPropositions) ?></p>
    </div>

    <div class="stats-box">
    <h2>Propositions en Attente</h2>
     <p style="font-size: 2em; color: #ffc107;"><?= htmlspecialchars($pendingPropCount) ?></p>
    </div>
    </div> 

    
    <h2>Gestion des Données Détaillées</h2>
    
    <div class="tab-nav">
        <div class="tab-header active" data-tab="tab-users">
            Derniers 10 Utilisateurs
        </div>
        <div class="tab-header" data-tab="tab-all-propositions">
            Historique Propositions (<?= htmlspecialchars($acceptedAndPendingPropCount) ?>)
        </div>
        <div class="tab-header" data-tab="tab-pending-propositions">
            Propositions à Valider (<?= htmlspecialchars($pendingPropCount) ?>)
        </div>
    </div>
    
    <div class="tab-container">
        <div id="tab-users" class="tab-content active">
            <?php if ($lastUsers): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Nom Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date d'Inscription</th>
                            <th>Actions</th> 
                        </tr>
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
                                <a href="gestionUtilisateurs.php?login=<?= urlencode($user['login']) ?>">Gérer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
            <p>Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div> <div id="tab-all-propositions" class="tab-content">
            <?php if ($acceptedAndPendingPropositions): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Statut</th>
                        <th>Titre / Description</th>
                        <th>Adresse / Ville</th>
                        <th>Auteur</th>
                        <th>Date de Proposition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acceptedAndPendingPropositions as $prop): ?>
                    <tr>
                        <td><?= htmlspecialchars($prop['id_prop']) ?></td>
                        <td>
                            <span class="status-<?= htmlspecialchars($prop['status']) ?>">
                                <?= htmlspecialchars($prop['status'] === 'pending' ? 'En Attente' : 'Acceptée') ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($prop['titre']) ?></strong><br>
                            <small><?= substr(htmlspecialchars($prop['description']), 0, 50) . '...' ?></small>
                        </td>
                        <td><?= htmlspecialchars($prop['adresse']) . ', ' . htmlspecialchars($prop['villev']) ?></td>
                        <td><?= htmlspecialchars($prop['user_login']) ?></td>
                        <td><?= htmlspecialchars($prop['formatted_date']) ?></td>
                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Aucune proposition acceptée ou en attente trouvée.</p>
            <?php endif; ?>
        </div> 
        <div id="tab-pending-propositions" class="tab-content">
            <?php if ($pendingPropositions): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre / Description</th>
                        <th>Adresse / Ville</th>
                        <th>Auteur</th>
                        <th>Date de Proposition</th>
                        <th style="width: 150px;">Actions</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingPropositions as $prop): ?>
                    <tr>
                        <td><?= htmlspecialchars($prop['id_prop']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($prop['titre']) ?></strong><br>
                            <small><?= substr(htmlspecialchars($prop['description']), 0, 50) . '...' ?></small>
                        </td>
                        <td><?= htmlspecialchars($prop['adresse']) . ', ' . htmlspecialchars($prop['villev']) ?></td>
                        <td><?= htmlspecialchars($prop['user_login']) ?></td>
                        <td><?= htmlspecialchars($prop['formatted_date']) ?></td>
                        <td>
                            <a href="admin.php?action=accept&id_prop=<?= urlencode($prop['id_prop']) ?>" 
                            style="background-color: #28a745; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8em;"
                            onclick="return confirm('Êtes-vous sûr de vouloir accepter cette proposition ?');">
                            Accepter
                            </a>
                            
                            <a href="admin.php?action=delete&id_prop=<?= urlencode($prop['id_prop']) ?>" 
                            style="background-color: #dc3545; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8em; margin-left: 5px;"
                            onclick="return confirm('Êtes-vous sûr de vouloir SUPPRIMER cette proposition ? Cette action est irréversible.');">
                            Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Aucune proposition en attente de validation.</p>
            <?php endif; ?>
        </div> 
      </div> 
      </div> 
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Gestion de la notification flash
        const notification = document.getElementById('flash-notification');
        if (notification) {
            setTimeout(function() {
                notification.style.display = 'none';
            }, 10000); 
        }

        // 2. Gestion des onglets
        const tabs = document.querySelectorAll('.tab-header');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Désactiver tous les onglets
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                // Activer l'onglet cliqué
                this.classList.add('active');

                // Afficher le contenu de l'onglet correspondant
                const targetId = this.getAttribute('data-tab');
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });

        // Assurez-vous que l'onglet 'tab-users' (le premier) est actif au chargement
        if (tabs.length > 0) {
            // S'assurer que 'tab-users' est actif si rien n'est sélectionné
            const activeTab = document.querySelector('.tab-header.active');
            if (!activeTab) {
                document.querySelector('[data-tab="tab-users"]').click();
            }
        }
    });
</script>
</body>
</html>