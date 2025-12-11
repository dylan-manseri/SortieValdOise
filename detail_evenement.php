<?php
if(!isset($_GET["uid"])){
    //$url = strtok($_SERVER['REQUEST_URI'], '?');
    //$query = http_build_query($_GET);
    header("Location: sortie.php");
}
$title = "Detail d'événement";
$css = "detail";
$description = "Page dédié aux détails d'un événement";
$url = "https://sortievaldoise.alwaysdata.net/data/activitiesJson.php";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$data = curl_exec($ch);
curl_close($ch);

//$data = file_get_contents("https://sortievaldoise.alwaysdata.net/data/activitiesJson.php");
$json = json_decode($data, true);
$case = 0;
if(!isset($json[$_GET["uid"]])){
    include "conf/bd_conf.php";
    $stmt = $pdo->prepare("SELECT * FROM propositions WHERE id_prop = ?");
    $stmt->execute([$_GET["uid"]]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    $case = 1;
    $event['title'] = $event['titre'];
    if(!isset($event)){
        echo "<h1 style='text-align:center'> <strong>Activité introuvable<strong> </h1>";
        exit;
    }
}
else{
    $event = $json[$_GET["uid"]];
}
$h1 = $event["title"];
include "includes/pageParts/header.php"
?>
<section class="default-section">
    <?php if ($case == 0): ?>
        <figure>
            <img src="<?= $event["image"] ?>" alt="Image d'illustration">
            <figcaption>Image d'illustration fournit par <?= $event['source'] ?></figcaption>
        </figure>
        <div id="info">
            <h2>Détails</h2>
            <h3 class="card-title details-section"><span class="material-icons">description</span>Description</h3>
            <p><?= $event["description"] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">location_on</span> Localisation</h3>
            <p><?= $event['address'] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">apartment</span>Lieu</h3>
            <p><?= $event['name'] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">event</span>Date</h3>
            <p><?= $event["dateFr"] ?></p>
        </div>
    <?php else: ?>
        <figure>
            <img src="showIcon.php?id=<?= $event["id_prop"] ?>" alt="Image d'illustration">
            <figcaption>Image d'illustration fournit par <?= $event['user_login'] ?></figcaption>
        </figure>
        <div id="info">
            <h2>Détails</h2>
            <h3 class="card-title details-section"><span class="material-icons">description</span>Description</h3>
            <p><?= $event["description"] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">location_on</span> Localisation</h3>
            <p><?= $event['adresse'] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">apartment</span>Ville</h3>
            <p><?= $event['ville'] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">event</span>Date</h3>
            <p><?= $event["date"] ?></p>
            <h3 class="card-title details-section"><span class="material-icons">account_circle</span>Auteur</h3>
            <p><?= $event['user_login'] ?></p>
        </div>
    <?php endif ?>
</section>



<?php include "includes/pageParts/footer.php"?>
</html>
