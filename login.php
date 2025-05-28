<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Lütfen e-posta ve şifre giriniz.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                header("Location: index.php"); // Ana sayfa
                exit;
            } else {
                $error = "❌ Şifre hatalı.";
            }
        } else {
            $error = "❌ Kullanıcı bulunamadı.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Giriş Yap</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                </form>

                <p class="mt-3 mb-0 text-center text-muted small">
                    Henüz hesabınız yok mu?
                    <a href="register.php" class="text-decoration-none">Kayıt Ol</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>