<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$error = "";

// IF FORM IS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF TOKEN VALIDATION
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        log_event("Login CSRF token mismatch");
        die("Security token invalid. Possible CSRF attack.");
    }

    // SANITIZE INPUT
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user["password"])) {
                session_regenerate_id(true); // SESSION FIXATION PREVENTION
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                log_event("Successful login for $email");
                header("Location: index.php");
                exit;
            } else {
                $error = "❌ Incorrect password.";
                log_event("Failed login (wrong password) for $email");
            }
        } else {
            $error = "❌ User not found.";
            log_event("Failed login (user not found) for $email");
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Login</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <!-- CSRF TOKEN -->
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="mt-3 mb-0 text-center text-muted small">
                    Don't have an account yet?
                    <a href="register.php" class="text-decoration-none">Register</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>