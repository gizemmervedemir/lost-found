<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["match_id"])) {
    die("Missing match ID.");
}

$match_id = (int) $_GET["match_id"];

// ✅ Check if the user is involved in this match (as requester or owner)
$check = $conn->prepare("
    SELECT m.*, i.user_id AS item_owner_id 
    FROM matches m
    JOIN items i ON m.lost_item_id = i.id
    WHERE m.id = ?
");
$check->bind_param("i", $match_id);
$check->execute();
$res = $check->get_result();
$match = $res->fetch_assoc();

if (!$match) {
    die("Match not found.");
}

// ⛔ Access control
if ($match['requester_id'] != $user_id && $match['item_owner_id'] != $user_id) {
    die("Unauthorized access.");
}

// ✅ Handle new message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["message"])) {
    $message = sanitize_input($_POST["message"]);

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (match_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $match_id, $user_id, $message);
        $stmt->execute();

        log_event("CHAT MESSAGE: User #$user_id sent a message in Match #$match_id");

        // ➕ Notification to other user
        $recipient_id = ($match['requester_id'] == $user_id) ? $match['item_owner_id'] : $match['requester_id'];
        add_notification($recipient_id, "You received a new message in Match #$match_id.");
    }
}

// 💬 Retrieve message history
$messages = $conn->prepare("
    SELECT m.*, u.name 
    FROM chat_messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.match_id = ? 
    ORDER BY m.created_at ASC
");
$messages->bind_param("i", $match_id);
$messages->execute();
$result = $messages->get_result();

include 'includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h4>Chat – Match #<?= $match_id ?></h4>
        <a href="report.php?user_id=<?= $match['requester_id'] == $user_id ? $match['item_owner_id'] : $match['requester_id'] ?>"
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-flag"></i> Report User
        </a>
    </div>

    <div class="card mb-4" style="max-height: 400px; overflow-y: auto;">
        <div class="card-body">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="mb-3">
                    <strong><?= htmlspecialchars($row['name']) ?>:</strong><br>
                    <span><?= nl2br(htmlspecialchars($row['message'])) ?></span>
                    <div class="text-muted small"><?= $row['created_at'] ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <form method="POST" class="d-flex">
        <input type="text" name="message" class="form-control me-2" placeholder="Type your message..." required>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>

    <a href="match_status.php" class="btn btn-link mt-4">← Back to Match Requests</a>
</div>

<?php include 'includes/footer.php'; ?>