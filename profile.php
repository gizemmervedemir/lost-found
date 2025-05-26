<?php
// includes/db.php sets up $conn and starts the session
include 'includes/db.php';
// includes/functions.php provides log_event() and other helpers
include 'includes/functions.php';

// Redirect guests to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// Handle avatar removal or upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove existing avatar
    if (isset($_POST['remove_avatar'])) {
        $stmt = $conn->prepare('SELECT profile_image FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc()['profile_image'] ?? '';

        if ($current && file_exists($current)) {
            unlink($current);
        }

        $stmt = $conn->prepare('UPDATE users SET profile_image = NULL WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        log_event("User #{$user_id} removed their avatar");
        $success = 'Profile photo removed.';
    }

    // Upload new avatar
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext     = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = 'Only JPG, PNG or GIF files are allowed.';
        } else {
            $uploadDir = 'uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newName = "user_{$user_id}.{$ext}";
            $target  = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $stmt = $conn->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
                $stmt->bind_param('si', $target, $user_id);
                $stmt->execute();

                log_event("User #{$user_id} uploaded a new avatar");
                $_SESSION['user_avatar'] = $target;
                $success = 'Profile photo updated.';
            } else {
                $error = 'Failed to upload the image.';
            }
        }
    }
}

// Fetch user profile
$stmt = $conn->prepare('
    SELECT name, email, created_at, profile_image
    FROM users
    WHERE id = ?
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Determine which avatar to display
if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
    $avatar = $user['profile_image'];
} else {
    $avatar = 'assets/default_avatar.png';
}

// Get user stats
$total_items         = $conn->query("SELECT COUNT(*) AS c FROM items WHERE user_id = {$user_id}")->fetch_assoc()['c'];
$total_matches       = $conn->query("SELECT COUNT(*) AS c FROM matches WHERE requester_id = {$user_id}")->fetch_assoc()['c'];
$total_notifications = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id = {$user_id}")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<div class="container mt-5">
  <h3 class="mb-4"><i class="bi bi-person-circle"></i> My Profile</h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-body d-flex align-items-center">
      <div class="me-4 text-center">
        <img
          src="<?= htmlspecialchars($avatar) ?>"
          alt="Avatar"
          class="rounded-circle"
          style="width:120px; height:120px; object-fit:cover; cursor:pointer;"
          id="profile-avatar"
        >
      </div>
      <div>
        <h5><?= htmlspecialchars($user['name']) ?></h5>
        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p class="mb-0"><strong>Member since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
      </div>
    </div>
    <div class="card-footer">
      <form method="POST" enctype="multipart/form-data" class="d-flex gap-3 align-items-center">
        <input type="file" name="profile_image" id="profile-upload" accept="image/*" class="form-control" style="display:none;" required>
        <button type="submit" class="btn btn-primary btn-sm">Upload New</button>

        <?php if (!empty($user['profile_image'])): ?>
          <button type="submit" name="remove_avatar"
                  class="btn btn-outline-danger btn-sm"
                  onclick="return confirm('Remove your profile photo?');">
            Remove
          </button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="row text-center">
    <div class="col-md-4 mb-3">
      <div class="card bg-light border-0 shadow-sm">
        <div class="card-body">
          <h6>My Items</h6>
          <h3><?= $total_items ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card bg-light border-0 shadow-sm">
        <div class="card-body">
          <h6>My Matches</h6>
          <h3><?= $total_matches ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card bg-light border-0 shadow-sm">
        <div class="card-body">
          <h6>Notifications</h6>
          <h3><?= $total_notifications ?></h3>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Show file selector when avatar is clicked
document.getElementById('profile-avatar').addEventListener('click', () => {
    const upload = document.getElementById('profile-upload');
    upload.style.display = 'block';
    upload.click();
});
</script>