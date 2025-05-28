<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// ✅ Admin kontrolü
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? '') !== 'admin') {
    log_event("ADMIN ACCESS DENIED: Unauthorized access attempt");
    die("Access denied.");
}

// 🗑 Eşya silme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_item_id"])) {
    $item_id = (int) $_POST["delete_item_id"];

    // Resim sil
    $stmt_img = $conn->prepare("SELECT image_path FROM items WHERE id = ?");
    if (!$stmt_img) {
        die("Prepare failed (image select): " . $conn->error);
    }
    $stmt_img->bind_param("i", $item_id);
    if (!$stmt_img->execute()) {
        die("Execute failed (image select): " . $stmt_img->error);
    }
    $res = $stmt_img->get_result();
    $item = $res->fetch_assoc();
    $stmt_img->close();

    if (!empty($item["image_path"]) && file_exists($item["image_path"])) {
        unlink($item["image_path"]);
    }

    // QR kodu sil
    $qr_path = "uploads/qr_item_$item_id.png";
    if (file_exists($qr_path)) {
        unlink($qr_path);
    }

    // Bu item ile ilişkili raporları (reports) sil
    $stmt_reports = $conn->prepare("
        DELETE r FROM reports r
        JOIN matches m ON r.match_id = m.id
        WHERE m.lost_item_id = ? OR m.found_item_id = ?
    ");
    if (!$stmt_reports) {
        die("Prepare failed (reports delete): " . $conn->error);
    }
    $stmt_reports->bind_param("ii", $item_id, $item_id);
    if (!$stmt_reports->execute()) {
        die("Execute failed (reports delete): " . $stmt_reports->error);
    }
    $stmt_reports->close();

    // Bu item ile ilişkili eşleşmeleri (matches) sil
    $stmt_match = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ? OR found_item_id = ?");
    if (!$stmt_match) {
        die("Prepare failed (matches delete): " . $conn->error);
    }
    $stmt_match->bind_param("ii", $item_id, $item_id);
    if (!$stmt_match->execute()) {
        die("Execute failed (matches delete): " . $stmt_match->error);
    }
    $stmt_match->close();

    // Item kaydını sil
    $stmt_item = $conn->prepare("DELETE FROM items WHERE id = ?");
    if (!$stmt_item) {
        die("Prepare failed (item delete): " . $conn->error);
    }
    $stmt_item->bind_param("i", $item_id);
    if (!$stmt_item->execute()) {
        die("Execute failed (item delete): " . $stmt_item->error);
    }
    $stmt_item->close();

    log_event("ADMIN DELETE ITEM: Item #$item_id deleted");

    header("Location: admin_panel.php?deleted=1");
    exit;
}

// 📊 Genel istatistikler çek
$total_users   = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$total_items   = $conn->query("SELECT COUNT(*) AS count FROM items")->fetch_assoc()['count'];
$total_matches = $conn->query("SELECT COUNT(*) AS count FROM matches")->fetch_assoc()['count'];

// 🆕 En son eklenen eşyaları çek
$result = $conn->query("SELECT * FROM items ORDER BY id DESC LIMIT 10");

include 'includes/header.php';
?>

<div class="container mt-4">
    <h3 class="mb-4"><i class="bi bi-shield-lock-fill"></i> Admin Dashboard</h3>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">✅ Item deleted successfully.</div>
    <?php endif; ?>

    <!-- 📊 Genel İstatistikler -->
    <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h2><?= $total_users ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Items</h5>
                    <h2><?= $total_items ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h5>Match Requests</h5>
                    <h2><?= $total_matches ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- 🆕 Son Eklenen Eşyalar -->
    <h5 class="mb-3 mt-5">🗃 Recently Added Items</h5>
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php
                        $imgPath = (!empty($row['image_path']) && file_exists($row['image_path']))
                            ? htmlspecialchars($row['image_path'])
                            : 'assets/no_image.png';
                        ?>
                        <img src="<?= $imgPath ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="Item Image">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars_decode($row['title']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars_decode($row['description'])) ?></p>
                            <p class="text-muted small"><?= htmlspecialchars($row['location']) ?> — <?= htmlspecialchars($row['date_lost']) ?></p>

                            <?php 
                            $qr_path = "uploads/qr_item_" . $row['id'] . ".png";
                            if (file_exists($qr_path)): ?>
                                <div class="text-center my-3">
                                    <img src="<?= $qr_path ?>" alt="QR Code" style="width: 120px; height: 120px;">
                                    <p class="small text-muted">QR for Item #<?= $row['id'] ?></p>
                                </div>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                <input type="hidden" name="delete_item_id" value="<?= $row["id"] ?>">
                                <button class="btn btn-outline-danger btn-sm w-100 mt-auto">🗑 Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No items found.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>