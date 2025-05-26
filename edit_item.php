<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    die("Missing item ID.");
}

$item_id = (int) $_GET["id"];

// Fetch item to edit
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found or access denied.");
}

// Handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);
    $date_lost = $_POST["date_lost"];

    if ($title && $description && $location && $date_lost) {
        $update = $conn->prepare("UPDATE items SET title = ?, description = ?, location = ?, date_lost = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("ssssii", $title, $description, $location, $date_lost, $item_id, $user_id);
        $update->execute();

        log_event("ITEM UPDATED: User #$user_id updated item #$item_id");

        header("Location: my_items.php?updated=1");
        exit;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h3 class="my-4"><i class="bi bi-pencil-square"></i> Edit Item</h3>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Item Title</label>
            <input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($item['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Item Description</label>
            <textarea class="form-control" name="description" id="description" required><?= htmlspecialchars($item['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location Lost</label>
            <input type="text" class="form-control" name="location" id="location" value="<?= htmlspecialchars($item['location']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="date_lost" class="form-label">Date Lost</label>
            <input type="date" class="form-control" name="date_lost" id="date_lost" value="<?= htmlspecialchars($item['date_lost']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Item</button>
        <a href="my_items.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>