<?php
$h1="Carte des activités";
$title="Carte des activités";
$description="Les activité du Val-d'Oise affiché sur une carte.";
$css="carte";
$cdn = '<link rel="preconnect" href="https://unpkg.com" crossorigin>
<link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
';
include "includes/pageParts/header.php";
?>

     
<button class="bouton-volant" onclick="window.location.href='ajouter.php'">Ajouter une activité</button>

    <div id="side-panel">
        <button onclick="closePanel()"><span class="material-icons">close</span></button>
    </div>
    <div id="map"></div>

    <?php

    ?>
    <script>
let mapStyle = "<?=$style?>"; // récupère "light" ou "dark" depuis le header
</script>

    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="includes/script/map.js" defer></script>
    <script src="includes/script/pins.js" defer></script>
</main>
</body>
</html>