<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$sender_id   = $_SESSION['user_id'];
$receiver_id = (int) $_POST['receiver_id'];
$message     = sanitize_input($_POST['message']);

if ($message) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();

    echo "Message sent.";
} else {
    echo "Message cannot be empty.";
}
?>