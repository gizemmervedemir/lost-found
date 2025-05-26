<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Logs system events to a log file
 *
 * @param string $message - The message to log
 * @return void
 */
function log_event($message) {
    $logFile = __DIR__ . '/../logs/app.log';  // Ensure this directory exists and is writable
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user = $_SESSION['user_name'] ?? 'GUEST';

    $entry = "[$timestamp] [$ip] [$user] $message\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Adds a notification for a specific user
 *
 * @param int $user_id - The recipient user's ID
 * @param string $message - The notification message
 * @return void
 */
function add_notification($user_id, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
}

/**
 * Sanitizes user input to prevent XSS
 *
 * @param string $data - The user input
 * @return string - The sanitized string
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generates and stores a CSRF token
 *
 * @return string - The CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token
 *
 * @param string $token - The token to validate
 * @return bool
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}