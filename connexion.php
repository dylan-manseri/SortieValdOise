<?php
session_start();
if(isset($_SESSION['login']) && $_SESSION['login']!== ""){
    header("Location: profil.php");
    exit;
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
  if (isset($_POST['action']) && $_POST['action'] === 'autocomplete') {
        
        header('Content-Type: application/json');
        
        $searchQuery = $_POST['query'] ?? '';
        if (empty($searchQuery)) {
            echo json_encode([]);
            exit;
        }
        try {
            $searchTerm = $searchQuery . '%';
            $stmt = $pdo->prepare("SELECT login FROM users WHERE login LIKE ? LIMIT 10"); 
            $stmt->execute([$searchTerm]); 
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
            echo json_encode($results);

        } catch (PDOException $e) {
            error_log("Autocomplete DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([]);
        }
        exit; 
    }

    
    $login = $_POST['login'] ?? null;
    $password = $_POST['password'] ?? null;
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;
    
    if (empty($login) || empty($password)) { $errorMessage ="Login et mot de passe requis"; }

    else if (!$recaptchaToken) {
        $errorMessage = 'Veuillez cocher la case "Je ne suis pas un robot".';
    }    

    if(empty($errorMessage)){
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
        $errorMessage='Échec de la vérification CAPTCHA. Veuillez réessayer.';
    }

    if(empty($errorMessage)){
    try {
      $stmt = $pdo->prepare("SELECT login, hashedPassword, nom_user, prenom_user, role FROM users WHERE login = ?");
      $stmt->execute([$login]);
      
      $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);

      } catch (PDOException $e) {
          http_response_code(500);
          exit('Database error: ' . $e->getMessage());
      }
      
      if ($foundUser && password_verify($password, $foundUser['hashedPassword'])) {

        $tracking_cookie_name = 'user_tracking_id'; 
        $anonymous_tracking_id = $_COOKIE[$tracking_cookie_name] ?? null;


        $_SESSION['login'] = $foundUser['login'];
        $_SESSION['name'] = $foundUser['nom_user'];
        $_SESSION['pren'] = $foundUser['prenom_user'];
        $_SESSION['role'] = $foundUser['role'];


        if($_SESSION['role']=='admin'){
            header('Location: admin.php');
            exit;
        }
        else if($_SESSION['role']=='user'){
        header('Location: profil.php');
        exit;
        }
        } else {
        $errorMessage = 'Login ou mot de passe incorrect.';
        }
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
            <div class="suggestions-container">
                <label for="username">Login</label><input type="text" id="username" name="login" placeholder="Nom d'utilisateur (Login)" required autocomplete="off" value="<?php echo htmlspecialchars($login ?? ''); ?>">

                <ul id="suggestions"></ul>
            </div>
            <label>Mot de passe
                <input type="password" name="password" placeholder="Mot de passe" required>
            </label>
            <div class="g-recaptcha" data-sitekey=<?=$data_sitekey?>></div>
            <button type="submit" id="btn">Login</button>
            <p style="font-size: 0.9em;">Vous n'avez pas de compte? <a href="creationCompte.php">inscrivez-vous </a></p>
            <a href="motDePasseOublier.php">Mot de passe oublié ?</a>
        </form>

    </div>
</section>

<script>
  const usernameInput = document.getElementById('username');
  const suggestionsBox = document.getElementById('suggestions');
  let currentAbortController = null;

  usernameInput.addEventListener('input', () => {
  const query = usernameInput.value.trim();
  suggestionsBox.innerHTML = ''; 

  if (currentAbortController) { 
      currentAbortController.abort(); 
  } 
  currentAbortController = new AbortController(); 
  const signal = currentAbortController.signal; 

  if (query.length > 0) {
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `query=${encodeURIComponent(query)}&action=autocomplete`,
      signal: signal 
  })
  .then(response => {
    const contentType = response.headers.get("content-type");
    if (response.ok && contentType && contentType.includes("application/json")) {
      return response.json();
    } else {
      console.error("Erreur de format de réponse ou statut non OK:", response.status);
      return [];
    }
  })
  .then(matchingUsers => {
    if (signal.aborted) return;

    matchingUsers.forEach(loginSuggestion => {
      const listItem = document.createElement('li');
      listItem.textContent = loginSuggestion;

      listItem.addEventListener('click', () => {
      usernameInput.value = loginSuggestion;
      suggestionsBox.innerHTML = '';
      });x

      suggestionsBox.appendChild(listItem);
    });
  })
  .catch(error => {
      if (error.name === 'AbortError') return; 
      console.error("Erreur lors de la récupération des suggestions:", error);
  });
}
});
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php include "includes/pageParts/footer.php"?>
</html>