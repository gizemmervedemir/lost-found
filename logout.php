<?php
session_start();
require_once 'includes/functions.php';

// Capture user info before destroying session
$user_id = $_SESSION['user_id'] ?? 'UNKNOWN';
$user_name = $_SESSION['user_name'] ?? 'UNKNOWN';

// Log logout event
log_event("LOGOUT: User #$user_id ($user_name) logged out");

// Clear session data and destroy session
session_unset();
session_destroy();

// Redirect to login
header("Location: login.php");
exit;