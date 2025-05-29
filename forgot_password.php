<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ CSRF check
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        log_event("CSRF token mismatch on password reset");
        die("Security token mismatch. Possible CSRF attack.");
    }

    $email_input = trim($_POST["email"] ?? "");
    $email = filter_var($email_input, FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = "❌ Please enter a valid email address.";
    } else {
        // ✅ Always respond with same message (prevent email enumeration)
        $success = "✅ If this email is registered, a reset link will be sent.";

        // ✅ Prepare token logic
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiry

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // ✅ Save token to reset table (or users table)
            $user_id = $user["id"];

            // Make sure table "password_resets" exists with user_id, token, expires_at
            $conn->prepare("DELETE FROM password_resets WHERE user_id = ?")->bind_param("i", $user_id)->execute();

            $insert = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $insert->bind_param("iss", $user_id, $token, $expires);
            $insert->execute();

            // ✅ Send email (placeholder)
            $reset_link = "http://localhost/lost-found/reset_password.php?token=" . $token;
            // TODO: Use mail() or PHPMailer to send
            log_event("Password reset requested for {$email}");
        } else {
            log_event("Password reset requested for non-existent email: {$email}");
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="bi bi-key"></i> Forgot Password</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <!-- ✅ CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>

                    <p class="mt-3 text-center">
                        <a href="login.php">Back to Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>