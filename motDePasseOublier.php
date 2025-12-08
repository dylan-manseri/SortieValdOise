<?php
session_start();
require_once 'conf/bd_conf.php';
require_once 'conf/email_conf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$css = "connexion";
$title = "Mot de passe oublié";
$description = "Réinitialisation du mot de passe utilisateur";

/* ---------------------------- Reset Password Logic ---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $genericSuccessMessage = '<h1 class="text-center mt-5">Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.</h1>';

    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $foundUser = $stmt->fetch();
    } catch (PDOException $e) { exit($genericSuccessMessage); }

    if ($foundUser) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + 3600;

        $upd = $pdo->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $upd->execute([$token,$expires,$email]);

        $resetLink = "https://sortievaldoise.alwaysdata.net/changerMdp.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpConfig['username'];
            $mail->Password   = $smtpConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $smtpConfig['port'];

            $mail->setFrom($smtpConfig['username'],$smtpConfig['sender_name']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Réinitialisation du mot de passe";
            $mail->Body    = "Cliquez ici pour réinitialiser : <a href='$resetLink'>$resetLink</a>";
            $mail->send();
        } catch(Exception $e){}
    }
    exit($genericSuccessMessage);
}

include "includes/pageParts/header.php";
?>

<section class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;">
    <div class="login-container p-4 p-md-5 col-11 col-sm-9 col-md-6 col-lg-4">

        <h2 class="mb-3 text-center">Réinitialiser le mot de passe</h2>
        <p class="text-center mb-4">Entrez votre email et un lien vous sera envoyé.</p>
        
        <form action="" method="POST" class="d-grid gap-3">
            <input type="email" name="email" class="form-control" placeholder="Votre email" required>
            <button type="submit" class="btn w-100">Envoyer le lien</button>
        </form>
    </div>
</section>

<?php include "includes/pageParts/footer.php"; ?>
</html>