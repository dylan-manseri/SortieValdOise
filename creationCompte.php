<?php
session_start();
if (session_status() === PHP_SESSION_ACTIVE && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_regenerate_id(true); 
}

require_once 'conf/bd_conf.php';
require_once 'conf/email_conf.php';
require_once 'conf/captcha_conf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$errorMessage = '';

/* ======================= ENVOI EMAIL ======================= */
function sendVerificationEmail($recipientEmail, $verificationCode, $smtpConfig){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpConfig['username'];
        $mail->Password   = $smtpConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $smtpConfig['port'];

        $mail->setFrom($smtpConfig['username'], $smtpConfig['sender_name']);
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = "Votre code de vérification";
        $mail->Body = "<h2>Bienvenue !</h2><p>Votre code est : <b>$verificationCode</b></p>";
        $mail->AltBody = "Votre code : $verificationCode";

        return $mail->send();
    }catch(Exception $e){
        return false;
    }
}

/* ======================= AJAX Vérif login ======================= */
if(isset($_GET['action']) && $_GET['action']==="check_username"){
    header('Content-Type: application/json');
    $login = $_POST['login'] ?? '';

    if(trim($login)==""){
        echo json_encode(['available'=>false]); exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(login)=LOWER(?)");
    $stmt->execute([$login]);
    echo json_encode(['available'=> $stmt->fetchColumn()==0 ]);
    exit;
}

/* ======================= Traitement inscription ======================= */
if($_SERVER['REQUEST_METHOD']==='POST'){
    $login       = trim($_POST['login'] ?? '');
    $nom_user    = trim($_POST['nom_user'] ?? '');
    $prenom_user = trim($_POST['prenom_user'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $captcha     = $_POST['g-recaptcha-response'] ?? null;

    $regex = '/^(?=.*\d)(?=.*[a-z]).{8,}$/';

    if(!$login || !$nom_user || !$prenom_user || !$email || !$password)
        $errorMessage="Tous les champs sont requis.";
    elseif(!preg_match($regex,$password))
        $errorMessage="Mot de passe trop faible (8 caractères + 1 chiffre minimum)";
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))
        $errorMessage="Email invalide.";
    elseif(!$captcha)
        $errorMessage="Veuillez valider le Captcha.";

    if(empty($errorMessage)){
        // Vérif captcha Google
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=".$captcha);
        if(!json_decode($response)->success)
            $errorMessage="Captcha incorrect, réessayez.";

        if(empty($errorMessage)){
            $stmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE login=? OR email=?");
            $stmt->execute([$login,$email]);

            if($stmt->fetchColumn()>0)
                $errorMessage="Login ou email déjà utilisé.";
            else{
                $code = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
                $hash = password_hash($password,PASSWORD_DEFAULT);

                $pdo->prepare("INSERT INTO users (login,nom_user,prenom_user,email,hashedPassword,code_genere) 
                VALUES (?,?,?,?,?,?)")->execute([$login,$nom_user,$prenom_user,$email,$hash,$code]);

                if(sendVerificationEmail($email,$code,$smtpConfig)){
                    $_SESSION['email_pending']=$email;
                    header("Location: verifCode.php"); exit;
                }else $errorMessage="Erreur email, réessayez.";
            }
        }
    }
}

/* ======================= Partie affichage ======================= */
$title="Inscription";
$description="Dans cette page vous pouvez crée votre compte, afin d'accéder à de nouvelles fonctionnalités";
$css="creationCompte"; // ton fichier CSS global + light/dark
include "includes/pageParts/header.php";
?>

<div class="register-page">
<div class="register-container">

    <h2>Créer un compte</h2>

    <?php if($errorMessage): ?>
        <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

        <form method="POST">

          <label for="username-input">Login</label>
          <input type="text" name="login" id="username-input" maxlength="12"
                placeholder="Login" value="<?=htmlspecialchars($login??'')?>">

          <span id="username-error"></span>

          <label for="nom_user">Nom</label>
          <input type="text" name="nom_user" id="nom_user" placeholder="Nom" required
                value="<?=htmlspecialchars($nom_user??'')?>">

          <label for="prenom_user">Prénom</label>
          <input type="text" name="prenom_user" id="prenom_user" placeholder="Prénom" required
                value="<?=htmlspecialchars($prenom_user??'')?>">

          <label for="email">Email</label>
          <input type="email" name="email" id="email" placeholder="Email" autocomplete="email" required
                value="<?=htmlspecialchars($email??'')?>">

<label for="password">Mot de passe</label>
<div class="password-wrapper">
    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
    <span id="togglePassword" class="eye">👁️</span>
</div>

          <p>Vérification Captcha</p>
          <div class="g-recaptcha" data-sitekey="<?=$data_sitekey?>"></div>

          <button id="register-btn" disabled>S'inscrire</button>

          <p class="link-msg">Déjà inscrit ? <a href="connexion.php">Connexion</a></p>
      </form>

</div>
</div>

<script>
const input=document.getElementById("username-input"),
      msg=document.getElementById("username-error"),
      btn=document.getElementById("register-btn");
let ctrl=null;

function checkLogin(login){
    if(login.length<3){ msg.textContent=""; btn.disabled=true; return;}
    if(ctrl) ctrl.abort(); ctrl=new AbortController();

    fetch("creationCompte.php?action=check_username",{
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`login=${encodeURIComponent(login)}`,
        signal:ctrl.signal
    })
    .then(r=>r.json())
    .then(d=>{
        if(!d.available){ msg.textContent="❌ Déjà pris"; msg.style.color="red"; btn.disabled=true;}
        else{ msg.textContent="✔ Disponible"; msg.style.color="green"; btn.disabled=false;}
    });
}

input.addEventListener("input",()=>checkLogin(input.value.trim()));

window.addEventListener("DOMContentLoaded",()=>{
    if(input.value.trim().length>=3) checkLogin(input.value.trim());
});
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');

    // Toggle type
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.textContent = '🙈'; // icon when visible
    } else {
        passwordInput.type = 'password';
        this.textContent = '👁️'; // icon when hidden
    }
});
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php include "includes/pageParts/footer.php"; ?>
</html>
