<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

$sql = file_get_contents('migrate_notifications_column.sql');

if ($conn->multi_query($sql)) {
    echo "✅ Migration successful.";
} else {
    echo "❌ Error: " . $conn->error;
}