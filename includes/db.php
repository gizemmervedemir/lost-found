<?php
// Oturumu başlat (önceden başlamamışsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantı ayarları
$host = 'localhost';
$dbname = 'lost_found_platform';
$username = 'root';
$password = '';

// MySQL bağlantısı oluştur
$conn = new mysqli($host, $username, $password, $dbname);

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    error_log("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    die("Veritabanı bağlantısı başarısız. Lütfen daha sonra tekrar deneyin.");
}

// Karakter setini ayarla (Türkçe karakterler için)
if (!$conn->set_charset("utf8mb4")) {
    error_log("Karakter seti ayarlanamadı: " . $conn->error);
}
?>