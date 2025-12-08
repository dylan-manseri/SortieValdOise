<?php
$title = "Plan du site";
$description = "Plan du site SortieValdoise pour navigation rapide";
$css = "sitemap";
include "includes/pageParts/header.php";
?>

<section class="sitemap-container">
    <h1>Plan du site</h1>

    <div class="sitemap-grid">

        <div class="sitemap-box">
            <h2>Pages principales</h2>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="sorties.php">Liste des activités</a></li>
                <li><a href="carte.php">Carte des activités</a></li>
            </ul>
        </div>

        <div class="sitemap-box">
            <h2>Activités</h2>
            <ul>
                <li><a href="ajouter.php">Ajouter une activité</a></li>
                <li><a href="detail_evenement.php">Détails d'un événement</a></li>
            </ul>
        </div>

        <div class="sitemap-box">
            <h2>Espace utilisateur</h2>
            <ul>
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="creationCompte.php">Créer un compte</a></li>
                <li><a href="profil.php">Profil utilisateur</a></li>
                <li><a href="modifier_profil.php">Modifier mon profil</a></li>
                <li><a href="motDePasseOublier.php">Mot de passe oublié</a></li>
                <li><a href="changerMdp.php">Changer mon mot de passe</a></li>
                <li><a href="supprimer_compte.php">Supprimer mon compte</a></li>
            </ul>
        </div>

        <div class="sitemap-box">
            <h2>Administration</h2>
            <ul>
                <li><a href="admin.php">Tableau de bord admin</a></li>
                <li><a href="rajouterAdmin.php">Ajouter un administrateur</a></li>
                <li><a href="gestionUtilisateurs.php">Gestion des utilisateurs</a></li>
            </ul>
        </div>

        <div class="sitemap-box">
            <h2>Légal & SEO</h2>
            <ul>
                <li><a href="sitemap.php">Plan du site</a></li>
                <li><a href="sitemap.xml">Sitemap XML</a></li>
                <li><a href="robots.txt">robots.txt</a></li>
            </ul>
        </div>

    </div>
</section>

<?php include "includes/pageParts/footer.php"; ?>
</html>
