<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$reporter_id = $_SESSION['user_id'];
$reported_user_id = isset($_POST['reported_user_id']) ? (int)$_POST['reported_user_id'] : null;
$match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : null;

if (!$reported_user_id || !$match_id) {
    die("Invalid report data.");
}

$report_message = "User reported without message";

// Insert report into reports table (remove this if you don't want to keep report records)
$stmt = $conn->prepare("
    INSERT INTO reports (reporter_id, reported_user_id, match_id, message)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $reporter_id, $reported_user_id, $match_id, $report_message);
$stmt->execute();
$stmt->close();

// Add notification for admin
$admin_user_id = 1; // Admin user ID
$notif_msg = "User #$reported_user_id was reported by user #$reporter_id in match #$match_id";
$target_url = "chat.php?match_id=$match_id";

$stmt2 = $conn->prepare("INSERT INTO notifications (user_id, message, target_url) VALUES (?, ?, ?)");
$stmt2->bind_param("iss", $admin_user_id, $notif_msg, $target_url);
$stmt2->execute();
$stmt2->close();

// Do not redirect, optionally return JSON or just exit
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Report sent successfully.']);
exit;
?>