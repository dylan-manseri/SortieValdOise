<?php
session_start();
$title = "À propos";
$description = "Informations sur le projet et l'équipe de développement.";
$css = "about";
include "includes/pageParts/header.php";
?>

<section class="apropos-container">

    <h1>À propos du projet</h1>

    <div class="content-block">
        <p>
            Ce site web a pour objectif de recenser, présenter et faciliter la découverte 
            des activités disponibles dans le <strong>Val-d’Oise</strong>.  
            Il permet aux utilisateurs de consulter des lieux, événements, sorties, 
            mais aussi de proposer leurs propres activités.
        </p>
        <p>
            L’interface a été pensée pour être simple, moderne et accessible, 
            avec un mode clair 🌞 et un mode sombre 🌙.
        </p>
    </div>

    <div class="content-block">
        <h2>Contexte pédagogique</h2>
        <p>
            Ce site est un projet pédagogique réalisé dans le cadre du module 
            <strong>Développement Web</strong> de la 
            <strong>Licence 3 Informatique – CY Cergy Paris Université</strong>, 
            année universitaire <strong>2025-2026</strong>.
        </p>
    </div>

    <div class="content-block">
        <h2>Équipe du projet</h2>
        <ul class="team-list">
            <li><strong>👤 Dylan MANSERI</strong></li>
            <li><strong>👤 Fariza FARADJI</strong></li>
            <li><strong>👤 Farah OUAIL</strong></li>
            <li><strong>👤 Nouha ELYAMANY</strong></li>
        </ul>
    </div>

    <div class="content-block">
        <h2>Fonctionnalités principales</h2>
        <ul class="features">
            <li>📍 Affichage des activités sur une carte interactive</li>
            <li>🔎 Recherche d’événements + filtrage par ville</li>
            <li>❤️ Possibilité d’ajouter des favoris</li>
            <li>👤 Comptes utilisateurs & connexion sécurisée</li>
            <li>📝 Proposition d’activités ajoutées par les utilisateurs</li>
            <li>🛠 Espace administrateur pour la modération des propositions et la gestion des utilisateurs</li>
        </ul>
    </div>

    <div class="content-block center">
        <a href="index.php" class="btn-retour">Retour à l'accueil</a>
    </div>

</section>

<?php include "includes/pageParts/footer.php"; ?>
</html>
