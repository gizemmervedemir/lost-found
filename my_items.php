<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, title, description, image_path, location, date_lost 
    FROM items 
    WHERE user_id = ? 
    ORDER BY id DESC
");

if (!$stmt) {
    die("❌ Sorgu hazırlanamadı: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

$items = [];

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $title, $description, $image_path, $location, $date_lost);
    while ($stmt->fetch()) {
        $items[] = [
            'id'          => $id,
            'title'       => $title,
            'description' => $description,
            'image_path'  => $image_path,
            'location'    => $location,
            'date_lost'   => $date_lost
        ];
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="mb-4"><i class="bi bi-collection"></i> My Lost Items</h3>

            <?php if (!empty($items)): ?>
                <div class="row">
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php
                                    $imgPath = (!empty($item['image_path']) && file_exists($item['image_path']))
                                        ? htmlspecialchars($item['image_path'])
                                        : 'assets/no_image.png';
                                ?>
                                <img src="<?= $imgPath ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="Item Image">

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars_decode($item['title']) ?></h5>
                                    <p class="card-text"><?= nl2br(htmlspecialchars_decode($item['description'])) ?></p>
                                    <p><small class="text-muted"><?= htmlspecialchars_decode($item['location']) ?> — <?= htmlspecialchars($item['date_lost']) ?></small></p>

                                    <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">✏️ Edit</a>

                                    <form method="POST" action="delete_my_item.php" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                        <button class="btn btn-outline-danger btn-sm w-100">🗑 Delete</button>
                                    </form>

                                    <?php 
                                        $qr_path = "uploads/qr_item_" . $item['id'] . ".png";
                                        if (file_exists($qr_path)): ?>
                                            <div class="text-center mt-3">
                                                <img src="<?= $qr_path ?>" alt="QR Code" class="img-fluid" style="width: 120px; height: 120px;">
                                                <p class="small text-muted">QR Code</p>
                                            </div>
                                    <?php else: ?>
                                        <a href="generate_qr.php?item_id=<?= $item['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mt-3">
                                           📎 Generate QR Code
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center mt-4">
                    You haven’t added any lost items yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>