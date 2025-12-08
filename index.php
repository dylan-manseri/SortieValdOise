<?php
$title="Découvrez le 95";
$description="ici plein de choses intéressantes!";
$css = "index";
include "includes/pageParts/header.php"; 
?>


<div class="carousel">
    <h1 class="carousel-title">Découvrez des activités dans le Val-d'Oise.</h1>
    <button class="btn-decouvrir carousel-btn" onclick="window.location.href='carte.php'">Découvrir</button>

    <!-- Première boucle -->
    <div class="group">
        <div class="card"><img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/></div>
        <div class="card"><img src="images/landscape/theatre.jpg" alt="image theatre"/></div>
        <div class="card"><img src="images/landscape/banc_valdoise.jpg" alt="image banc"/></div>
        <div class="card"><img src="images/landscape/mediatheque.jpg" alt="image mediatheque"/></div>
    </div>

    <!-- Deuxième boucle pour l’animation infinie -->
    <div class="group" aria-hidden="true">
        <div class="card"><img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/></div>
        <div class="card"><img src="images/landscape/theatre.jpg" alt="image theatre"/></div>
        <div class="card"><img src="images/landscape/banc_valdoise.jpg" alt="image banc"/></div>
        <div class="card"><img src="images/landscape/mediatheque.jpg" alt="image mediatheque"/></div>
    </div>
</div>


<section class="default-section container my-5">
    <h2 class="h2-presentation">S'amuser devient simple</h2>

    <p class="fs-5 mb-3">
        Bienvenue sur <strong>SortieValdoise</strong>, votre plateforme de gestion de sortie dans le Val-d'Oise.
        Ce site vous permet de consulter en temps réel les <strong>activités</strong> de votre département.
        De plus grâce à notre système d<strong>'analyse météo</strong>, vous pouvez connaître immédiatement la météo là où vous rechercher des activités.
    </p>

    <p class="fs-5 mb-3">
        <strong>Que l’on ait envie de s’évader au grand air ou de découvrir des lieux chargés d’histoire</strong>,
        le Val-d’Oise offre une richesse de sorties qui rythment <strong>notre quotidien.</strong>
        Explorer ses espaces verts, flâner dans ses villages, profiter <strong>d’activités culturelles</strong> ou
        <strong>familiales</strong> : sortir dans le Val-d’Oise,
        c’est varier les ambiances et se laisser surprendre...
    </p>

    <p class="fs-5 mb-3">
        Notre défi est de vous offrir un accès <strong>simple, clair</strong> et pratique aux <strong>activités</strong>
        de votre département, chaque jour.
    </p>

    <p class="fs-5">
        Pour consulter les activités, vous pouvez consulter notre <strong>carte interactive</strong> du Val-d'Oise
        ou indiquer des mots clé via notre <strong>barre de recherche</strong>.
        Il vous est aussi possible d'accéder à diverses informations liées <strong>aux statistiques</strong> du site.
    </p>
</section>


<!--FAQ AVEC BOOTSTRAP -->

<section class="default-section container my-5" id="faq">
    <h2 class="h2-question">Questions fréquentes</h2>

    <div class="row">

        <!-- Colonne 1 -->
        <div class="col-lg-6">
            <div class="d-flex mb-4">
                <p class="question-symbole me-2">❓</p>
                <div class="question">
                    <h3>Que propose SortieValdoise ?</h3>
                    <p>SortieValdoise vous permet de découvrir et de partager des activités dans le Val-d’Oise. 
                    Vous y trouverez des sorties culturelles, sportives, familiales ou encore des balades en pleine nature.</p>
                </div>
            </div>

            <div class="d-flex mb-4">
                <p class="question-symbole me-2">👥</p>
                <div class="question">
                    <h3>Les sorties viennent-elles seulement de l’équipe du site ?</h3>
                    <p>Non ! Les sorties sont proposées à la fois par nos utilisateurs et par nos partenaires. 
                    Chacun peut enrichir la plateforme en partageant ses propres idées de sorties.</p>
                </div>
            </div>

            <div class="d-flex mb-4">
                <p class="question-symbole me-2">🔐</p>
                <div class="question">
                    <h3>Dois-je créer un compte pour consulter les sorties ?</h3>
                    <p>Non, la consultation est libre et gratuite. 
                    La création de compte n'est nécessaire que si vous souhaitez proposer vos propres sorties.</p>
                </div>
            </div>
        </div>

        <!-- Colonne 2 -->
        <div class="col-lg-6">
            <div class="d-flex mb-4">
                <p class="question-symbole me-2">🎯</p>
                <div class="question">
                    <h3>Puis-je filtrer les activités selon mes préférences ?</h3>
                    <p>Oui ! Vous pouvez rechercher une sortie par type (randonnée, concert, exposition…), 
                    par ville et selon d’autres critères.</p>
                </div>
            </div>

    
        </div>
    </div>
</section>


<?php include "includes/pageParts/footer.php"; ?>
</html>
