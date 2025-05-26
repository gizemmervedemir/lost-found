<?php
include 'includes/db.php';
include 'includes/functions.php';

// 🔒 Admin Access Control
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    log_event("ADMIN ACCESS DENIED: Unauthorized access attempt");
    die("Access denied.");
}

// 🗑 Handle Item Deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_item_id"])) {
    $item_id = (int) $_POST["delete_item_id"];

    // Delete image
    $stmt_img = $conn->prepare("SELECT image_path FROM items WHERE id = ?");
    $stmt_img->bind_param("i", $item_id);
    $stmt_img->execute();
    $res = $stmt_img->get_result();
    $item = $res->fetch_assoc();

    if (!empty($item["image_path"]) && file_exists($item["image_path"])) {
        unlink($item["image_path"]);
    }

    // Delete matches related to this item
    $stmt1 = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ?");
    $stmt1->bind_param("i", $item_id);
    $stmt1->execute();

    // Delete the item itself
    $stmt2 = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt2->bind_param("i", $item_id);
    $stmt2->execute();

    log_event("ADMIN DELETE ITEM: Item #$item_id deleted");

    header("Location: admin_panel.php?deleted=1");
    exit;
}

// 📊 Stats
$total_users   = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$total_items   = $conn->query("SELECT COUNT(*) AS count FROM items")->fetch_assoc()['count'];
$total_matches = $conn->query("SELECT COUNT(*) AS count FROM matches")->fetch_assoc()['count'];

// 🕓 Latest Items
$sql = "SELECT * FROM items ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h3 class="mb-4">Admin Dashboard</h3>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">✅ Item deleted successfully.</div>
    <?php endif; ?>

    <!-- Statistics Overview -->
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

    <!-- Latest Items -->
    <h5 class="mb-3 mt-5">Recently Added Items</h5>
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <p><small class="text-muted"><?= htmlspecialchars($row['location']) ?> – <?= htmlspecialchars($row['date_lost']) ?></small></p>

                            <?php $qr_path = "uploads/qr_item_" . $row['id'] . ".png"; ?>
                            <?php if (file_exists($qr_path)): ?>
                                <div class="text-center my-3">
                                    <img src="<?= $qr_path ?>" alt="QR Code" style="width: 120px; height: 120px;">
                                    <p class="small text-muted">QR for Item #<?= $row['id'] ?></p>
                                </div>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                <input type="hidden" name="delete_item_id" value="<?= $row["id"] ?>">
                                <button class="btn btn-outline-danger btn-sm w-100">🗑 Delete</button>
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