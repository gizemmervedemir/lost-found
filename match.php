<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to request a match.'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['lost_item_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
    exit;
}

$lost_item_id = (int) $_POST['lost_item_id'];

// Check item ownership
$stmt = $conn->prepare("SELECT user_id FROM items WHERE id = ?");
$stmt->bind_param("i", $lost_item_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Item not found.'
    ]);
    exit;
}

$stmt->bind_result($item_owner_id);
$stmt->fetch();

if ($item_owner_id == $user_id) {
    echo json_encode([
        'status' => 'warning',
        'message' => 'You cannot request a match for your own item.'
    ]);
    exit;
}

// Check if a match request already exists for this item by this user
$check = $conn->prepare("SELECT status FROM matches WHERE lost_item_id = ? AND requester_id = ?");
$check->bind_param("ii", $lost_item_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->bind_result($existing_status);
    $check->fetch();

    switch ($existing_status) {
        case 'pending':
            $msg = 'You already requested a match for this item.';
            $status = 'warning';
            break;
        case 'approved':
            $msg = 'This item is already matched with you.';
            $status = 'success';
            break;
        case 'rejected':
            $msg = 'Your previous request for this item was rejected.';
            $status = 'info';
            break;
        default:
            $msg = 'You already have a request for this item.';
            $status = 'info';
    }

    echo json_encode([
        'status' => $status,
        'message' => $msg
    ]);
    exit;
}

// Create match request
$insert = $conn->prepare("INSERT INTO matches (lost_item_id, requester_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
$insert->bind_param("ii", $lost_item_id, $user_id);

if ($insert->execute()) {
    // Add notification
    $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $message = "📌 A new match request has been made for your item.";
    $notif->bind_param("is", $item_owner_id, $message);
    $notif->execute();

    log_event("MATCH REQUEST: User #$user_id requested match for Item #$lost_item_id");

    echo json_encode([
        'status' => 'success',
        'message' => '✅ Match request sent successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Something went wrong. Please try again later.'
    ]);
}