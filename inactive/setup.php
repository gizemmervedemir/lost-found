<?php
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "lost_found";
$sqlFile  = __DIR__ . "/database/lost_found_platform.sql";

// Connect to MySQL
$conn = new mysqli($host, $username, $password);

// Connection check
if ($conn->connect_error) {
    die("❌ Failed to connect to MySQL: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
$conn->select_db($dbname);

// Load and execute SQL file
$commands = file_get_contents($sqlFile);

if ($conn->multi_query($commands)) {
    do {
        $conn->store_result();
    } while ($conn->more_results() && $conn->next_result());

    echo "✅ Database setup completed successfully.";
} else {
    echo "❌ Error executing SQL file: " . $conn->error;
}

$conn->close();
?>