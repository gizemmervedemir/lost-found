document.getElementById("searchBox").addEventListener("input", function () {
    let query = this.value.trim();

    if (query.length < 2) {
        document.getElementById("results").innerHTML = "";
        return;
    }

    fetch("search.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            let output = '<div class="row">';

            data.forEach(item => {
                output += `
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        ${item.image_path ? `<img src="${item.image_path}" class="card-img-top" style="height: 200px; object-fit: cover;">` : ''}
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="card-text">${item.description}</p>
                            <p><small class="text-muted">${item.location} – ${item.date_lost}</small></p>
                            <form method="POST" action="match.php">
                                <input type="hidden" name="lost_item_id" value="${item.id}">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Request Match</button>
                            </form>
                        </div>
                    </div>
                </div>`;
            });

            output += '</div>';
            document.getElementById("results").innerHTML = output;
        })
        .catch(err => {
            console.error("Search failed:", err);
            document.getElementById("results").innerHTML = "<div class='alert alert-danger'>Search failed. Please try again later.</div>";
        });
});