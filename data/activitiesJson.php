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
if(!file_exists($cacheFile)){   // Si le fichier de cache n'existe pas, on le crée
    $activities = null;
    try {
        $activities = json_encode(getActivities()); // On écrit en json le tableau d'activité construit au préalable.
    } catch (DateMalformedStringException $e) {

    }
    file_put_contents($cacheFile, $activities);
    echo $activities;
}
else{       // Si il existe on va la lire ou le recrée, dépendamment de son âge
    $cacheDuration = 24 * 3600;
    $age = time() - filemtime($cacheFile);
    if ($age < $cacheDuration) {        // Au delà de 24h il est recrée
        echo file_get_contents($cacheFile);
    } else {
        $activities = null;
        try {                           // A chaque rafraichissement on nettoie en plus les favoris périmé de la BD
            $activities = getActivities(); // On récupère les activités
            $stmt = $pdo->prepare("SELECT * FROM favoris");
            $stmt->execute();
            $listeFavoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $today = new DateTime();
            foreach ($listeFavoris as $favoris) {   // On regarde pour chaque favoris si elle existe encore dans notre flux
                if(!isset($activites[$favoris['id_sortie']])){
                    $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_sortie = :id_sortie");
                    $stmt->bindParam(":id_sortie", $favoris['id_sortie']);
                    $stmt->execute();
                }
            }
        } catch (DateMalformedStringException $e) {

        }
        unlink($cacheFile);     // Suppression du fichier cache
        $activities = json_encode($activities);     // Création du fichier cache
        file_put_contents($cacheFile, $activities);
        echo $activities;       // On écrit les activités pour la demande en cours, tout le reste est fait en arrière plan
                                // Mais côté utilisateur c'est juste plus long.
    }
}


