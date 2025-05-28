<?php
include '../includes/db.php'; // db bağlantısı

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        gender ENUM('male', 'female', 'other') DEFAULT 'male',
        profile_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255),
        description TEXT,
        location VARCHAR(255),
        date_lost DATE,
        image_path VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        requester_id INT NOT NULL,
        lost_item_id INT NOT NULL,
        found_item_id INT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (lost_item_id) REFERENCES items(id) ON DELETE CASCADE,
        FOREIGN KEY (found_item_id) REFERENCES items(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        target_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        match_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reporter_id INT NOT NULL,
        reported_user_id INT NOT NULL,
        match_id INT NULL DEFAULT NULL,
        message TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
    )"
];

$success = true;

foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo "<p style='color:red;'>❌ Hata: " . htmlspecialchars($conn->error) . "</p>";
        $success = false;
    } else {
        echo "<p style='color:green;'>✅ Sorgu başarıyla çalıştı.</p>";
    }
}

// ALTER TABLE: Eğer reports tablosunda 'reason' sütunu varsa kaldır
$res = $conn->query("SHOW COLUMNS FROM reports LIKE 'reason'");
if ($res && $res->num_rows > 0) {
    if ($conn->query("ALTER TABLE reports DROP COLUMN reason")) {
        echo "<p style='color:green;'>✅ 'reason' sütunu kaldırıldı.</p>";
    } else {
        echo "<p style='color:red;'>❌ 'reason' sütunu kaldırılamadı: " . htmlspecialchars($conn->error) . "</p>";
        $success = false;
    }
} else {
    echo "<p style='color:gray;'>ℹ️ 'reason' sütunu zaten mevcut değil.</p>";
}

$conn->close();
?>