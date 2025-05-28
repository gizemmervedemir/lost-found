<?php
require_once 'includes/db.php';

$name = "Admin";
$email = "admin@lostfound.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Secure password hashing
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Admin created successfully. Login credentials:</p>";
    echo "<p>Email: <strong>admin@lostfound.com</strong><br>Password: <strong>admin123</strong></p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>