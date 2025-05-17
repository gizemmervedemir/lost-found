document.getElementById("searchBox").addEventListener("input", function () {
    let query = this.value;

    if (query.length < 2) {
        document.getElementById("results").innerHTML = "";
        return;
    }

    fetch("search.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            let output = "";
            data.forEach(item => {
                output += `<div>
                    <strong>${item.title}</strong><br>
                    <em>${item.location}</em> - ${item.date_lost}<br>
                    <p>${item.description}</p>

                    <form method="POST" action="match.php" style="margin-top:10px;">
                        <input type="hidden" name="lost_item_id" value="${item.id}">
                        <button type="submit">Request Match</button>
                    </form>

                    <hr>
                </div>`;
            });

            document.getElementById("results").innerHTML = output;
        });
});