<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "
    SELECT * FROM items 
    WHERE id NOT IN (
        SELECT lost_item_id FROM matches WHERE status = 'approved'
    )
    ORDER BY date_lost DESC
    LIMIT 20
";

$result = $conn->query($sql);
if (!$result) {
    die("Data retrieval error: " . $conn->error);
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="card mb-4 border-0 shadow-sm rounded-4" style="background: linear-gradient(90deg, #e0f3ff, #f9fcff);">
        <div class="card-body d-flex align-items-center">
            <div class="me-4 text-primary fs-1"><i class="bi bi-search"></i></div>
            <div>
                <h4 class="card-title mb-1">Welcome to Lost & Found</h4>
                <p class="card-text mb-0 text-muted">Browse lost items, request a match, or add your own listing.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Lost Items</h3>
                <a href="add_item.php" class="btn btn-success">+ Add Lost Item</a>
            </div>

            <div class="mb-3">
                <input type="text" id="searchBox" class="form-control" placeholder="Search items...">
            </div>

            <div id="results">
                <div class="row" id="item-list">
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100" style="min-height: 460px;">
                                <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" style="height: 280px; object-fit: cover; border-bottom: 1px solid #ddd;">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars_decode($item['title']) ?></h5>
                                    <p class="card-text"><?= nl2br(htmlspecialchars_decode($item['description'])) ?></p>
                                    <p class="text-muted small"><?= htmlspecialchars_decode($item['location']) ?> — <?= htmlspecialchars($item['date_lost']) ?></p>
                                    <button class="btn btn-primary btn-sm w-100 mt-auto" onclick="sendMatchRequest(<?= $item['id'] ?>)" id="btn-<?= $item['id'] ?>">Request Match</button>
                                    <div id="msg-<?= $item['id'] ?>" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 border-info shadow-sm">
                <div class="card-header bg-info text-white"><strong>Did You Know?</strong></div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li>🔍 Over 2 billion items are lost yearly.</li>
                        <li>📱 Most commonly lost items: phones, wallets, keys.</li>
                        <li>⏱ Average recovery time: 3.5 days.</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-3 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">📬 Need Help?</div>
                <div class="card-body small">
                    <p>Contact support:</p>
                    <ul class="mb-0">
                        <li>📧 support@lostfound.com</li>
                        <li>📞 +1 555-LOST-NOW</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS: Search & Match -->
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
                    <div class="card h-100" style="min-height: 460px;">
                        ${item.image_path ? `<img src="${item.image_path}" class="card-img-top" style="height: 280px; object-fit: cover;">` : ''}
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="card-text">${item.description.replace(/\n/g, '<br>')}</p>
                            <p class="text-muted small">${item.location} — ${item.date_lost}</p>
                            <button class="btn btn-primary btn-sm w-100" onclick="sendMatchRequest(${item.id})" id="btn-${item.id}">Request Match</button>
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
    const btn = document.getElementById("btn-" + itemId);
    const msg = document.getElementById("msg-" + itemId);
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending...`;

    fetch('match.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'lost_item_id=' + itemId
    })
    .then(res => res.json())
    .then(data => {
        msg.innerHTML = `<div class="alert alert-${data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger')} p-2">${data.message}</div>`;
        btn.disabled = false;
        btn.innerHTML = originalText;
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => { msg.innerHTML = ""; }, 4000);
    })
    .catch(() => {
        msg.innerHTML = `<div class="alert alert-danger p-2">❌ An error occurred. Please try again.</div>`;
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>

<?php include 'includes/footer.php'; ?>