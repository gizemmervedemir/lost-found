<?php
include 'includes/db.php';
include 'includes/functions.php';

session_start();

// Admin privilege check only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    log_event("UNAUTHORIZED REPORT VIEW ATTEMPT");
    die("Access denied.");
}

// Report deletion process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report_id'])) {
    $report_id = (int) $_POST['delete_report_id'];

    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $stmt->close();

        log_event("ADMIN DELETED REPORT: ID #$report_id");
        header("Location: view_reports.php?deleted=1");
        exit;
    } else {
        die("Database error: " . $conn->error);
    }
}

// Fetch reports (message removed)
$sql = "
SELECT r.*, 
       u.name AS reporter_name, 
       ru.name AS reported_name
FROM reports r
JOIN users u ON r.reporter_id = u.id
JOIN users ru ON r.reported_user_id = ru.id
ORDER BY r.created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Database query error: " . $conn->error);
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h3 class="mb-4"><i class="bi bi-exclamation-triangle"></i> User Reports</h3>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">✅ Report deleted successfully.</div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Reporter</th>
                        <th>Reported User</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $report['id'] ?></td>
                            <td><?= htmlspecialchars($report['reporter_name']) ?></td>
                            <td><?= htmlspecialchars($report['reported_name']) ?></td>
                            <td><?= htmlspecialchars($report['created_at']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this report?');">
                                    <input type="hidden" name="delete_report_id" value="<?= $report['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No reports found.</div>
    <?php endif; ?>

    <div class="mt-4 text-end">
        <a href="admin_panel.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Admin Panel
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>