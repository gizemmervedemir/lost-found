<?php
include 'includes/db.php';
include 'includes/functions.php';

header('Content-Type: application/json');

// 🔐 Oturum kontrolü
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

// 🔎 Son 20 okunmamış bildirimi al
$sql = "
    SELECT id, message, created_at
    FROM notifications
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 20
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        "id" => (int)$row["id"],
        "message" => $row["message"],
        "created_at" => $row["created_at"]
    ];
}

// ✅ Bildirimleri okundu olarak işaretle (sadece varsa)
if (!empty($notifications)) {
    $markRead = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $markRead->bind_param("i", $user_id);
    $markRead->execute();
}

// ✅ JSON formatında dön
echo json_encode([
    "status" => "success",
    "count" => count($notifications),
    "notifications" => $notifications
]);