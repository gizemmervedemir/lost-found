<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$success = "";
$error = "";

// 🧠 Process profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ CSRF check
    if (!validate_csrf_token($_POST["csrf_token"] ?? '')) {
        log_event("CSRF token mismatch on profile update (User #$user_id)");
        die("Security token invalid. Possible CSRF attack.");
    }

    $new_name  = sanitize_input($_POST["name"]);
    $new_email_input = trim($_POST["email"]);
    $new_email = filter_var($new_email_input, FILTER_VALIDATE_EMAIL);

    if (!$new_name || !$new_email) {
        $error = "❌ Please fill in all required fields with valid data.";
    } else {
        // Check if email is used by someone else
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $new_email, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "❌ This email is already used by another account.";
        } else {
            $update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $new_name, $new_email, $user_id);
            if ($update->execute()) {
                $_SESSION['user_name'] = $new_name;
                log_event("PROFILE UPDATED: User #$user_id updated their profile.");
                $success = "✅ Profile updated successfully.";
            } else {
                $error = "❌ Failed to update profile. Please try again.";
            }
        }
        $check->close();
    }
}

// 🔎 Fetch current user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-pencil-square"></i> Edit Profile</h3>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>

                <div class="mt-3 text-end">
                    <a href="profile.php" class="btn btn-link">← Back to Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>