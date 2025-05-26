<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$reporter_id = $_SESSION['user_id'];
$reported_user_id = isset($_POST['reported_user_id']) ? (int)$_POST['reported_user_id'] : null;
$match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : null;
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    die("Report message is required.");
}

// Optional: Validate that match or user exists
if (!$reported_user_id && !$match_id) {
    die("Invalid report. Please select a target.");
}

$stmt = $conn->prepare("
    INSERT INTO reports (reporter_id, reported_user_id, match_id, message)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $reporter_id, $reported_user_id, $match_id, $message);
$stmt->execute();

log_event("REPORT SUBMITTED: User #$reporter_id reported User #$reported_user_id or Match #$match_id");

header("Location: match_status.php?report=success");
exit;
?>