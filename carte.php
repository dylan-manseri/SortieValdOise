<?php
$h1="Carte des activités";
$title="Carte des activités";
$description="Les activité du Val-d'Oise affiché sur une carte.";
$css="carte";
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="includes/script/map.js" defer></script>
    <script src="includes/script/pins.js" defer></script>

</body>
</html>