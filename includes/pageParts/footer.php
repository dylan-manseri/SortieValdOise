

</main>
<footer class="main-footer">
    <div class="container">

        <div class="footer-content row justify-content-center text-center text-md-start">

            <!-- Réseaux sociaux = 1er bloc -->
            <div class="footer-social col-12 mb-4">
                <a href="https://x.com/EtTemps81032" target="_blank" class="mx-2 d-inline-block">
                    <img src="images/footer/twitter.webp" alt="X" width="35"/>
                </a>
                <a href="https://www.instagram.com/cielettemps_officiel" target="_blank" class="mx-2 d-inline-block">
                    <img src="images/footer/instagram.webp" alt="Instagram" width="35"/>
                </a>
                <a href="https://www.youtube.com/@CielEtTemps-Officiel" target="_blank" class="mx-2 d-inline-block">
                    <img src="images/footer/youtube.webp" alt="YouTube" width="35"/>
                </a>
            </div>

            <!-- 4 blocs responsive -->
            <div class="footer-column col-12 col-sm-6 col-lg-3 mb-4">
                <p>🌐 SortieValdoise</p>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="carte.php">Carte d'activités</a></li>
                    <li><a href="stats.php">Mes activités</a></li>
                    <li><a href="about.php">À propos</a></li>
                </ul>
            </div>

            <div class="footer-column col-12 col-sm-6 col-lg-3 mb-4">
                <p>⚙️ Fonctionnalités</p>
                <ul>
                    <li><a href="meteo.php#map">Recherche par mot clé/ville</a></li>
                    <li><a href="index.php#locateWeather">Ajout d'activités</a></li>
                    <?php if (!isset($_GET["style"]) || $_GET["style"] == "light"): ?>
                        <li><a href="?style=dark">Changement de mode visuel</a></li>
                    <?php else: ?>
                        <li><a href="?style=light">Changement de mode visuel</a></li>
                    <?php endif; ?>
                    <li><a href="cookies.php">Traitement des cookies</a></li>
                </ul>
            </div>

            <div class="footer-column col-12 col-sm-6 col-lg-3 mb-4">
                <p>🗂️ Ressources</p>
                <ul>
                    <li><a href="sitemap.php">Sitemap</a></li>
                    <li><a href="mentions.php">Mentions légales</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="index.php#faq">FAQ</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom text-center mt-4">
            <p>Réalisé par Dylan Manseri et Amadou Bawol — Licence 2 Informatique, CY Cergy Paris Université</p>
            <p>© Copyright 2025</p>
        </div>
    </div>
</footer>
</body>