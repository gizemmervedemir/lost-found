<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'lost_found_platform';

$conn = new mysqli($host, $user, $pass);

// Hata kontrolü
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Veritabanı oluştur
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✅ Veritabanı '$dbname' başarıyla oluşturuldu.</p>";
} else {
    echo "<p style='color:red;'>❌ Hata: " . $conn->error . "</p>";
}

$conn->close();
?>