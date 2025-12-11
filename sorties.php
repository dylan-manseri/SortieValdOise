<?php
session_start();
$title="Activités dans le Val-d'Oise";
$css = "sortie";
$description="Liste d'activités dans le Val-d'Oise";
include "includes/fonctions/activities.php";
include "includes/pageParts/header.php";
// Connexion à la base
require_once 'conf/bd_conf.php';

$props = $pdo->query("SELECT id_prop, description, titre, adresse, status, user_login, date, ville FROM propositions WHERE status='accepted';")->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($props);
// Récupérer les favoris
$login = $_SESSION['login'] ?? null;
$userFavorites = [];
if ($login) {
    try {
        $stmt = $pdo->prepare("SELECT id_sortie FROM favoris WHERE user_login = ?");
        $stmt->execute([$login]);
        $userFavorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        echo "Erreur de notre base de données.";
    }
}
?>

<section class="container py-4">
    <h1>Liste d'activités</h1>
    <button class="bouton-volant" onclick="window.location.href='ajouter.php'">Ajouter une activité</button>
    <div class="choice">
        <p>OpenAgenda</p>
    <label class="switch">
        choix
        Source
        <input type="checkbox" id="activitySwitch">
        <span class="slider"></span>
    </label>
        <p>Utilisateurs</p>
    </div>
    <!-- Barre de recherche centrée -->
    <div id="filter" class="row justify-content-center text-center mb-4 g-3">
        
        <div class="col-12 col-md-6">
            <label for="searchInput" class="form-label">Indiquez des mots clés</label>
            <div class="search w-100">
                <span class="search-icon material-symbols-outlined">search</span>
                <input id="searchInput" class="search-input" type="search" placeholder="Rechercher">
            </div>
        </div>

        <div class="col-10 col-md-3">
            <label for="cities" class="form-label">Sélectionner une ville</label>
            <select id="cities" class="form-select">
                <option value="">-- Ville --</option>
            </select>
        </div>

    </div>

    <!-- ZONE D'AFFICHAGE DES CARTES -->
    <div id="results" class="row justify-content-center gy-4"></div>
    <button id="addBtn" onclick="showCards()">
        <span class="material-icons">add</span>
    </button>

</section>

<script defer>
    window.props = <?= $json ?>;
    window.isLoggedIn = <?= $login ? 'true' : 'false' ?>;
    window.userFavorites = <?= json_encode($userFavorites) ?>;
</script>
<script src="includes/script/activitiesList.js" defer></script>
<?php include "includes/pageParts/footer.php" ?>
</html>
 