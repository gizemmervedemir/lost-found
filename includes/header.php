<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include_once 'includes/db.php';

$user_avatar = 'assets/default_avatar.png';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare('SELECT profile_image, gender, role, name FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
        $user_avatar = $user['profile_image'];
    } else {
        $gender = $user['gender'] ?? 'male';
        $user_avatar = $gender === 'female' ? 'assets/default_female.png' : 'assets/default_male.png';
    }

    // Set role and username in session
    $_SESSION['role'] = $user['role'] ?? 'user';
    $_SESSION['user_name'] = $user['name'] ?? 'User';
    $_SESSION['user_avatar'] = $user_avatar;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #fdfcfb, #e2d1c3); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(90deg, #667eea, #764ba2); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; }
        .nav-link:hover { text-decoration: underline; }
        .card { border: none; border-radius: 20px; box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
        .badge { font-size: 0.75rem; padding: 0.4em 0.6em; }
        .avatar-header { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; margin-right: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-compass me-2"></i> Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Home</a></li>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_panel.php"><i class="bi bi-shield-lock"></i> Admin Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="view_logs.php"><i class="bi bi-file-earmark-text"></i> Logs</a></li>
                        <li class="nav-item"><a class="nav-link" href="view_reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="add_item.php"><i class="bi bi-plus-square"></i> Add Item</a></li>
                        <li class="nav-item"><a class="nav-link" href="match_status.php"><i class="bi bi-link-45deg"></i> My Matches</a></li>
                        <li class="nav-item"><a class="nav-link" href="my_items.php"><i class="bi bi-collection"></i> My Items</a></li>
                        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-lines-fill"></i> My Profile</a></li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav me-3">
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span id="notifCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" id="notifList">
                            <li class="dropdown-header">Notifications</li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="text-muted px-3">No new notifications</li>
                        </ul>
                    </li>
                </ul>

                <span class="navbar-text d-flex align-items-center me-3">
                    <img src="<?= htmlspecialchars($user_avatar) ?>" alt="Avatar" class="avatar-header">
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <span class="badge bg-warning text-dark ms-2">Admin</span>
                    <?php endif; ?>
                </span>

                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Register</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
<?php if (isset($_SESSION['user_id'])): ?>
<script>
document.addEventListener("DOMContentLoaded", () => {
    fetch('notifications.php')
        .then(res => res.json())
        .then(data => {
            const notifList  = document.getElementById('notifList'),
                  notifCount = document.getElementById('notifCount');
            if (data.status === "success" && data.count > 0) {
                notifCount.classList.remove("d-none");
                notifCount.textContent = data.count;
                notifList.innerHTML = '<li class="dropdown-header">Notifications</li><li><hr class="dropdown-divider"></li>';
                data.notifications.forEach(n => {
                    notifList.innerHTML += `
                      <li>
                        <span class="dropdown-item small">
                          ${n.message}<br>
                          <small class="text-muted">${n.created_at}</small>
                        </span>
                      </li>`;
                });
            }
        });
});
</script>
<?php endif; ?>