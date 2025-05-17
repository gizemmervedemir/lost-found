<?php
include 'includes/db.php';

if (!isset($_GET['id'])) {
    die("Item ID missing.");
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT i.*, u.name FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found.");
}
?>

<h2><?php echo htmlspecialchars($item["title"]); ?></h2>
<p><strong>Description:</strong> <?php echo htmlspecialchars($item["description"]); ?></p>
<p><strong>Location:</strong> <?php echo $item["location"]; ?></p>
<p><strong>Lost Date:</strong> <?php echo $item["date_lost"]; ?></p>
<p><strong>Owner:</strong> <?php echo $item["name"]; ?></p>