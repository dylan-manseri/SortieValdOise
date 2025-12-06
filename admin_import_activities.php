<?php
require_once 'conf/bd_conf.php';
session_start();

// login de l'utilisateur connecté
$loginDefault = $_SESSION['login'] ?? null;

// si pas d'utilisateur connecté → on stoppe
if (!$loginDefault) {
    die("Aucun utilisateur connecté !");
}

// Chemin vers le fichier JSON
$jsonFile = __DIR__ . '/activities.json';

if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable : $jsonFile");
}

$jsonContent = file_get_contents($jsonFile);
$data = json_decode($jsonContent, true);

if (!is_array($data)) {
    die("JSON invalide ou vide !");
}

foreach ($data as $activity) {

    $id_sortie   = $activity['uid'] ?? null;
    $titre       = $activity['title'] ?? '';
    $description = $activity['description'] ?? '';
    $adresse     = $activity['address'] ?? '';
    $status      = 'pending';

    if (!$id_sortie) continue;

    // vérifier si existe déjà
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM propositions WHERE id_sortie = ?");
    $stmtCheck->execute([$id_sortie]);
    if ($stmtCheck->fetchColumn() > 0) continue;

    // INSERT avec user_login dynamique
    $stmtInsert = $pdo->prepare("
        INSERT INTO propositions (description, id_sortie, titre, adresse, user_login, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmtInsert->execute([$description, $id_sortie, $titre, $adresse, $loginDefault, $status]);
}

echo "Import terminé !";
