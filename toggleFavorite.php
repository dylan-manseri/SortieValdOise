<?php
session_start();
require_once 'conf/bd_conf.php';
header('Content-Type: application/json');

$login = $_SESSION['login'] ?? null;
if (!$login) {
    echo json_encode(['success'=>false, 'message'=>'Utilisateur non connecté']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_sortie = $data['id_sortie'] ?? null;
if (!$id_sortie) {
    echo json_encode(['success'=>false, 'message'=>'ID sortie manquant']);
    exit;
}

// Vérifier si déjà favori
$stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_login=? AND id_sortie=?");
$stmt->execute([$login, $id_sortie]);
$isFav = $stmt->fetchColumn() > 0;

if ($isFav) {
    // Supprimer le favori
    $del = $pdo->prepare("DELETE FROM favoris WHERE user_login=? AND id_sortie=?");
    $del->execute([$login, $id_sortie]);
    echo json_encode(['success'=>true, 'isFavorite'=>false]);
} else {
    // Récupérer les infos réelles depuis propositions
    $stmtProp = $pdo->prepare("SELECT titre, adresse, status FROM propositions WHERE id_sortie=?");
    $stmtProp->execute([$id_sortie]);
    $prop = $stmtProp->fetch(PDO::FETCH_ASSOC);

    if (!$prop) {
        // Si la sortie n'existe pas dans propositions, renvoyer erreur
        echo json_encode(['success'=>false, 'message'=>'La sortie n’existe pas dans propositions']);
        exit;
    }

    // Ajouter le favori avec les infos réelles
    $add = $pdo->prepare("
        INSERT INTO favoris (user_login, id_sortie, titre, adresse) 
        VALUES (?, ?, ?, ?)
    ");
    $add->execute([
        $login,
        $id_sortie,
        $prop['titre'],
        $prop['adresse'],
    ]);

    echo json_encode(['success'=>true, 'isFavorite'=>true]);
}
?>
