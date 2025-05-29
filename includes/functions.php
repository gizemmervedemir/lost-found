<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Logs system events to a log file (with IP and user info)
 *
 * @param string $message - The message to log
 * @return void
 */
function log_event($message) {
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/app.log';

    // Create logs directory if it does not exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user = $_SESSION['user_name'] ?? ($_SESSION['user_id'] ?? 'GUEST');

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

    if (!$conn || !$conn->ping()) {
        log_event("add_notification error: No valid DB connection.");
        return;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");

    if ($stmt === false) {
        log_event("add_notification prepare failed: " . $conn->error);
        return;
    }

    $stmt->bind_param("is", $user_id, $message);
    if (!$stmt->execute()) {
        log_event("add_notification execute failed: " . $stmt->error);
    }

    $stmt->close();
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
 * Generates and stores a CSRF token in session
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
 * Validates a submitted CSRF token against session
 *
 * @param string $token - The token to validate
 * @return bool - True if valid, false otherwise
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Checks if the user is currently logged in
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the currently logged-in user is an admin
 *
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Validates an email using regex (extra check)
 *
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
           preg_match('/^[^@]+@[^@]+\.[^@]+$/', $email);
}