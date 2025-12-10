<?php
/**
 * Fichier : activitiesJson.php
 * Description :    Fichier stockant les activités récupéré (cf. fonctions/activities.php).
 *                  L'intérêt est de pouvoir les manipuler via js, sans avoir à réaliser de multiples requête navigateur.
 * Auteur : Dylan Manseri
 * Date : 23/11/2025
 */
include "../conf/bd_conf.php";
include "../includes/fonctions/activities.php";
header('Content-Type: application/json');   // On définit la structure de la page (json)
//echo "<html>";
$cacheFile = "../cache/activities.json";

if(!is_dir("../cache")){
    mkdir("../cache", 0777, true);
}
if(!file_exists($cacheFile)){
    $activities = null;
    try {
        $activities = json_encode(getActivities()); // On écrit en json le tableau d'activité construit au préalable.
    } catch (DateMalformedStringException $e) {

    }
    file_put_contents($cacheFile, $activities);
    echo $activities;
}
else{
    $cacheDuration = 24 * 3600;
    $age = time() - filemtime($cacheFile);
    if ($age < $cacheDuration) {
        echo file_get_contents($cacheFile);
    } else {
        $activities = null;
        try {
            $activities = getActivities(); // On écrit en json le tableau d'activité construit au préalable.
            $stmt = $pdo->prepare("SELECT * FROM favoris");
            $stmt->execute();
            $listeFavoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $today = new DateTime();
            foreach ($listeFavoris as $favoris) {
                if(!isset($activites[$favoris['id_sortie']])){
                    $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_sortie = :id_sortie");
                    $stmt->bindParam(":id_sortie", $favoris['id_sortie']);
                    $stmt->execute();
                }
            }
        } catch (DateMalformedStringException $e) {

        }
        unlink($cacheFile);
        $activities = json_encode($activities);
        file_put_contents($cacheFile, $activities);
        echo $activities;
    }
}


