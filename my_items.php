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

// Fetch user's own items
$sql = "SELECT * FROM items WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-4"><i class="bi bi-collection"></i> My Lost Items</h3>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row">
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                <p class="mb-2">
                                    <small class="text-muted"><?= htmlspecialchars($item['location']) ?> — <?= htmlspecialchars($item['date_lost']) ?></small>
                                </p>

                                <!-- 🔄 Edit Button -->
                                <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                    ✏️ Edit
                                </a>

                                <!-- 🗑 Delete Button -->
                                <form method="POST" action="delete_my_item.php" onsubmit="return confirm('Are you sure you want to delete this item?');" class="mb-2">
                                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm w-100">🗑 Delete</button>
                                </form>

                                <!-- 📎 QR Code -->
                                <?php 
                                    $qr_path = "uploads/qr_item_" . $item['id'] . ".png";
                                    if (file_exists($qr_path)): ?>
                                        <div class="text-center">
                                            <img src="<?= $qr_path ?>" alt="QR Code" class="img-fluid" style="width: 120px; height: 120px;">
                                            <p class="small text-muted mt-1">QR Code</p>
                                        </div>
                                <?php else: ?>
                                    <a href="generate_qr.php?item_id=<?= $item['id'] ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-secondary btn-sm w-100">
                                       📎 Generate QR Code
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">You haven’t added any lost items yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>