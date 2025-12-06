<?php

session_start();

require_once 'conf/bd_conf.php'; 
require_once 'recordVisit.php'; 
recordVisit($pdo);

$title="Découvrez le 95";
$description="ici plein de choses intéressantes!";
$h1="";
$css = "index";
include "includes/pageParts/header.php";
?>
<div class="carousel">
    <h1 class="carousel-title">Découvrez des activités dans le Val-d'Oise.</h1>
    <button class="btn-decouvrir carousel-btn" onclick="window.location.href='carte.php'">Découvrir</button>
    <div class="group">
        <div class="card">
            <img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/>
        </div>
        <div class="card">
            <img src="images/landscape/theatre.jpg" alt="image banc"/>
        </div>
        <div class="card">
            <img src="images/landscape/banc_valdoise.jpg" alt="image banc"/>
        </div>
        <div class="card">
            <div class="card">
                <img src="images/landscape/mediatheque.jpg" alt="image foret"/>
            </div>
        </div>
    </div>
    <div aria-hidden class="group">
        <div class="card">
            <div class="card">
                <img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/>
            </div>
        </div>
        <div class="card">
            <img src="images/landscape/theatre.jpg" alt="image banc"/>
        </div>
        <div class="card">3</div>
        <div class="card">4</div>
    </div>
</div>
    <section class="default-section" style="margin: 10% auto 10% auto">
        <h2 class="h2-presentation">S'amuser devient simple</h2>
        <p style="font-size: 20px; margin-bottom: 20px">
            Bienvenue sur <strong>SortieValdoise</strong>, votre plateforme de gestion de sortie dans le Val-d'Oise.
            Ce site vous permet de consulter en temps réel les <strong>activités</strong> de votre département.
            De plus grâce à notre système d<strong>'analyse météo</strong>, vous pouvez connaître immédiatement la météo là où vous rechercher des activités.
        </p>
        <p style="font-size: 20px; margin-bottom: 20px">
            <strong>Que l’on ait envie de s’évader au grand air ou de découvrir des lieux chargés d’histoire</strong>, le Val-d’Oise offre une richesse
            de sorties qui rythment <strong>notre quotidien.</strong>
            Le département influence nos loisirs, nos envies de découverte, et parfois même notre façon de planifier nos week-ends.
            Explorer ses espaces verts, flâner dans ses villages, profiter <strong>d’activités culturelles</strong> ou
            <strong>familiales</strong> : sortir dans le Val-d’Oise,
            c’est varier les ambiances et se laisser surprendre. Comprendre ce que propose le territoire, c’est aussi mieux anticiper ses sorties,
            s’adapter à ses envies, et <strong>développer une véritable curiosité</strong> pour un département aux multiples facettes.
        </p>
        <p style="font-size: 20px; margin-bottom: 20px">
            Notre défi est de vous offrir un accès <strong>simple, clair</strong> et pratique aux <strong>activités</strong>
            de votre département, chaque jour.
        </p>
        <p style="font-size: 20px">
            Pour consulter les activités, vous pouvez consulter notre <strong>carte interactive</strong> du Val-d'Oise ou
            indiquer des mots clé via notre <strong>barre de recherche</strong>. Par ailleurs il vous est possible d'accéder à diverses informations liées
            <strong>aux statistiques</strong>,
            de notre site.
        </p>
    </section>
<section class="default-section" id="faq">
    <h2 class="h2-question">Questions fréquemment posées</h2>
    <div class="question-parent">
        <div class="question-child">
            <div style="display: flex">
                <p class="question-symbole">📊</p>
                <div class="question">
                    <h3>Comment sont générées les données météo ?</h3>
                    <p>Les données proviennent d'une API météo professionnelle et sont mises à jour régulièrement.
                        Les prévisions reposent sur des modèles numériques complexes qui analysent l’évolution des masses d’air,
                        de la pression, de l’humidité et d’autres paramètres.</p>
                </div>
            </div>
            <div style="display: flex">
                <p class="question-symbole">🧭</p>
                <div class="question">
                    <h3>Puis-je voir la météo de ma ville ?</h3>
                    <p>
                        Oui, vous pouvez rechercher n’importe quelle ville
                        via notre barre de recherche ou activer la géolocalisation
                        pour obtenir les prévisions de votre position actuelle.
                    </p>
                </div>
            </div>
            <div style="display: flex">
                <p class="question-symbole">🌈</p>
                <div class="question">
                    <h3>Que signifient les icônes météo ?</h3>
                    <p>
                        Chaque icône représente une condition météo :
                        ☀️ pour le soleil, 🌧️ pour la pluie, ❄️ pour la neige, 🌩️ pour les orages, etc.
                        Elles vous permettent de comprendre rapidement la tendance du temps.
                    </p>
                </div>
            </div>
        </div>
        <div class="question-child">
            <div style="display: flex">
                <p class="question-symbole">🔍</p>
                <div class="question">
                    <h3>Quelle est la fiabilité des prévisions ?</h3>
                    <p>
                        Les prévisions sont très fiables à court terme (1 à 3 jours),
                        raisonnables jusqu'à 5 jours, mais deviennent progressivement incertaines au-delà,
                        en raison de la complexité des phénomènes atmosphériques.
                    </p>
                </div>
            </div>
            <div style="display: flex">
                <p class="question-symbole">🎲</p>
                <div class="question">
                    <h3>Pourquoi certaines infos sont aléatoires sur la page d’accueil ?</h3>
                    <p>
                        Certaines données affichées sont volontairement aléatoires pour enrichir l'expérience
                        utilisateur.
                        Cela permet de découvrir des faits météo insolites ou éducatifs à chaque visite.
                    </p>
                </div>
            </div>
            <div style="display: flex">
                <p class="question-symbole">⚖️</p>
                <div class="question">
                    <h3>Pourquoi la météo affichée peut-elle être différente d’un site à l’autre ?</h3>
                    <p>
                        Les sites utilisent différentes sources de données et modèles de prévision.
                        Certains privilégient la précision locale, d’autres l’étendue géographique.
                        Cela peut entraîner de légères variations selon les plateformes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include "includes/pageParts/footer.php"
?>
</html>
