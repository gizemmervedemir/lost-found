<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $current = $_POST["current_password"];
    $new = $_POST["new_password"];
    $confirm = $_POST["confirm_password"];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current, $result["password"])) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $user_id);
        $stmt->execute();
        $success = "✅ Password updated successfully.";
        log_event("PASSWORD CHANGED: User #$user_id updated their password");
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <h3 class="my-4"><i class="bi bi-shield-lock"></i> Change Password</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>