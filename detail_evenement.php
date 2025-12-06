<?php
if(!isset($_GET["uid"])){
    //$url = strtok($_SERVER['REQUEST_URI'], '?');
    //$query = http_build_query($_GET);
    header("Location: sortie.php");
}
$title = "Detail d'événement";
$css = "detail";
$description = "Page dédié aux détails d'un événement";
$data = file_get_contents("https://sortievaldoise.alwaysdata.net/data/activitiesJson.php");
$json = json_decode($data, true);
$event = $json[$_GET["uid"]];
$h1 = $event["title"];
include "includes/pageParts/header.php"
?>
<section class="default-section">
    <figure>
        <img src="<?= $event["image"] ?>" alt="Image d'illustration">
        <figcaption>Image d'illustration fournit par <?= $event['source'] ?></figcaption>
    </figure>
<div id="info">
    <h2>Détails</h2>
    <h3 class="card-title details-section"><span class="material-icons">location_on</span> Localisation</h3>
    <p><?= $event['address'] ?></p>
    <h3 class="card-title details-section"><span class="material-icons">apartment</span>Lieu</h3>
    <p><?= $event['name'] ?></p>
    <h3 class="card-title details-section"><span class="material-icons">event</span>Date</h3>
    <p><?= $event["dateFr"] ?></p>
    <h3 class="card-title details-section"><span class="material-icons">description</span>Description</h3>
    <p><?= $event["description"] ?></p>
</div>
</section>



<?php include "includes/pageParts/footer.php"?>
</html>
