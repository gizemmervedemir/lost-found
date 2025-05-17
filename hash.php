<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["plain"])) {
    $plain = $_POST["plain"];
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
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="card-title mb-3">Password Hash Generator</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Enter Plain Password</label>
                    <input type="text" name="plain" class="form-control" required>
                </div>
                <button class="btn btn-primary">Generate Hash</button>
            </form>

            <?php if (isset($hash)): ?>
                <div class="alert alert-success mt-4">
                    <strong>Generated Hash:</strong><br>
                    <code><?= htmlspecialchars($hash) ?></code>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>