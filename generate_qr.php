<?php

// Show errors but hide deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) session_start();

// Include QR library
require_once 'qr/qrlib.php';

// Expect item_id from user
if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
    die("⚠️ Error: Item ID is missing or invalid.");
}

$item_id = (int) $_GET['item_id'];

// Path where QR code image will be saved
$savePath = "uploads/qr_item_$item_id.png";

// URL to be encoded in QR code
$url = "http://localhost/lost-found/item_view.php?id=$item_id";

// Generate QR code
QRcode::png($url, $savePath, QR_ECLEVEL_L, 4);

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code for Item #<?= $item_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container text-center mt-5">
        <h3 class="mb-4">QR Code for Item #<?= $item_id ?></h3>

        <div class="card shadow-sm p-4">
            <img src="<?= $savePath ?>" class="img-fluid mb-3" style="max-width: 300px;">
            <br>
            <a href="<?= $savePath ?>" download class="btn btn-primary">
                ⬇️ Download QR Code
            </a>
        </div>

        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">← Back to Home</a>
        </div>
    </div>
</body>
</html>