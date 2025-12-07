<?php
session_start();
require_once 'conf/bd_conf.php';

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
    header('Location: /index.php');
    exit;
}

$login = $_SESSION['login'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer favoris avec infos réelles
$stmtFav = $pdo->prepare("
    SELECT titre, adresse 
    FROM favoris 
    WHERE user_login = ?
");
$stmtFav->execute([$login]);
$favoris = $stmtFav->fetchAll(PDO::FETCH_ASSOC);
$h1 = "Profil";
$css = "profil";
$description = "Page dédié au profil des utilisateurs";
include "includes/pageParts/header.php";
?>

<section class="profile-container">
<h1 class="gold-gradient">
    Bienvenue <?= htmlspecialchars($user['prenom_user'] . " " . $user['nom_user']) ?>
</h1>

<div class="container">
<h2>Informations</h2>
<table>
<tr><th>Nom</th><td><?= htmlspecialchars($user['nom_user'] ?? '') ?></td></tr>
<tr><th>Prénom</th><td><?= htmlspecialchars($user['prenom_user'] ?? '') ?></td></tr>
<tr><th>Login</th><td><?= htmlspecialchars($user['login'] ?? '') ?></td></tr>
<tr><th>Email</th><td><?= htmlspecialchars($user['email'] ?? '') ?></td></tr>
</table>

<form action="modifier_profil.php" method="get">
    <button type="submit" class="logout">Modifier les informations</button>
</form>
<form action="supprimer_compte.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.')">
    <button type="submit" class="logout" style="background:#d9534f;">Supprimer le compte</button>
</form>
</div>

<div class="section">
<h2>Favoris</h2>
<?php if (empty($favoris)): ?>
    <p>Aucun favori enregistré.</p>
<?php else: ?>
    <table>
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

<a class="logout" href="logout.php">Déconnexion</a>
</section>
<?php include "includes/pageParts/footer.php"?>
</html>
