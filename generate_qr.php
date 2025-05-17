<?php

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

include 'qr/qrlib.php';

if (!isset($_GET['item_id'])) {
    die("Item ID missing.");
}

$item_id = (int)$_GET['item_id'];
$savePath = "uploads/qr_item_$item_id.png";
$url = "http://localhost/lost-found/item_view.php?id=$item_id";

QRcode::png($url, $savePath, QR_ECLEVEL_L, 4);

echo "<h3>QR Code for Item #$item_id</h3>";
echo "<img src='$savePath'><br>";
echo "<a href='$savePath' download>Download QR Code</a>";