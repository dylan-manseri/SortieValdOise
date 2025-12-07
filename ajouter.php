<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: connexion.php");
}
include "conf/bd_conf.php";
if(isset($_POST["title"]) && isset($_POST['adr']) && isset($_POST['desc'])){
    try {
        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $req = $pdo->prepare("INSERT INTO propositions (description, titre, adresse, status, user_login) VALUES 
                                                                               (?, ?, ?, ?, ?)");
        $req->execute([$_POST["desc"], $_POST["title"], $_POST["adr"], 'pending', $_SESSION['login']]);
        $result = $req->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

$title="Ajouter une activité";
$css="ajouter";
$description="Page pour ajouter une activité";
include "includes/pageParts/header.php";
?>
<h1 style="margin-top:10px">Faites nous découvrir !</h1>
    <div class="container">
        <!-- code here -->
        <div class="card">
            <div class="card-image">
                <h2 class="card-heading">
                    Commençons
                    <small>Faites nous découvrir vos aventures</small>
                </h2>
            </div>
            <form action="ajouter.php" method="post" class="card-form">
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
                <div class="action">
                    <button class="action-button">Publier</button>
                </div>
            </form>
            <div class="card-info">
                <p>Avant d'être publié vos activités sont validé par nos administrateurs</p>
            </div>
        </div>
    </div>


<?php include "includes/pageParts/footer.php" ?>
</html>