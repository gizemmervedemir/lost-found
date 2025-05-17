<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    log_event("UNAUTHORIZED LOG VIEW ATTEMPT");
    die("Access denied.");
}

$logfile = __DIR__ . '/logs/app.log';
$log_content = file_exists($logfile) ? file_get_contents($logfile) : 'No logs found.';

include 'includes/header.php';
?>

<h3 class="mb-4">System Logs</h3>

<div class="card border-secondary">
    <div class="card-body" style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 14px; white-space: pre-wrap;">
        <?= htmlspecialchars($log_content) ?>
    </div>
</div>

<p class="mt-3 text-end">
    <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
</p>

<?php include 'includes/footer.php'; ?>