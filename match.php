<?php
include 'includes/db.php';
include 'includes/functions.php';

header('Content-Type: application/json');

// 🔒 Require user to be logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    log_event("MATCH DENIED: Unauthorized access attempt");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["lost_item_id"])) {
    $lost_item_id = (int) $_POST["lost_item_id"];
    $requester_id = $_SESSION["user_id"];

    // 🔁 Check if user already made a match request for this item
    $check = $conn->prepare("SELECT id FROM matches WHERE lost_item_id = ? AND requester_id = ?");
    $check->bind_param("ii", $lost_item_id, $requester_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        log_event("MATCH DUPLICATE: User #$requester_id attempted to re-request item #$lost_item_id");
        echo json_encode([
            "status" => "warning",
            "message" => "You have already requested a match for this item."
        ]);
        exit;
    }

    // 🧠 Get item owner
    $owner_query = $conn->prepare("SELECT user_id FROM items WHERE id = ?");
    $owner_query->bind_param("i", $lost_item_id);
    $owner_query->execute();
    $owner_result = $owner_query->get_result();
    $owner_data = $owner_result->fetch_assoc();
    $owner_id = $owner_data['user_id'] ?? null;

    if (!$owner_id) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Item not found."
        ]);
        exit;
    }

    // ✅ Create new match request
    $chat_enabled = 1;
    $stmt = $conn->prepare("INSERT INTO matches (lost_item_id, requester_id, chat_enabled) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $lost_item_id, $requester_id, $chat_enabled);
    $stmt->execute();

    // 🔔 Add a notification for the item owner
    add_notification($owner_id, "You received a new match request for one of your items.");

    log_event("MATCH REQUEST: User #$requester_id requested a match for item #$lost_item_id");

    echo json_encode([
        "status" => "success",
        "message" => "Match request sent!"
    ]);
    exit;
}

// ❌ Invalid request fallback
http_response_code(400);
log_event("MATCH FAILED: Invalid request from User #" . ($_SESSION["user_id"] ?? 'UNKNOWN'));

echo json_encode([
    "status" => "error",
    "message" => "Invalid request."
]);