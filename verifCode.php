<?php
require_once 'conf/bd_conf.php';
session_start();

$message = "";
$code = trim((string)($_POST['code'] ?? ''));
$email_pending = $_SESSION['email_pending'] ?? null;
$foundUser = null;

if (!$email_pending) {
    header('Location: creationCompte.php');
    exit;
}

try{
  if (isset($_SESSION['email_pending'])) {
    $stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, status, code_genere FROM users WHERE email = ?");
    $stmt->execute([$email_pending]);
    $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  if (!$foundUser || $foundUser['status'] !== 'pending') {
    if ($foundUser && $foundUser['status'] === 'active') {
      header('Location: connexion.php');
      exit;
    }
    unset($_SESSION['email_pending']);
    header('Location: creationCompte.php');
    exit;
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $code) {

    if ($code === $foundUser['code_genere']) {

      $updateStmt = $pdo->prepare("UPDATE users SET status = 'active', code_genere = NULL WHERE login = ?");
      $updateStmt->execute([$foundUser['login']]);

      unset($_SESSION['email_pending']);


      $message = "Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.";
      header("refresh:3;url=connexion.php"); // redirection apres 3 sec
    } else {
      $message = "Le code de vérification est incorrect. Veuillez réessayer.";
    }
  }

} catch (PDOException $e) {
    error_log("DB Error on verifCode: " . $e->getMessage());
    $message = "Une erreur de base de données est survenue. Veuillez réessayer plus tard.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vérification du compte</title>

<style>
body{
    font-family:'Permanent Marker',cursive;
    display:flex;justify-content:center;align-items:center;
    height:100vh;margin:0;background:#e7e8bc;
}
.login-container{
    background:#f4f4d7;padding:30px 40px;border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);text-align:center;width:300px;
}
.login-container input{
    width:100%;padding:10px;margin:10px 0;font-size:1rem;
    border:1px solid #ccc;border-radius:5px;
}
.login-container button{
    width:100%;padding:10px;margin-top:15px;background:#7e9ad7;color:white;
    font-size:1rem;border:none;border-radius:5px;cursor:pointer;transition:.3s;
}
.login-container button:hover{background:#6d83c5;opacity:.7;}
.error{color:red;font-weight:bold;}
.success{color:green;font-weight:bold;}
</style>

</head>
<body>

<div class="login-container">

<?php if ($message): ?>
    <p class="<?= strpos($message,'succès')!==false?'success':'error' ?>">
        <?= htmlspecialchars($message) ?>
    </p>

    <?php if (strpos($message,'succès')!==false): ?>
        <p>Redirection vers la connexion...</p>
        <a href="connexion.php" style="display:block;margin-top:10px;">👉 Se connecter maintenant</a>
    <?php endif; ?>

<?php else: ?>

    <h2>Activation du compte</h2>
    <p>Saisissez le code envoyé à <strong><?= htmlspecialchars($email_pending) ?></strong></p>

    <form method="POST">
        <input type="text" name="code" placeholder="Code à 6 chiffres"
               required maxlength="6" pattern="\d{6}" autofocus>
        <button type="submit">Activer</button>
    </form>

<?php endif; ?>
</div>

</body>
</html>
