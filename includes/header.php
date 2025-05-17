<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost & Found</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand i {
            margin-right: 6px;
        }
        .card-body {
            padding: 1.25rem;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-box-seam"></i> Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_item.php"><i class="bi bi-plus-square"></i> Add Item</a></li>
                    <li class="nav-item"><a class="nav-link" href="match_status.php"><i class="bi bi-link-45deg"></i> My Matches</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_items.php"><i class="bi bi-collection"></i> My Items</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_panel.php"><i class="bi bi-shield-lock"></i> Admin Panel</a></li>
                        <li class="nav-item"><a class="nav-link" href="view_logs.php"><i class="bi bi-file-earmark-text"></i> View Logs</a></li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text text-white me-3 d-flex align-items-center">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
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