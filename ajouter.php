<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: connexion.php");
}
include "conf/bd_conf.php";
include "includes/fonctions/icon.php";
//echo "coucou";
if(isset($_POST["title"]) && isset($_POST['adr']) && isset($_POST['desc']) && isset($_POST["date"]) && isset($_POST["ville"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK){
    $img = verifImage($_FILES["image"], "ajouter", 800, 800);
    try {
        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $req = $pdo->prepare("INSERT INTO propositions (description, titre, adresse, status, user_login, date, ville, image, type_mime) VALUES 
                                                                               (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $req->execute([$_POST["desc"], $_POST["title"], $_POST["adr"], 'pending', $_SESSION['login'], $_POST["date"], $_POST["ville"], $img[0], $img[1]]);
        $result = $req->fetch(PDO::FETCH_ASSOC);
        header("Location: ajouter.php?add=true");
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

$title="Ajouter une activité";
$css="ajouter";
$description="Page pour ajouter une activité";
include "includes/pageParts/header.php";
?>
<?php if(isset($_GET["error"]) && $_GET["error"] === "dim"):?>
    <p class="error-msg">Merci d'envoyer un fichier de taille <= à 800px</p>
<?php elseif (isset($_GET["error"]) && $_GET["error"] === "file"):?>
    <p class="error-msg">Merci d'envoyer un fichier valide ! Les formats autorisé : png, jpeg, webp.</p>
<?php elseif (isset($_GET["error"]) && $_GET["error"] === "size"):?>
    <p class="error-msg">Fichier trop volumineux, taille maximale autorisée : 300Ko</p>
<?php elseif (isset($_GET["add"]) && $_GET["add"] === "true"):?>
    <p class="validate-msg">Activité ajoutée avec succès, elle sera vérifiée dans les 48h.</p>
<?php endif?>
    <div class="container">
        
        <div class="card">
            <div class="card-image">
                <h2 class="card-heading">
                    Commençons
                    <small>Faites nous découvrir vos aventures</small>
                </h2>
            </div>
            <form action="ajouter.php" method="post" class="card-form" enctype="multipart/form-data">
                <div class="input">
                    <input type="text" name="title" class="input-field" value="Balade à Montmorency" required/>
                    <label class="input-label">Titre</label>
                </div>
                <div class="input">
                    <input type="text" name="desc" class="input-field" value="Découverte du parc" required/>
                    <label class="input-label">Description</label>
                </div>
                <div class="input">
                    <input type="text" name="adr" class="input-field" required/>
                    <label class="input-label">Adresse</label>
                </div>
                <div class="input">
                    <label style="text-align: center;">Quand y êtes vous aller ou quand allez-vous y aller ?
                        <input type="date" name="date" class="input-field" required/>
                    </label>
                </div>
                <div class="input">
                    <input type="text" name="ville" class="input-field" required/>
                    <label class="input-label">Ville</label>
                </div>
                <label style="margin-top:15px;"> Choisir une image
                    <input type="file" name="image" accept="image/*" required>
                </label>
                <div class="action">
                    <button class="action-button">Publier</button>
                </div>

            </form>
            <div class="card-info">
                <p>Avant d'être publié vos activités sont validées par nos administrateurs</p>
            </div>
        </div>
    </div>


<?php include "includes/pageParts/footer.php" ?>
</html>