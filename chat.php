<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];

// ✅ Validate match_id
if (!isset($_GET["match_id"]) || !is_numeric($_GET["match_id"])) {
    die("❌ Match ID is missing or invalid.");
}
$match_id = (int) $_GET["match_id"];

// ✅ Verify match belongs to current user
$stmt = $conn->prepare("
    SELECT m.*, i.user_id AS item_owner_id 
    FROM matches m
    JOIN items i ON m.lost_item_id = i.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();
$match = $result->fetch_assoc();
$stmt->close();

if (!$match || ($match['requester_id'] != $user_id && $match['item_owner_id'] != $user_id)) {
    die("⛔ You do not have permission to access this chat.");
}

// ✅ Handle new message POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["message"])) {
    $message = sanitize_input($_POST["message"]);

    if (!empty($message)) {
        $insert = $conn->prepare("INSERT INTO chat_messages (match_id, sender_id, message) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $match_id, $user_id, $message);
        $insert->execute();
        $insert->close();

        // ✅ Notify recipient
        $recipient_id = ($match['requester_id'] == $user_id) ? $match['item_owner_id'] : $match['requester_id'];
        add_notification($recipient_id, "📩 You have a new message in your match chat.", "chat.php?match_id=$match_id");
    }

    header("Location: chat.php?match_id=$match_id");
    exit;
}

// ✅ Fetch chat messages
$chat = $conn->prepare("
    SELECT m.message, m.created_at, u.name, u.id AS sender_id 
    FROM chat_messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.match_id = ? 
    ORDER BY m.created_at ASC
");
$chat->bind_param("i", $match_id);
$chat->execute();
$messages = $chat->get_result();

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-chat-dots"></i> Chat – Match #<?= htmlspecialchars($match_id) ?></h4>

        <!-- Report button -->
        <a href="report.php" 
           onclick="event.preventDefault(); 
                    if(confirm('Report this user?')) {
                        fetch('report.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'reported_user_id=<?= htmlspecialchars(($match['requester_id'] == $user_id) ? $match['item_owner_id'] : $match['requester_id']) ?>&match_id=<?= htmlspecialchars($match_id) ?>'
                        }).then(res => res.json()).then(data => alert(data.message));
                    }"
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-flag"></i> Report User
        </a>
    </div>

    <!-- Chat messages box -->
    <div class="card shadow-sm mb-3" style="max-height: 400px; overflow-y: auto;">
        <div class="card-body">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="mb-3 <?= $msg['sender_id'] == $user_id ? 'text-end' : '' ?>">
                        <div>
                            <strong><?= htmlspecialchars($msg['name']) ?></strong><br>
                            <span><?= nl2br(htmlspecialchars($msg['message'])) ?></span><br>
                            <small class="text-muted"><?= htmlspecialchars($msg['created_at']) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted text-center">💬 No messages yet. Start the conversation!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message input -->
    <form method="POST" class="d-flex gap-2" autocomplete="off">
        <input type="text" name="message" class="form-control" placeholder="Type your message…" required autofocus>
        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
    </form>

    <div class="mt-3">
        <a href="match_status.php" class="btn btn-link">&larr; Back to Match Requests</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>