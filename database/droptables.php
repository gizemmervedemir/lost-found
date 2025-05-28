<?php
// Hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısını dahil et
include __DIR__ . '/../includes/db.php';

// Foreign key kontrollerini geçici olarak kapat
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// DROP TABLE işlemleri (sıralama önemli!)
$sql = "
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS users;
";

// Sorguyu çalıştır
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "<p style='color:green;'>✅ Tüm tablolar başarıyla silindi.</p>";
} else {
    echo "<p style='color:red;'>❌ Hata: " . $conn->error . "</p>";
}

// Foreign key kontrollerini tekrar aç
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$conn->close();
?>