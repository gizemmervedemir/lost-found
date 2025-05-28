<?php
require_once 'includes/db.php';

$name = "Admin";
$email = "admin@lostfound.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Güvenli şifreleme
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Admin başarıyla oluşturuldu. Giriş için:</p>";
    echo "<p>Email: <strong>admin@lostfound.com</strong><br>Şifre: <strong>admin123</strong></p>";
} else {
    echo "<p style='color:red;'>❌ Hata: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>