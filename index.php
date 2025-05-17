<?php
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$items = $conn->query("
    SELECT * FROM items
    WHERE id NOT IN (
        SELECT lost_item_id FROM matches WHERE status = 'approved'
    )
    ORDER BY date_lost DESC
    LIMIT 20
");
?>

<?php include 'includes/header.php'; ?>

<div class="card mb-4 border-0 shadow-sm rounded-4" style="background: linear-gradient(90deg, #e0f3ff, #f9fcff);">
    <div class="card-body d-flex align-items-center">
        <div class="me-4 text-primary fs-1">
            <i class="bi bi-search"></i>
        </div>
        <div>
            <h4 class="card-title mb-1">Welcome to Lost & Found</h4>
            <p class="card-text mb-0 text-muted">
                Report lost items, browse found listings, and request matches easily.
                Admins review match requests to help reconnect owners with their belongings.
            </p>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Lost Items</h2>
            <a href="add_item.php" class="btn btn-success">+ Add Lost Item</a>
        </div>

        <div class="mb-3">
            <input type="text" id="searchBox" class="form-control" placeholder="Search items...">
        </div>

        <div id="results">
            <div class="row" id="item-list">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100" style="min-height: 450px;">
                            <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                <img src="<?= $item['image_path'] ?>" class="card-img-top" style="height: 280px; object-fit: cover; border-bottom: 1px solid #ddd;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                <p><small><?= $item['location'] ?> – <?= $item['date_lost'] ?></small></p>
                                <button class="btn btn-primary btn-sm w-100" onclick="sendMatchRequest(<?= $item['id'] ?>)">Request Match</button>
                                <div id="msg-<?= $item['id'] ?>" class="mt-2 small"></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-white">
                <strong>Did You Know?</strong>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li>🌍 <strong>2+ billion</strong> items lost globally each year.</li>
                    <li>🔍 Only <strong>20%</strong> of lost items are recovered.</li>
                    <li>📱 Most lost: phones, wallets, keys.</li>
                    <li>⏱ Avg. time to recovery: <strong>3.5 days</strong>.</li>
                    <li>✈️ Airports find <strong>1.2M+</strong> items annually.</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3 border-secondary">
            <div class="card-header bg-secondary text-white">
                💡 Quick Tips
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li>📸 Add a photo for better visibility.</li>
                    <li>🕓 Include the exact date lost.</li>
                    <li>🧭 Mention location clearly.</li>
                    <li>📦 Use clear titles like "Black Backpack".</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3 border-success">
            <div class="card-body text-success small">
                ✅ <strong>Sena</strong> recovered her lost keys in 2 days using this system.
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById("searchBox").addEventListener("input", function () {
    let query = this.value;

    if (query.length < 2) {
        document.getElementById("item-list").style.display = "flex";
        document.getElementById("results").innerHTML = "";
        return;
    }

    fetch("search.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            let html = '<div class="row">';
            data.forEach(item => {
                html += `
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="min-height: 450px;">
                        ${item.image_path ? `<img src="${item.image_path}" class="card-img-top" style="height: 280px; object-fit: cover; border-bottom: 1px solid #ddd;">` : ''}
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="card-text">${item.description}</p>
                            <p><small>${item.location} – ${item.date_lost}</small></p>
                            <button class="btn btn-primary btn-sm w-100" onclick="sendMatchRequest(${item.id})">Request Match</button>
                            <div id="msg-${item.id}" class="mt-2 small"></div>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            document.getElementById("results").innerHTML = html;
            document.getElementById("item-list").style.display = "none";
        });
});

function sendMatchRequest(itemId) {
    fetch('match.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'lost_item_id=' + itemId
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.getElementById('msg-' + itemId);
        msgDiv.innerHTML = `<div class="alert alert-${data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger')} p-2">${data.message}</div>`;
        setTimeout(() => {
            msgDiv.innerHTML = "";
        }, 3000);
    });
}
</script>

<?php include 'includes/footer.php'; ?>