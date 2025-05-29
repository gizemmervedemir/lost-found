<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable MySQLi strict error reporting (safer debugging during development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection settings (consider moving these to a .env file in production)
$host     = 'localhost';
$dbname   = 'lost_found_platform';
$username = 'root';
$password = '';

// Create MySQL connection with error handling
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Set character set to support Turkish and emojis
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Failed to set character set: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("❌ DB Connection Error: " . $e->getMessage());
    die("⚠️ We're experiencing technical issues. Please try again later.");
}