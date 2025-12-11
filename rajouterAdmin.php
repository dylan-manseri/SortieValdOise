<?php
session_start();
require_once 'conf/bd_conf.php';

// Vérification stricte du rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
   header('Location: connexion.php');
   exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'check_username') {
  header('Content-Type: application/json');
  
  $login = $_POST['login'] ?? ''; 

  if (empty($login)) {
    echo json_encode(['available' => false]);
    exit;
  }
  
  $isTaken = false; 
  try {
    // Requête préparée pour vérifier l'existence (case-insensitive)
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Ajouter un Administrateur</title>
 <style>
  body {
   font-family: 'Permanent Marker', cursive; 
   display: flex;
   justify-content: center;
   align-items: center;
   height: 100vh; 
   margin: 0;
   background-color: #e7e8bc;
  }

  .register-container {
   background-color: #f4f4d7;
   padding: 30px 40px;
   border-radius: 10px;
   box-shadow: 0 4px 10px rgba(0,0,0,0.2);
   text-align: center;
   width: 300px;
  }

  .register-container h2 {
   margin-bottom: 20px;
   font-size: 1.8rem;
   color: #333;
  }

  .register-container input {
   width: 100%;
   padding: 10px;
   margin: 10px 0;
   font-size: 1rem;
   font-family: 'Permanent Marker', cursive; 
   border: 1px solid #ccc;
   border-radius: 5px;
  }

  .register-container button {
   position: relative;
   width: 100%;
   padding: 10px;
   margin-top: 15px;
   background-color: #7e9ad7;
   color: white;
   font-size: 1rem;
   font-family: 'Permanent Marker', cursive; 
   border: none;
   border-radius: 5px;
   cursor: pointer;
   transition: 0.2s;
  }

  .register-container button:hover {
   background-color: #7789b1;
   opacity: 0.3;
  }
  
  .register-container h2 {
   position: relative; 
   display: inline-block;
   margin-bottom: 20px;
   font-size: 2.5rem;
   color: #333;
  }

 </style>
</head>
<body>
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
   <button type="submit">Ajouter l'Admin</button>
  </form>
 </div>
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
</script>
</body>
</html>