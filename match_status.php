<?php
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "
SELECT m.*, i.title, i.description, i.image_path, i.location, i.date_lost, i.user_id AS owner_id
FROM matches m
JOIN items i ON m.lost_item_id = i.id
WHERE m.requester_id = ? OR i.user_id = ?
ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-4"><i class="bi bi-link-45deg"></i> My Match Requests</h3>

        <?php if ($result->num_rows > 0): ?>
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
                                <p class="mb-2"><small class="text-muted">
                                    <?= htmlspecialchars($row['location']) ?> — <?= htmlspecialchars($row['date_lost']) ?>
                                </small></p>

                                <div class="mb-2">
                                    <span class="badge bg-<?=
                                        $row['status'] === 'approved' ? 'success' :
                                        ($row['status'] === 'rejected' ? 'danger' : 'secondary')
                                    ?>">
                                        <?= ucfirst($row["status"]) ?>
                                    </span>

                                    <?php if ($row['requester_id'] == $user_id): ?>
                                        <span class="badge bg-primary ms-2">You Requested</span>
                                    <?php elseif ($row['owner_id'] == $user_id): ?>
                                        <span class="badge bg-info text-dark ms-2">You Own This</span>
                                    <?php endif; ?>
                                </div>

                                <a href="chat.php?match_id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                    💬 Chat
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                You haven’t made or received any match requests yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>