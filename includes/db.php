<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection settings
$host = 'localhost';
$dbname = 'lost_found_platform';
$username = 'root';
$password = '';

// Create MySQL connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection error
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

// Set character set (for Turkish characters support)
if (!$conn->set_charset("utf8mb4")) {
    error_log("Failed to set character set: " . $conn->error);
}
?>