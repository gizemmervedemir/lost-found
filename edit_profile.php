<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$success = "";
$error = "";

// 🧠 Update process
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = sanitize_input($_POST["name"]);
    $new_email = sanitize_input($_POST["email"]);

    // Check if email is already used by someone else
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $new_email, $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "❌ This email is already used by another account.";
    } else {
        $update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update->bind_param("ssi", $new_name, $new_email, $user_id);
        $update->execute();

        $_SESSION['user_name'] = $new_name;
        log_event("PROFILE UPDATED: User #$user_id updated their profile.");

        $success = "✅ Profile updated successfully.";
    }
}

// 🔎 Fetch current user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-pencil-square"></i> Edit Profile</h3>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
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