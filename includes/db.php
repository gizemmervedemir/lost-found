<?php
$host = 'localhost';
$db = 'lost_found_platform';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
session_start();
?>