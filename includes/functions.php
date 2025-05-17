<?php
function log_event($message) {
    $logfile = __DIR__ . '/../logs/app.log';
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user = $_SESSION['user_name'] ?? 'GUEST';

    $entry = "[$timestamp] [$ip] [$user] $message\n";
    file_put_contents($logfile, $entry, FILE_APPEND);
}