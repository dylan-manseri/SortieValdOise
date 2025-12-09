<?php
session_start();
$title="Activités dans le Val-d'Oise";
$h1="Liste d'activités";
$css = "sortie";
$description="Liste d'activités dans le Val-d'Oise";
include "includes/fonctions/activities.php";
include "includes/pageParts/header.php";
// Connexion à la base
require_once 'conf/bd_conf.php';

$props = $pdo->query("SELECT * FROM propositions;")->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($props);
// Récupérer les favoris
$login = $_SESSION['login'] ?? null;
$userFavorites = [];
if ($login) {
    $stmt = $pdo->prepare("SELECT id_sortie FROM favoris WHERE user_login = ?");
    $stmt->execute([$login]);
    $userFavorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<section class="container py-4">

    <!-- Barre de recherche centrée -->
    <div class="row justify-content-center text-center mb-4 g-3">
        
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


<?php include "includes/pageParts/footer.php" ?>
<script>
    window.props = <?= $json ?>;
    window.isLoggedIn = <?= $login ? 'true' : 'false' ?>;
    window.userFavorites = <?= json_encode($userFavorites) ?>;
</script>
<script src="includes/script/activitiesList.js" defer></script>

</html>
 