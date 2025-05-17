<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    log_event("ADMIN ACCESS DENIED: Unauthorized user attempted to access admin panel.");
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["match_id"], $_POST["action"])) {
        $match_id = (int) $_POST["match_id"];
        $action = $_POST["action"] === "approve" ? "approved" : "rejected";

        $stmt = $conn->prepare("UPDATE matches SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $match_id);
        $stmt->execute();

        log_event("ADMIN $action MATCH: Match #$match_id");
    }

    if (isset($_POST["delete_item_id"])) {
        $item_id = (int) $_POST["delete_item_id"];

        $stmt1 = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ?");
        $stmt1->bind_param("i", $item_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt2->bind_param("i", $item_id);
        $stmt2->execute();

        log_event("ADMIN DELETE ITEM: Item #$item_id deleted");

        header("Location: admin_panel.php?deleted=1");
        exit;
    }
}

$sql = "
SELECT m.*, u.name AS requester_name, i.title, i.description, i.image_path, i.id AS item_id
FROM matches m
JOIN users u ON m.requester_id = u.id
JOIN items i ON m.lost_item_id = i.id
ORDER BY m.created_at DESC
";

$result = $conn->query($sql);

include 'includes/header.php';
?>

<h3 class="mb-4">Admin Panel – Match Approvals & Item Management</h3>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Item successfully deleted.</div>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-<?=
                    $row['status'] === 'approved' ? 'success' :
                    ($row['status'] === 'rejected' ? 'danger' : 'warning');
                ?>">
                    <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                        <img src="<?= htmlspecialchars($row['image_path']) ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                        <p><strong>Requester:</strong> <?= htmlspecialchars($row['requester_name']) ?></p>
                        <p>Status:
                            <span class="badge bg-<?=
                                $row["status"] === "approved" ? "success" :
                                ($row["status"] === "rejected" ? "danger" : "secondary")
                            ?>">
                                <?= ucfirst($row["status"]) ?>
                            </span>
                        </p>

                        <?php if ($row["status"] === "pending"): ?>
                            <form method="POST" class="d-flex gap-2 mt-2">
                                <input type="hidden" name="match_id" value="<?= $row["id"] ?>">
                                <button name="action" value="approve" class="btn btn-outline-success btn-sm">Approve</button>
                                <button name="action" value="reject" class="btn btn-outline-danger btn-sm">Reject</button>
                            </form>
                        <?php endif; ?>

                
                        <form method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to delete this item?');">
                            <input type="hidden" name="delete_item_id" value="<?= $row["item_id"] ?>">
                            <button class="btn btn-outline-danger btn-sm w-100">🗑 Delete Item</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No match requests found.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>