<?php
// Note : session_start() et require_once 'conf/bd_conf.php'; sont inclus dans les fichiers appelants.

// Définition de la durée du cookie : par exemple 30 jours (30 * 24 * 60 * 60)
const TRACKING_COOKIE_EXPIRY = 2592000; 
const TRACKING_COOKIE_NAME = 'user_tracking_id';

/**
 * Génère un UUID (Universally Unique Identifier) simple pour le suivi.
 * @return string Un identifiant unique de 36 caractères.
 */
function generateUuidV4() {
    // Une façon simple de générer un GUID/UUID V4 sans dépendance à des fonctions spécifiques
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function recordVisit($pdo) {
    $status = 'anonymous';
    $loginId = null;
    $trackingId = null;

    // 1. GESTION DE L'ID DE SUIVI PAR COOKIE
    if (isset($_SESSION['login'])) {
        // L'utilisateur est connecté via la session PHP (login = ID utilisateur)
        $status = 'logged_in';
        $loginId = $_SESSION['login'];
        // Si connecté, on utilise le login comme trackingId pour les futures visites connectées
        $trackingId = $loginId; 
        
        // Optionnel: Supprimer l'ancien cookie de suivi anonyme si l'utilisateur vient de se connecter
        if (isset($_COOKIE[TRACKING_COOKIE_NAME])) {
            // Pour l'index.php: le cookie peut être mis à jour pour correspondre au login ID s'il n'est pas déjà défini
            // Mais la vraie magie (UPDATE DB) se passe dans connexion.php après le succès.
            setcookie(TRACKING_COOKIE_NAME, $loginId, time() + TRACKING_COOKIE_EXPIRY, "/");
        }

    } else {
        // L'utilisateur est anonyme
        if (isset($_COOKIE[TRACKING_COOKIE_NAME])) {
            // L'utilisateur a déjà un cookie de suivi anonyme
            $trackingId = $_COOKIE[TRACKING_COOKIE_NAME];
        } else {
            // C'est une NOUVELLE visite anonyme : créer un UUID et définir le cookie
            $trackingId = generateUuidV4();
            setcookie(TRACKING_COOKIE_NAME, $trackingId, time() + TRACKING_COOKIE_EXPIRY, "/", "", false, true);
        }
    }

    // 2. ENREGISTREMENT DANS LA DB
    try {
        if ($status === 'logged_in') {
             // Si connecté, on utilise le login (loginId)
             $sql = "INSERT INTO visits (login, status) VALUES (?, ?)";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$loginId, $status]);
        } else { 
         // Nous utilisons l'ID de suivi pour toutes les visites non connectées.
         $sql = "INSERT INTO visits (tracking_id, status) VALUES (?, ?)";
         $stmt = $pdo->prepare($sql);
         $stmt->execute([$trackingId, $status]);
    }
        
    } catch (PDOException $e) {
        error_log("DB Error recording visit: " . $e->getMessage());
    }
}
// Note: retirez l'appel de fonction ici, il sera fait dans index.php et connexion.php
// recordVisit($pdo); 
?>