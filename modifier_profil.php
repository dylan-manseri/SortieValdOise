<?php
session_start();
require_once 'conf/bd_conf.php';

if (!isset($_SESSION['login'])) {
    header('Location: /index.php');
    exit;
}

$login = $_SESSION['login'];

$stmt = $pdo->prepare("SELECT nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom_user'];
    $prenom = $_POST['prenom_user'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE users SET nom_user = ?, prenom_user = ?, email = ? WHERE login = ?");
    $stmt->execute([$nom, $prenom, $email, $login]);

    header('Location: profil.php');
    exit;
}
$title = "Modification";
$h1 = "Modifier votre profil";
$css = "modifier";
$description = "Dans cet page l'utilisateur peut modifier son profil";
include "includes/pageParts/header.php";
?>
<section class="profile-container">
<h2>Ne vous trompez pas cette fois…</h2>
<form action="modifier_profil.php" method="post">
    <label>Nom<input type="text" name="nom_user" value="<?= htmlspecialchars($user['nom_user']) ?>" required></label>
    <label>Prénom<input type="text" name="prenom_user" value="<?= htmlspecialchars($user['prenom_user']) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label>
    <button class="logout" type="submit">Enregistrer</button>
</form>
<a class="logout" href="profil.php">Annuler</a>
</section>

<?php include "includes/pageParts/footer.php"?>

</html>
