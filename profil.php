<?php
session_start();
require_once 'conf/bd_conf.php';
ini_set("log_errors", 1);
ini_set("error_log", "C:/wamp64/www/debug.log");
error_log(print_r($_SESSION, true));

// Gestion thème et cookies
$cookieConsent = $_COOKIE['cookieConsent'] ?? null;
$style = "light";

if (isset($_GET["style"]) && in_array($_GET["style"], ["light","dark"], true)) {
    $style = $_GET["style"];
    if ($cookieConsent === 'true') {
        setcookie("style", $style, time() + 60*60*24*30, "/");
    }
} elseif ($cookieConsent === 'true' && isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light','dark'], true)) {
    $style = $_COOKIE['style'];
}

if ($cookieConsent === 'true' && isset($_COOKIE["date_last_visit"])) {
    setcookie("date_last_visit", time(), time() + 60*60*24*30, "/");
}

$bascule = ($style === "light") ? "dark" : "light";

// Vérifier si connecté
if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

if ((!isset($_SESSION['role'])) || $_SESSION['role'] !== "user") {
    error_log($_SESSION['role']);
    header('Location: connexion.php');
    exit; 
}

$login = $_SESSION['login'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer favoris
$stmtFav = $pdo->prepare("SELECT titre, adresse FROM favoris WHERE user_login = ?");
$stmtFav->execute([$login]);
$favoris = $stmtFav->fetchAll(PDO::FETCH_ASSOC);

include "includes/fonctions/icon.php";
if(isset($_FILES["image"]) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
    $verif = verifImage($_FILES["image"], "profil", 400, 400);
    $rInsert = $pdo->prepare("UPDATE users SET icon = ?, type_mime = ? WHERE login = ?");
    $rInsert->execute([$verif[0], $verif[1], $login]);
}

$rIcon = $pdo->prepare("SELECT icon FROM users WHERE login = ?");
$rIcon->execute([$login]);
$line = $rIcon->fetch(PDO::FETCH_ASSOC);
$icon = ($line['icon'] === NULL) ? "icons/default.png" : "showIcon.php?id=".$login;

$title = "Profil";
$css = "profil";
$description = "Page dédiée au profil des utilisateurs";
include "includes/pageParts/header.php";
?>

<section class="profile-container container py-4">

<?php if(isset($_GET["error"])): ?>
    <p class="alert alert-danger">
        <?= ($_GET["error"] === "dim") ? "Merci d'envoyer un fichier de taille <= à 256" : "" ?>
        <?= ($_GET["error"] === "file") ? "Merci d'envoyer un fichier valide ! Formats autorisés : png, jpeg, webp." : "" ?>
        <?= ($_GET["error"] === "size") ? "Fichier trop volumineux, max: 300Ko" : "" ?>
    </p>
<?php endif; ?>

<h1 class="text-center mt-4 mb-5">
    Bienvenue <?= htmlspecialchars($user['prenom_user'] . " " . $user['nom_user']) ?>
</h1>

<!-- --------------------------------------
                INFORMATIONS
--------------------------------------- -->
<div class="container">
<h2>Informations</h2>

<div class="row g-4 align-items-start mt-2">

    <!-- PHOTO + UPLOAD -->
    <div class="col-12 col-md-4 text-center">
        <img class="pp img-fluid rounded mb-3" src="<?= $icon ?>" alt="photo de profil" style="max-width:180px;">
        
        <form action="profil.php" method="POST" enctype="multipart/form-data" class="upload-container">
            <input class="form-control mt-2" type="file" name="image" accept="image/*" required>
            <button type="submit" class="btn btn-primary w-100 mt-2">Envoyer</button>
        </form>
    </div>

    <!-- TABLEAU INFORMATIONS -->
    <div class="col-12 col-md-8">
        <table class="table table-dark table-striped table-bordered">
            <tr>
                <th>Nom</th>
                <td><?= htmlspecialchars($user['nom_user']) ?></td>
            </tr>
            <tr>
                <th>Prénom</th>
                <td><?= htmlspecialchars($user['prenom_user']) ?></td>
            </tr>
            <tr>
                <th>Login</th>
                <td><?= htmlspecialchars($user['login']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($user['email']) ?></td>
            </tr>
        </table>
    </div>

</div>

<!-- Boutons -->
<div class="row mt-4">
    <div class="col-12 col-md-6">
        <form action="modifier_profil.php" method="get">
            <button type="submit" class="btn btn-warning w-100">Modifier les informations</button>
        </form>
    </div>

    <div class="col-12 col-md-6">
        <form action="supprimer_compte.php" method="post" 
              onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.')">
            <button type="submit" class="btn btn-danger w-100">Supprimer le compte</button>
        </form>
    </div>
</div>
</div>

<!-- --------------------------------------
                FAVORIS
--------------------------------------- -->
<div class="section container py-4">
<h2>Favoris</h2>

<?php if (empty($favoris)): ?>
    <p>Aucun favori enregistré.</p>
<?php else: ?>
<table class="table table-dark table-hover table-bordered mt-3">
    <tr>
        <th>Titre</th>
        <th>Adresse</th>
    </tr>
    <?php foreach ($favoris as $f): ?>
    <tr>
        <td><?= htmlspecialchars($f['titre']) ?></td>
        <td><?= htmlspecialchars($f['adresse']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</div>

<a class="btn btn-secondary w-100 mt-4 mb-4" href="logout.php">Déconnexion</a>

</section>

<?php include "includes/pageParts/footer.php"; ?>
</html>
