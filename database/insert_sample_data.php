<?php
include '../includes/1connect.php';

$password = password_hash("123456", PASSWORD_DEFAULT);

$sql = "
INSERT INTO users (name, email, password) VALUES
('Ali Veli', 'ali@example.com', '$password'),
('Ayşe Fatma', 'ayse@example.com', '$password');

INSERT INTO items (user_id, title, description, location, date_lost, image_path) VALUES
(1, 'Wallet', 'Brown leather wallet', 'Kadıköy', '2024-05-01', 'uploads/wallet.jpg'),
(2, 'Key', 'Car key', 'Beşiktaş', '2024-05-05', 'uploads/key.jpg');

INSERT INTO matches (requester_id, lost_item_id, found_item_id, status) VALUES
(2, 1, 2, 'pending');
";

if ($conn->multi_query($sql)) {
    echo "Sample data added successfully.";
} else {
    echo "An error occurred: " . $conn->error;
}

$conn->close();
?>