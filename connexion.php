<?php
session_start();
require_once 'conf/bd_conf.php';
require_once 'conf/captcha_conf.php';

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
  if (isset($_POST['action']) && $_POST['action'] === 'autocomplete') {
        
        // 1. Indiquer au navigateur qu'on envoie du JSON
        header('Content-Type: application/json');
        
        $searchQuery = $_POST['query'] ?? '';
        if (empty($searchQuery)) {
            echo json_encode([]);
            exit;
        }

        // 2. Recherche dans la base de données
        try {
            $searchTerm = $searchQuery . '%';
            // Requête sécurisée pour sélectionner le champ 'login' correspondant
            $stmt = $pdo->prepare("SELECT login FROM users WHERE login LIKE ? LIMIT 10"); 
            $stmt->execute([$searchTerm]); 
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
            
            // 3. Conversion du tableau PHP en chaîne JSON et envoi
            echo json_encode($results);

        } catch (PDOException $e) {
            error_log("Autocomplete DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([]);
        }
        // CRITIQUE: Arrêter l'exécution pour ne pas renvoyer le code HTML ci-dessous
        exit; 
    }

    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    if (!$recaptchaToken) {
        http_response_code(400); 
        $errorMessage = 'Veuillez cocher la case "Je ne suis pas un robot".';
    }
    
    $login = $_POST['login'] ?? null;
    $password = $_POST['password'] ?? null;
    
    if (empty($login) || empty($password)) { $errorMessage ="Login et mot de passe requis"; }


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
        http_response_code(401);
        exit('<h1>Échec de la vérification CAPTCHA. Vous êtes un robot ?</h1>');
    }


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

        if ($anonymous_tracking_id) {
          try {
              // Mise à jour de toutes les entrées 'anonymous' enregistrées avec cet UUID
              $update_sql = "
                  UPDATE visits 
                  SET login = :new_login, status = 'logged_in', tracking_id = NULL
                  WHERE tracking_id = :old_tracking_id AND status = 'anonymous'
              ";
          $update_stmt = $pdo->prepare($update_sql);
          $update_stmt->execute([
              ':new_login' => $foundUser['login'], 
              ':old_tracking_id' => $anonymous_tracking_id
          ]);
              
          } catch (PDOException $e) {
              error_log("DB Error linking anonymous history: " . $e->getMessage());
              // Continuer le processus de connexion même en cas d'erreur de suivi
          }
        } 

        recordVisit($pdo);
        header('Location: profil.php');
        exit;
    } else {
        $errorMessage = 'Login ou mot de passe incorrect.';
    }
}
$title = "Connexion";
$description = "Page de connexion utilisateur";
$css = "connexion";
include "includes/pageParts/header.php";
?>
<section class="center">
    <div class="login-container">
        <h2>
            Login
        </h2>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="suggestions-container">
                <input type="text" id="username" name="login" placeholder="Nom d'utilisateur (Login)" required autocomplete="off" value="<?php echo htmlspecialchars($login ?? ''); ?>">

                <ul id="suggestions"></ul>
            </div>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <div class="g-recaptcha" data-sitekey=<?=$data_sitekey?>></div>
            <button type="submit">Login</button>
            <p style="font-size: 0.9em;">Vous n'avez pas de compte? <a href="creationCompte.php">inscrivez-vous </a></p>
            <a href="motDePasseOublier.php">Mot de passe oublié ?</a>
        </form>

    </div>
</section>

<?php include "includes/pageParts/footer.php"?>
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
      });

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
</html>