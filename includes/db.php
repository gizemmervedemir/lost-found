<?php
// Database connection settings
$host = 'localhost';
$db   = 'lost_found_platform';
$user = 'root';
$pass = '';

// Create MySQL connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection error
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding (UTF-8, supports Turkish characters)
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>