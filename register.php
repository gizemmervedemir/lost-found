<?php
include 'includes/db.php';
include 'includes/functions.php';

$error   = "";
$success = "";

// Only handle POST submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name             = sanitize_input($_POST["name"] ?? "");
    $email_input      = trim($_POST["email"] ?? "");
    $email            = filter_var($email_input, FILTER_VALIDATE_EMAIL);
    $password         = $_POST["password"] ?? "";
    $password_confirm = $_POST["password_confirm"] ?? "";
    $gender           = $_POST["gender"] ?? "male";
    $role             = "user";

    if (!$name) {
        $error = "❌ Please enter your full name.";
    } elseif (!$email) {
        $error = "❌ Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password must be at least 6 characters.";
    } elseif ($password !== $password_confirm) {
        $error = "❌ Passwords do not match.";
    } elseif (!in_array($gender, ['male', 'female'])) {
        $error = "❌ Please select a valid gender.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "❌ That email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role, gender) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $name, $email, $hashed, $role, $gender);
            if ($stmt->execute()) {
                log_event("NEW USER REGISTERED: {$email}");
                $success = "✅ Registration successful! Redirecting to login…";
                header("refresh:3;url=login.php");
            } else {
                $error = "❌ Something went wrong. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm rounded-4 border-0">
      <div class="card-body p-4">
        <h3 class="text-center mb-4"><i class="bi bi-person-plus"></i> Create Account</h3>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="Your full name"
                   required
                   value="<?= isset($name) ? htmlspecialchars($name) : "" ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="you@example.com"
                   required
                   value="<?= isset($email_input) ? htmlspecialchars($email_input) : "" ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Choose a password"
                   required
                   minlength="6">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password"
                   name="password_confirm"
                   class="form-control"
                   placeholder="Re-enter your password"
                   required
                   minlength="6">
          </div>

          <div class="mb-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select" required>
              <option value="male" <?= (isset($gender) && $gender === 'male') ? 'selected' : '' ?>>Male</option>
              <option value="female" <?= (isset($gender) && $gender === 'female') ? 'selected' : '' ?>>Female</option>
            </select>
          </div>

          <button type="submit" class="btn btn-success w-100">Sign Up</button>
        </form>

        <p class="mt-3 mb-0 text-center text-muted small">
          Already have an account?
          <a href="login.php">Login here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>