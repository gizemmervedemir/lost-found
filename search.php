<?php
include 'includes/db.php';

header('Content-Type: application/json');

if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);

    $sql = "SELECT * FROM items WHERE title LIKE '%$q%' OR description LIKE '%$q%' LIMIT 10";
    $result = $conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
}