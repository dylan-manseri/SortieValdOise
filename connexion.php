<?php
session_start();
if(isset($_SESSION['login']) && $_SESSION['login']!== ""){
    if(isset($_SESSION['role']) && $_SESSION['role']==="admin"){
        header('Location: admin.php');
        exit;
    }
    else{
        header('Location: profil.php');
        exit;
    }
}

require_once 'conf/bd_conf.php';
require_once 'conf/captcha_conf.php';

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

$errorMessage = '';
$login = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = $_POST['login'] ?? null;
    $password = $_POST['password'] ?? null;
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    if (empty($login) || empty($password)) {
        $errorMessage = "Login et mot de passe requis";
    }
    elseif (!$recaptchaToken) {
        $errorMessage = 'Veuillez cocher la case "Je ne suis pas un robot".';
    }

    if (empty($errorMessage)) {

        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $postData = http_build_query([
            'secret'   => $secretKey,
            'response' => $recaptchaToken,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]);

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => $postData
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($verifyURL, false, $context);
        $result = json_decode($response);

        if (!$result || !$result->success) {
            $errorMessage = 'Échec de la vérification CAPTCHA. Veuillez réessayer.';
        }
    }

    if (empty($errorMessage)) {
        try {
            $stmt = $pdo->prepare("SELECT login, hashedPassword, nom_user, prenom_user, role FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database error: ' . $e->getMessage());
        }

        if ($foundUser && password_verify($password, $foundUser['hashedPassword'])) {

            $_SESSION['login'] = $foundUser['login'];
            $_SESSION['name'] = $foundUser['nom_user'];
            $_SESSION['pren'] = $foundUser['prenom_user'];
            $_SESSION['role'] = $foundUser['role'];

            if ($_SESSION['role'] == 'admin') {
                header('Location: admin.php');
                exit;
            } elseif ($_SESSION['role'] == 'user') {
                header('Location: profil.php');
                exit;
            }
        } else {
            $errorMessage = 'Login ou mot de passe incorrect.';
        }
    }
}
$title = "Connexion";
$description = "Page de connexion utilisateur";
$css = "connexion";
include "includes/pageParts/header.php";
?>
<section class="center">
    <div class="login-container">
        <h1>
            Connexion
        </h1>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <label for="username">Login</label>
            <input type="text" id="username" name="login" placeholder="Nom d'utilisateur (Login)" required>

            <label for="password">Mot de passe</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <span id="togglePassword" class="eye">👁️</span>
            </div>
            <div class="g-recaptcha" data-sitekey=<?=$data_sitekey?>></div>
            <button type="submit" id="btn">Login</button>
            <p style="font-size: 0.9em;">Vous n'avez pas de compte? <a href="creationCompte.php">inscrivez-vous </a></p>
            <a href="motDePasseOublier.php">Mot de passe oublié ?</a>
        </form>

    </div>
</section>

<script>
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
<?php include "includes/pageParts/footer.php"?>
</html>