let i = 0;

function display(eventsList){
    i=0;
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
        if(i>6){
            card.style.display="none";
        }
        results.appendChild(card);
        i++;
    });
    i = 10;
}

function showCards(){
    const switchInput = document.getElementById("activitySwitch");
    if (switchInput.checked) {
        const cards = Array.from(document.querySelectorAll(".card-prop"));
        const j = i + 6;
        while (i < j && i < cards.length) {
            cards[i].style.display = "";
            i++;
        }
    } else {
        const cards = Array.from(document.querySelectorAll(".card-event"));
        const j = i + 6;
        while (i < j && i < cards.length) {
            cards[i].style.display = "";
            i++;
        }
    }
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

function displayProps(props){
    i=0;
    console.log(props[0]);
    const results = document.getElementById("results");
    results.innerHTML="";
    props.forEach((ev, index) => {
        const div = document.createElement("div")
        const card = document.createElement("div");
        card.classList.add("card-prop");
        console.log(ev.id_prop);
        card.innerHTML = `
            <div style="width:150px; height:120px; background:#ccc;"><img src="showIcon.php?id=${ev.id_prop}" alt="illustration"/></div>
            <div class="infos">
                <h2 style="margin:0 0 10px 0;font-family: 'Playfair Display', serif;">
                    <a href="detail_evenement.php?uid=${ev.id_prop}" style="text-decoration:none; color:#333;">
                        ${ev.titre}
                    </a>
                </h2>

                <div>
                    <span style="background:#3498db; color:white; padding:3px 8px; border-radius:3px; font-size:0.8em;">
                        ${ev.ville}
                    </span>

                    <span class="date-badge">
                        Le ${new Date(ev.date).toLocaleDateString("fr-FR")}
                    </span>
                    <p style="text-align:left"><i>Proposée par ${ev.user_login}</i></p>
                </div>
            </div>
        `;
        if(i>6){
            card.style.display="none";
        }
        results.appendChild(card);
        i++;
    })
    i = 10;
}

function showProps(){
    const cards = Array.from(document.querySelectorAll(".card-prop"));
    const j = i+6;
    while(i<j && i<cards.length){
        cards[i].style.display="";
        i++;
    }
}

fetch("data/activitiesJson.php")
    .then(response => response.json())
    .then(data => {
        const eventsArray = Object.values(data);
        eventsArray.sort((a, b) => {
            let time1 = new Date(a.date);
            let time2 = new Date(b.date);
            if(time1 > time2) return 1;
            if(time1 < time2) return -1;
            return 0;
            //return (time1 > time2) ? 1 : (time1 < time2) ? -1 : 0;
        })
        const searchInput = document.getElementById("searchInput");
        const selectCities = document.getElementById("cities");
        const switchInput = document.getElementById("activitySwitch");

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
        switchInput.addEventListener("change", function () {
            const filter = document.getElementById("filter");
            if(this.checked){
                filter.style.display="none";
                displayProps(window.props)
            }
            else{
                filter.style.display="";
                display(eventsArray);
            }
        })
        display(eventsArray);
    })
