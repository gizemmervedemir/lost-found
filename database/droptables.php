<?php
// Display errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
include __DIR__ . '/../includes/db.php';

// Temporarily disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// DROP TABLE statements (order matters!)
$sql = "
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS users;
";

// Execute the query
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "<p style='color:green;'>✅ All tables have been successfully dropped.</p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$conn->close();
?>