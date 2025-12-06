<?php
session_start();
require_once 'conf/bd_conf.php';

// Chemin vers le fichier JSON avec toutes les sorties
$jsonFile = __DIR__ . '/data/activitiesJson.php'; // adapte selon ton projet

// Récupérer le contenu JSON
if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable !");
}

// Charger les données depuis le JSON
$activities = json_decode(file_get_contents($jsonFile), true);
if (!is_array($activities)) {
    die("Le JSON n'est pas valide ou vide !");
}

// Parcourir chaque sortie
foreach ($activities as $activity) {
    $id_sortie = $activity['uid'] ?? null;
    $titre = $activity['title'] ?? 'Titre par défaut';
    $description = $activity['description'] ?? 'Description par défaut';
    $adresse = $activity['adresse'] ?? 'Adresse par défaut';
    $user_login = $activity['user_login'] ?? 'nely'; // si tu veux une valeur par défaut
    $status = $activity['status'] ?? 'pending';

    if (!$id_sortie) continue; // Ignorer les entrées sans ID

    // Vérifier si la sortie existe déjà dans propositions
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM propositions WHERE id_sortie = ?");
    $stmtCheck->execute([$id_sortie]);
    if ($stmtCheck->fetchColumn() > 0) {
        continue; // déjà existant
    }

    // Insérer la sortie manquante
    $stmtInsert = $pdo->prepare("
        INSERT INTO propositions (description, id_sortie, titre, adresse, user_login, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$description, $id_sortie, $titre, $adresse, $user_login, $status]);
}

echo "Toutes les sorties manquantes ont été insérées avec succès !";
