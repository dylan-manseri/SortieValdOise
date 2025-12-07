function display(eventsList){
    const results = document.getElementById("results");
    results.innerHTML="";
    eventsList.forEach(event => {
        const div = document.createElement("div")
        const card = document.createElement("div");
        card.classList.add("card-event");

        card.innerHTML = `
            <div style="width:150px; height:120px; background:#ccc;"><img src="${event.icon}" alt="illustration"/></div>
            <div class="infos">
                <h2 style="margin:0 0 10px 0;font-family: 'Playfair Display', serif;">
                    <a href="detail_evenement.php?uid=${event.uid}" style="text-decoration:none; color:#333;">
                        ${event.title}
                    </a>
                </h2>

                <div>
                    <span style="background:#3498db; color:white; padding:3px 8px; border-radius:3px; font-size:0.8em;">
                        ${event.ville}
                    </span>

                    <span class="date-badge">
                        Le ${new Date(event.date).toLocaleDateString("fr-FR")}
                    </span>
                </div>
            </div>
        `;

        // Bouton favoris
        const favBtn = document.createElement("button");
        favBtn.className = "favorite-btn";
        favBtn.dataset.id = event.uid;
        favBtn.textContent = userFavorites.includes(event.uid) ? "❤️" : "♡";

        favBtn.addEventListener("click", () => {
            if(!window.isLoggedIn){
                // Rediriger vers la page de connexion
                window.location.href = "connexion.php";
                return;
            }
            toggleFavorite(favBtn);
        });
        card.appendChild(favBtn);

        results.appendChild(card);
    });
}

function toggleFavorite(btn) {
    const id = btn.dataset.id;

    fetch("toggleFavorite.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_sortie: id })
    })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                btn.textContent = data.isFavorite ? "❤️" : "♡";

                if(data.isFavorite){
                    if(!userFavorites.includes(id)) userFavorites.push(id);
                } else {
                    const index = userFavorites.indexOf(id);
                    if(index > -1) userFavorites.splice(index,1);
                }
            }
        });
}

fetch("data/activitiesJson.php")
    .then(response => response.json())
    .then(data => {
        const eventsArray = Object.values(data);
        const searchInput = document.getElementById("searchInput");
        const selectCities = document.getElementById("cities");

        eventsArray.forEach(event => {
            if (event.ville !== undefined && event.ville !== null && event.ville !== "") {
                cities[event.ville] = event.ville;
            }
        });
        Object.values(cities).forEach(c => {
            const option = document.createElement("option");
            option.value = c;
            option.textContent = c;
            selectCities.appendChild(option);
        })

        searchInput.addEventListener("input", () => {
            if(searchInput.value === ""){
                display(eventsArray);
            }
            document.createElement("div").innerHTML="";
            const term = searchInput.value.toLowerCase().trim();
            let i = 0
            const filtered = eventsArray.filter(ev => {
                const title = (ev.title ?? "").toLowerCase();
                const keywordMatch = Array.isArray(ev.keywords)
                    ? ev.keywords.some(kw => kw.toLowerCase().includes(term))
                    : false;
                let cityTest = true;
                if(selectCities.value !== ""){
                    if(ev.ville){
                        cityTest = ev.ville.includes(selectCities.value);
                    }
                }
                return (title.includes(term) || keywordMatch) && cityTest;
            });
            display(filtered);
        });

        selectCities.addEventListener("change", () => {
            if(selectCities.value !== "" && searchInput.value === ""){
                const actByCity = eventsArray.filter(ev => {
                    if(ev.ville){
                        return ev.ville.includes(selectCities.value);
                    }
                })
                display(actByCity);
            }
        })
        display(eventsArray);
    })
