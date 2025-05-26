<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    die("Unauthorized.");
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["item_id"])) {
    $item_id = (int) $_POST["item_id"];

    // Check if item belongs to the user
    $check = $conn->prepare("SELECT image_path FROM items WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $item_id, $user_id);
    $check->execute();
    $result = $check->get_result();
    $item = $result->fetch_assoc();

    if ($item) {
        // Delete related match requests
        $stmt1 = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ?");
        $stmt1->bind_param("i", $item_id);
        $stmt1->execute();

        // Delete the item itself
        $stmt2 = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt2->bind_param("i", $item_id);
        $stmt2->execute();

        // Remove image file if exists
        if (!empty($item["image_path"]) && file_exists($item["image_path"])) {
            unlink($item["image_path"]);
        }

        log_event("USER DELETE ITEM: User #$user_id deleted item #$item_id");
    } else {
        log_event("UNAUTHORIZED DELETE ATTEMPT: User #$user_id tried to delete item #$item_id");
    }

    header("Location: my_items.php");
    exit;
} else {
    die("Invalid request.");
}