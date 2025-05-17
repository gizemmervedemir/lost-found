<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';
include 'includes/functions.php';


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


$sql = "SELECT * FROM items WHERE owner_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<h3 class="mb-4">My Lost Items</h3>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="row">
        <?php while ($item = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <p><small><?= htmlspecialchars($item['location']) ?> – <?= htmlspecialchars($item['date_lost']) ?></small></p>

                        <form method="POST" action="delete_my_item.php" onsubmit="return confirm('Are you sure you want to delete this item?');">
                            <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                            <button class="btn btn-outline-danger btn-sm w-100">🗑 Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">You haven’t added any lost items yet.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>