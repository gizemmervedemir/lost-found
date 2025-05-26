<?php
include 'includes/db.php';
include 'includes/functions.php';

$error = "";

// 🔒 Handle login request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $password = $_POST["password"];

    if (!$email || empty($password)) {
        $error = "❌ Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"]      = $user["role"];

            log_event("LOGIN SUCCESS: {$user['email']}");
            header("Location: index.php");
            exit;
        } else {
            $error = "❌ Invalid email or password.";
            log_event("LOGIN FAILED: $email");
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
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>

                <p class="mt-3 mb-0 text-center text-muted small">
                    Don't have an account?
                    <a href="register.php" class="text-decoration-none">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>