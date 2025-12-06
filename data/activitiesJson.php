<?php
// Toujours utiliser le chemin absolu pour éviter les erreurs d'inclusion
include __DIR__ . '/../includes/fonctions/activities.php';

header('Content-Type: application/json');

// Définir un cache (facultatif)
$cacheFile = __DIR__ . '/../cache/activities.json';
$cacheDuration = 24 * 3600; // 24h

if (file_exists($cacheFile)) {
    $age = time() - filemtime($cacheFile);
    if ($age < $cacheDuration) {
        echo file_get_contents($cacheFile);
        exit;
    }
}

try {
    // Récupérer les activités via ta fonction
    $activities = getActivities();

    if (!is_array($activities)) {
        $activities = [];
    }

    // Ajouter des valeurs par défaut si des champs manquent
    foreach ($activities as &$activity) {
        if (!isset($activity['id'])) $activity['id'] = uniqid();
        if (!isset($activity['titre'])) $activity['titre'] = 'Titre inconnu';
        if (!isset($activity['description'])) $activity['description'] = '';
        if (!isset($activity['categorie'])) $activity['categorie'] = '';
        if (!isset($activity['ville'])) $activity['ville'] = '';
        if (!isset($activity['date'])) $activity['date'] = '';
        if (!isset($activity['image'])) $activity['image'] = '';
    }
    unset($activity);

    // Écrire dans le cache
    file_put_contents($cacheFile, json_encode($activities));

    // Retourner le JSON
    echo json_encode($activities);

} catch (Exception $e) {
    // En cas d'erreur, renvoyer un tableau vide
    echo json_encode([]);
}
