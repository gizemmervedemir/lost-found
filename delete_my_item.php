<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    die("Unauthorized.");
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["item_id"])) {
    $item_id = (int) $_POST["item_id"];

    $check = $conn->prepare("SELECT id FROM items WHERE id = ? AND owner_id = ?");
    $check->bind_param("ii", $item_id, $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {

        $stmt1 = $conn->prepare("DELETE FROM matches WHERE lost_item_id = ?");
        $stmt1->bind_param("i", $item_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt2->bind_param("i", $item_id);
        $stmt2->execute();

        log_event("USER DELETE ITEM: User #$user_id deleted item #$item_id");
    } else {
        log_event("UNAUTHORIZED DELETE ATTEMPT: User #$user_id tried to delete item #$item_id");
    }

    header("Location: my_items.php");
    exit;
}
?>