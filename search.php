<?php
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
    echo json_encode([]);
    exit;
}

$q = '%' . trim($_GET['q']) . '%';

$stmt = $conn->prepare("SELECT id, title, description, image_path, location, date_lost FROM items WHERE title LIKE ? OR description LIKE ? ORDER BY date_lost DESC LIMIT 10");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$result = $stmt->get_result();

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'image_path' => $row['image_path'],
        'location' => $row['location'],
        'date_lost' => $row['date_lost']
    ];
}

echo json_encode($items);