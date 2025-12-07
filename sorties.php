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

// Récupérer les favoris
$login = $_SESSION['login'] ?? null;
$userFavorites = [];
if ($login) {
    $stmt = $pdo->prepare("SELECT id_sortie FROM favoris WHERE user_login = ?");
    $stmt->execute([$login]);
    $userFavorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

    <section class="main-container">
        <div style="display: flex; gap: 5px; text-align: center; justify-content: center;">
            <div style="display:flex; flex-direction: column;">
                <label for="searchInput">Indiquez des mots clés</label>
                <div class="search">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input id="searchInput" class="search-input" type="search" placeholder="Rechercher">
                </div>
            </div>
            <div class="select-container">
                <label for="cities">Sélectionner une ville</label>
                <select id="cities">
                    <option value="">-- Ville --</option>
                </select>
            </div>
        </div>
        <div id="results" style="padding-top: 10px;">

        </div>
    </section>

<?php include "includes/pageParts/footer.php" ?>
<script>
    window.isLoggedIn = <?= $login ? 'true' : 'false' ?>;
    window.userFavorites = <?= json_encode($userFavorites) ?>;
</script>
<script src="includes/script/activitiesList.js" defer></script>

</html>
