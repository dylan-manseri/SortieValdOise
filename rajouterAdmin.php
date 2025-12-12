<?php
session_start();
require_once 'conf/bd_conf.php';

// Vérification du rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
   header('Location: connexion.php');
   exit; 
}

$title = "Page rajouter Admin";
$css = "rajouterAdmin";  
$description = "Page dédiée au rajout d’un administrateur";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'check_username') {
  header('Content-Type: application/json');
  
  $login = $_POST['login'] ?? ''; 

  if (empty($login)) {
    echo json_encode(['available' => false]);
    exit;
  }
  
  $isTaken = false; 
  try {
    // Requête préparée pour vérifier l'existence 
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(?)");
    $stmt->execute([$login]);
    
    if ($stmt->fetchColumn() > 0) {
      $isTaken = true;
    }

  } catch (PDOException $e) {
    error_log("DB Error during AJAX check: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['available' => false, 'error' => 'Erreur de base de données.']);
    exit;
  }
  
  echo json_encode(['available' => !$isTaken]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanctuarisation des entrées
  $login = trim($_POST['login'] ?? null);
  $nom_user = trim($_POST['nom_user'] ?? null);
  $prenom_user = trim($_POST['prenom_user'] ?? null);
  $email = trim($_POST['email'] ?? null);
  $password = $_POST['password'] ?? null; 

  $errorMessage = '';
  $regex = '/^(?=.*\d)(?=.*[a-z]).{8,}$/';
  if (empty($login) || empty($nom_user) || empty($prenom_user) || empty($email) || empty($password)) { 
        $errorMessage ="Tous les champs sont requis.";
  }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "L'adresse e-mail n'est pas valide.";
  } elseif (strlen($password) < 8) {
        $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
  } elseif (!preg_match($regex, $password)) {
    $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères, un chiffre et une lettre minuscule.';
  }
    
    // if (!empty($errors)) {
    //     http_response_code(400);
    //     $errorMessage = 'Erreur(s) de validation: ' . implode(" | ", $errors);
    // }

if(empty($errorMessage)){
try {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(?) OR email = ?");
  $stmt->execute([$login, $email]);
  if ($stmt->fetchColumn() > 0) {
    http_response_code(409);
    exit('Un Admin avec ce login ou cet e-mail existe déjà.');
  }

  // PRÉ-ENREGISTREMENT
  $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  
  $role = 'admin';
  $stmt = $pdo->prepare("INSERT INTO users (login, nom_user, prenom_user, email, hashedPassword, code_genere, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
  $stmt->execute([$login, $nom_user, $prenom_user, $email, $hashedPassword, $verificationCode, $role]);
  $_SESSION['flash_message'] = 'Administrateur rajouté avec succès !';
  header('Location: admin.php');
  exit;

} catch (PDOException $e) {
  error_log("Erreur DB lors de l'enregistrement admin: " . $e->getMessage());
  http_response_code(500);
  exit('Erreur de base de données lors de l\'enregistrement.');
} catch (\Exception $e) {
  error_log("Erreur inattendue lors de l'enregistrement: " . $e->getMessage());
  http_response_code(500);
  exit('Une erreur inattendue est survenue.');
}
}
}
include "includes/pageParts/header.php";
?>
<div class="page-wrapper">
<div class="register-container">
  <h2>
   Ajouter Admin
  </h2>
      <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

      <form action="" method="POST">
   <input type="text" name="login" placeholder="Nom d'utilisateur (Login)" id="username-input" maxlength="12" required value="<?= htmlspecialchars($login ?? '') ?>">
    <span id="username-error" style="color: red; font-size: 0.9em; height: 1em;"></span>   
   <input type="text" name="nom_user" placeholder="Nom" maxlength="30" required value="<?= htmlspecialchars($nom_user ?? '') ?>">
   <input type="text" name="prenom_user" placeholder="Prénom" maxlength="30" required value="<?= htmlspecialchars($prenom_user ?? '') ?>">
   <input type="email" name="email" placeholder="Email" maxlength="50" required value="<?= htmlspecialchars($email ?? '') ?>">
   <input type="password" name="password" placeholder="Mot de passe" required>
   <button type="submit" class="unified-btn">Ajouter l'Admin</button>
     <a href="admin.php" class="unified-btn">Retour au Tableau de Bord</a>
  </form>
  <script>
 const usernameInput = document.getElementById('username-input');
 const errorMessageSpan = document.getElementById('username-error');
 let currentAbortController = null; 

 // Cette fonction s'exécute à chaque frappe dans le champ du nom d'utilisateur
 usernameInput.addEventListener('input', function() {
  const login = usernameInput.value.trim(); 

  // Effacer le message précédent
  errorMessageSpan.textContent = '';
   
   // Annuler la requête précédente pour ne pas avoir de résultats en double
  if (currentAbortController) { 
    currentAbortController.abort(); 
  }
  currentAbortController = new AbortController(); 
  const signal = currentAbortController.signal; 

  // Commencer la vérification seulement après 3 caractères
  if (login.length >= 3) {
   
   // La requête AJAX pointe vers le même fichier avec l'action 'check_username'
   fetch('?action=check_username', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `login=${encodeURIComponent(login)}`, 
    signal: signal 
   })
   .then(response => {
     if (!response.ok) throw new Error('Network response was not ok');
     return response.json();
   })
   .then(data => {
    if (signal.aborted) return; 
         
    if (!data.available) {
     errorMessageSpan.textContent = 'Ce login est déjà utilisé.';
     errorMessageSpan.style.color = 'red';
    } else {
     errorMessageSpan.textContent = 'Login disponible !';
     errorMessageSpan.style.color = 'green';
    }
   })
   .catch(error => {
    if (error.name === 'AbortError') return; 
    console.error("Erreur de vérification AJAX:", error);
    errorMessageSpan.textContent = 'Erreur lors de la vérification.';
    errorMessageSpan.style.color = 'orange';
   });
  }
 });
 usernameInput.addEventListener('focus', () => {
    errorMessageSpan.textContent = '';
});
</script>
</div>
</div>
<?php include "includes/pageParts/footer.php"; ?>