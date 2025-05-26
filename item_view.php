<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

// ✅ ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger text-center mt-4'>❌ Invalid item ID.</div>";
    include 'includes/footer.php';
    exit;
}

$id = (int) $_GET['id'];

// ✅ İlan detaylarını al
$stmt = $conn->prepare("SELECT i.*, u.name AS owner_name FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "<div class='alert alert-warning text-center mt-4'>⚠️ Item not found.</div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="container my-4">
    <div class="card shadow-sm border-0 rounded-4">
        <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Lost Item Image" class="card-img-top" style="max-height: 400px; object-fit: cover;">
        <?php endif; ?>

        <div class="card-body">
            <h3 class="card-title mb-3"><?= htmlspecialchars($item['title']) ?></h3>

            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($item['description'])) ?></p>
            <p><strong>Last Seen Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
            <p><strong>Date Lost:</strong> <?= htmlspecialchars($item['date_lost']) ?></p>
            <p><strong>Reported By:</strong> <?= htmlspecialchars($item['owner_name']) ?></p>

            <?php
            // QR dosyası varsa göster
            $qr_path = "uploads/qr_item_" . $item['id'] . ".png";
            if (file_exists($qr_path)): ?>
                <div class="text-center my-4">
                    <img src="<?= $qr_path ?>" alt="QR Code" style="width: 120px; height: 120px;">
                    <p class="text-muted small">Scan this QR to view this item</p>
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-outline-secondary mt-3">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>