<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Authorization check
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "⛔ Unauthorized";
    exit;
}

$sender_id = (int) $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$message = sanitize_input($_POST['message'] ?? '');

// Input validation
if ($receiver_id <= 0 || empty($message)) {
    http_response_code(400);
    echo "❌ Invalid input.";
    exit;
}

// Secure insert
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();


    add_notification($receiver_id, "📩 New message from user #$sender_id", "chat.php");

    echo "✅ Message sent successfully.";
} else {
    http_response_code(500);
    echo "❌ Server error.";
}
?>