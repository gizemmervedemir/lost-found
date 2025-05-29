<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// ✅ Validate input length
if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
    echo json_encode([]);
    exit;
}

$search_term = sanitize_input($_GET['q']); // XSS protection
$like_query = '%' . $search_term . '%';

// ✅ Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("
    SELECT id, title, description, image_path, location, date_lost 
    FROM items 
    WHERE title LIKE ? OR description LIKE ? 
    ORDER BY date_lost DESC 
    LIMIT 10
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => '❌ Database query error.']);
    exit;
}

$stmt->bind_param("ss", $like_query, $like_query);
$stmt->execute();
$result = $stmt->get_result();

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id'          => (int) $row['id'],
        'title'       => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
        'description' => htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'),
        'image_path'  => htmlspecialchars($row['image_path'], ENT_QUOTES, 'UTF-8'),
        'location'    => htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8'),
        'date_lost'   => $row['date_lost']
    ];
}

echo json_encode($items);