<?php
$h1="Carte des activités";
$title="Carte des activités";
$description="Les activité du Val-d'Oise affiché sur une carte.";
$css="carte";
include "includes/pageParts/header.php";
?>
<style>
    #side-panel {
        position: fixed;
        top: 0;
        left: 0;
        width: 500px;            /* largeur du panneau */
        height: 100vh;
        background: #f7f7f7;
        box-shadow: 2px 0 10px rgba(0,0,0,0.25);
        transform: translateX(-100%);  /* caché à gauche */
        transition: transform 0.35s ease;
        z-index: 9999;           /* devant tout */
        padding: 20px;
        overflow-y: auto;
        overflow-x: hidden;
        display flex;
    }

    #side-panel.open {
        transform: translateX(0);   /* panneau visible */
    }

    figure{
        text-align: center;
    }
    img{
        border-radius: 15px;
    }

    h2{
        margin-top: 8px;
        font-size: 20px;
        font-family: 'Playfair Display', serif;
    }

    h3 {
        background: #f2f2f2;
        padding: 8px 14px;
        border-left: 4px solid #81a3f6;
        border-radius: 5px;
        font-size: 0.9rem;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 8px; /* espace entre l’emoji et le texte */
        margin-bottom: 8px;
    }

    h3 .material-icons {
        font-size: 15px; /* ou 16px, 20px… */
    }
</style>
<!-- Import Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div id="side-panel"></div>
    <div id="map"></div>

    <?php

    ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="includes/script/map.js" defer></script>
    <script src="includes/script/pins.js" defer></script>
</body>
</html>