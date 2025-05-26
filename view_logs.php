<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    log_event("UNAUTHORIZED LOG VIEW ATTEMPT");
    die("Access denied.");
}

$logFile = __DIR__ . '/logs/app.log';

// Clear logs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_logs'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        log_event("LOG CLEARED by admin");
        header("Location: view_logs.php?cleared=1");
        exit;
    }
}

// Read logs
$logContent = "No logs found.";
if (file_exists($logFile)) {
    $lines = file($logFile);
    $filtered = [];

    // Get filters
    $filterText = strtolower(trim($_GET['filter'] ?? ''));
    $filterUser = strtolower(trim($_GET['user'] ?? ''));
    $startDate  = $_GET['start_date'] ?? '';
    $endDate    = $_GET['end_date'] ?? '';

    foreach ($lines as $line) {
        $keep = true;

        // Filter by keyword
        if ($filterText && stripos($line, $filterText) === false) {
            $keep = false;
        }

        // Filter by user
        if ($filterUser && stripos($line, "[$filterUser]") === false) {
            $keep = false;
        }

        // Filter by date range
        if ($startDate || $endDate) {
            preg_match('/\[(.*?)\]/', $line, $matches);
            if (!empty($matches[1])) {
                $logDate = date_create_from_format('Y-m-d H:i:s', $matches[1]);
                if ($logDate) {
                    if ($startDate && $logDate < date_create($startDate)) $keep = false;
                    if ($endDate && $logDate > date_create($endDate . ' 23:59:59')) $keep = false;
                }
            }
        }

        if ($keep) $filtered[] = $line;
    }

    $logContent = count($filtered) ? implode("", $filtered) : "No matching logs found.";
}

include 'includes/header.php';
?>

<div class="container">
    <h3 class="mb-4"><i class="bi bi-file-earmark-text"></i> System Logs</h3>

    <?php if (isset($_GET['cleared'])): ?>
        <div class="alert alert-success">✅ Logs cleared successfully.</div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="filter" class="form-control" placeholder="Search keyword..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="user" class="form-control" placeholder="Username..." value="<?= htmlspecialchars($_GET['user'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
        </div>
        <div class="col-md-3 d-flex">
            <button class="btn btn-outline-secondary me-2 w-100">Filter</button>
            <a href="view_logs.php" class="btn btn-outline-dark">Reset</a>
        </div>
    </form>

    <!-- Clear Logs -->
    <form method="POST" class="mb-3">
        <button type="submit" name="clear_logs" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to clear all logs?');">
            <i class="bi bi-trash"></i> Clear Logs
        </button>
    </form>

    <!-- Log Display -->
    <div class="card border-secondary shadow-sm">
        <div class="card-body" style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 14px; white-space: pre-wrap; background-color: #f8f9fa;">
            <?= htmlspecialchars($logContent) ?>
        </div>
    </div>

    <p class="mt-3 text-end">
        <a href="admin_panel.php" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Admin Panel
        </a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>