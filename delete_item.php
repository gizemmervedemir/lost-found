<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    log_event("DELETE BLOCKED: Unauthorized user tried to delete item.");
    die("Access denied.");
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = (int) $_POST['item_id'];

    // Get the image path of the related item
    $stmt = $conn->prepare("SELECT image_path FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();

    // 1. Delete matches
    $stmt1 = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ?");
    $stmt1->bind_param("i", $item_id);
    $stmt1->execute();

    // 2. Delete the item
    $stmt2 = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt2->bind_param("i", $item_id);
    $stmt2->execute();

    // 3. If image exists, delete it from the server
    if (!empty($item['image_path']) && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }

    // 4. Log the action
    log_event("ITEM DELETED: Admin #{$_SESSION['user_id']} deleted item #$item_id");

    // 5. Redirect
    header("Location: admin_panel.php?deleted=1");
    exit;
} else {
    http_response_code(400);
    echo "Invalid request.";
}