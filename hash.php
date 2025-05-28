<?php
// If the form is submitted and the "plain" input exists
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["plain"])) {
    $plain = trim($_POST["plain"]);
    $hash = password_hash($plain, PASSWORD_DEFAULT);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Hasher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-center">🔐 Password Hash Generator</h4>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter Plain Password</label>
                            <input type="text" name="plain" class="form-control" placeholder="e.g. 123456" required>
                        </div>
                        <button class="btn btn-primary w-100">Generate Hash</button>
                    </form>

                    <?php if (isset($hash)): ?>
                        <div class="alert alert-success mt-4">
                            <strong>Generated Hash:</strong><br>
                            <code class="d-block mt-2"><?= htmlspecialchars($hash) ?></code>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-secondary btn-sm">← Back to Home</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>