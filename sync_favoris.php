<?php
// sync_favoris.php
session_start();
require_once 'conf/bd_conf.php';

echo "<pre>";

// 1️⃣ Charger les activités depuis le JSON
$jsonFile = __DIR__ . '/data/activitiesJson.php';
if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable : $jsonFile");
}

ob_start();
include $jsonFile;
$activitiesJson = ob_get_clean();
$activities = json_decode($activitiesJson, true);

if (!is_array($activities)) {
    die("JSON invalide ou vide après inclusion du PHP.");
}

if (!is_array($activities)) {
    die("JSON invalide ou vide.");
}

// Indexer les activités par uid pour accès rapide
$activitiesByUid = [];
foreach ($activities as $act) {
    $activitiesByUid[$act['uid']] = $act;
}

// 2️⃣ Récupérer tous les favoris
$favStmt = $pdo->query("SELECT id_sortie FROM favoris");
$favIds = $favStmt->fetchAll(PDO::FETCH_COLUMN);

echo "Nombre de favoris à traiter : " . count($favIds) . "\n\n";

// 3️⃣ Synchroniser chaque favori
foreach ($favIds as $id_sortie) {

    if (!isset($activitiesByUid[$id_sortie])) {
        echo "⚠️ Sortie $id_sortie non trouvée dans le JSON API\n";
        continue;
    }

    $act = $activitiesByUid[$id_sortie];
    $titre = $act['title'] ?? 'Titre inconnu';
    $ville = $act['ville'] ?? '';
    $status = $act['status'] ?? 'active';

    // Vérifier si existe déjà dans propositions
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM propositions WHERE id_sortie = ?");
    $stmtCheck->execute([$id_sortie]);
    $exists = $stmtCheck->fetchColumn() > 0;

    if ($exists) {
        // Update titre et ville
        $upd = $pdo->prepare("UPDATE propositions SET titre = ?, adresse = ?, status = ? WHERE id_sortie = ?");
        $upd->execute([$titre, $ville, $status, $id_sortie]);
        echo "✅ Sortie $id_sortie mise à jour : $titre\n";
    } else {
        // Insert nouvelle sortie
        $ins = $pdo->prepare("INSERT INTO propositions (id_sortie, titre, adresse, status) VALUES (?, ?, ?, ?)");
        $ins->execute([$id_sortie, $titre, $ville, $status]);
        echo "➕ Sortie $id_sortie ajoutée : $titre\n";
    }
}

echo "\n✅ Synchronisation terminée.\n";
