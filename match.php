<?php
include 'includes/db.php';
include 'includes/functions.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    log_event("MATCH DENIED: Unauthorized access attempt");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["lost_item_id"])) {
    $lost_item_id = (int) $_POST["lost_item_id"];
    $requester_id = $_SESSION["user_id"];

    $check = $conn->prepare("SELECT id FROM matches WHERE lost_item_id = ? AND requester_id = ?");
    $check->bind_param("ii", $lost_item_id, $requester_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        log_event("MATCH DUPLICATE: User #$requester_id tried to request again for item #$lost_item_id");

        echo json_encode([
            "status" => "warning",
            "message" => "You already requested a match for this item."
        ]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO matches (lost_item_id, requester_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $lost_item_id, $requester_id);
    $stmt->execute();

    log_event("MATCH REQUEST: User #$requester_id sent match request for item #$lost_item_id");

    echo json_encode([
        "status" => "success",
        "message" => "Match request sent!"
    ]);
    exit;
}

http_response_code(400);
log_event("MATCH FAILED: Invalid request from User #" . ($_SESSION["user_id"] ?? 'UNKNOWN'));

echo json_encode([
    "status" => "error",
    "message" => "Invalid request."
]);