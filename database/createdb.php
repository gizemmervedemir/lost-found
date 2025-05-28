<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'lost_found_platform';

$conn = new mysqli($host, $user, $pass);

// Error checking
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✅ Database '$dbname' created successfully.</p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
}

$conn->close();
?>