<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Match approve/reject process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_id'], $_POST['action'])) {
    $match_id = (int) $_POST['match_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $conn->prepare("UPDATE matches SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $match_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: match_status.php");
    exit;
}

// Fetch matches
$sql = "
SELECT 
    m.id AS match_id,
    m.status AS match_status,
    m.requester_id,
    i.id AS item_id,
    i.title AS item_title,
    i.description AS item_description,
    i.image_path AS item_image_path,
    i.location AS item_location,
    i.date_lost AS item_date_lost,
    i.user_id AS owner_id
FROM matches m
JOIN items i ON m.lost_item_id = i.id
WHERE m.requester_id = ? OR i.user_id = ?
ORDER BY m.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="mb-4"><i class="bi bi-link-45deg"></i> Match Requests</h3>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): 
                        $isOwner = ($row['owner_id'] == $user_id);
                        $isRequester = ($row['requester_id'] == $user_id);
                        $badgeClass = match ($row['match_status']) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default    => 'secondary'
                        };
                        $imgPath = (!empty($row['item_image_path']) && file_exists($row['item_image_path']))
                            ? htmlspecialchars($row['item_image_path'])
                            : 'assets/no_image.png';
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-<?= $badgeClass ?>">
                            <img src="<?= $imgPath ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="Item Image">

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars_decode($row['item_title']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars_decode($row['item_description'])) ?></p>
                                <p><small class="text-muted"><?= htmlspecialchars($row['item_location']) ?> — <?= htmlspecialchars($row['item_date_lost'] ?? '') ?></small></p>

                                <div class="mb-2">
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($row['match_status']) ?></span>
                                    <?php if ($isRequester): ?>
                                        <span class="badge bg-primary ms-2">You Requested</span>
                                    <?php endif; ?>
                                    <?php if ($isOwner): ?>
                                        <span class="badge bg-info text-dark ms-2">Your Item</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isOwner && $row['match_status'] === 'pending'): ?>
                                    <form method="POST" class="d-flex gap-2 mt-auto">
                                        <input type="hidden" name="match_id" value="<?= $row['match_id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm w-50">✅ Accept</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm w-50">❌ Reject</button>
                                    </form>
                                <?php endif; ?>

                                <a href="chat.php?match_id=<?= $row['match_id'] ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">💬 Chat</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No match requests yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$stmt->close();
include 'includes/footer.php';
?>