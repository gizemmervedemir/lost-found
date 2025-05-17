<?php
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "
SELECT m.*, i.title, i.description, i.image_path, i.location, i.date_lost, i.owner_id
FROM matches m
JOIN items i ON m.lost_item_id = i.id
WHERE m.requester_id = ? OR i.owner_id = ?
ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<h3 class="mb-4">My Match Requests</h3>

<?php if ($result->num_rows > 0): ?>
    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-<?php
                    echo $row['status'] === 'approved' ? 'success' :
                         ($row['status'] === 'rejected' ? 'danger' : 'warning');
                ?>">
                    <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                        <img src="<?= $row['image_path'] ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                        <p><small><?= $row['location'] ?> — <?= $row['date_lost'] ?></small></p>
                        <span class="badge bg-<?=
                            $row['status'] === 'approved' ? 'success' :
                            ($row['status'] === 'rejected' ? 'danger' : 'secondary')
                        ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">You have no match requests yet.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>