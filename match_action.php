<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["match_id"], $_POST["action"])) {
    $match_id = (int) $_POST["match_id"];
    $action = $_POST["action"] === "approve" ? "approved" : "rejected";
    $user_id = $_SESSION["user_id"];

    // Check if the user owns the match
    $stmt = $conn->prepare("
        SELECT m.id 
        FROM matches m
        JOIN items i ON m.lost_item_id = i.id
        WHERE m.id = ? AND i.user_id = ?
    ");
    $stmt->bind_param("ii", $match_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $update = $conn->prepare("UPDATE matches SET status = ? WHERE id = ?");
        $update->bind_param("si", $action, $match_id);
        $update->execute();
        $_SESSION["flash"] = "Match status updated to '$action'.";
    } else {
        $_SESSION["flash"] = "Unauthorized or invalid match ID.";
    }
}

header("Location: match_status.php");
exit;