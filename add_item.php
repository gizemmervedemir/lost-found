<?php
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST["title"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $location = $conn->real_escape_string($_POST["location"]);
    $date_lost = $_POST["date_lost"];
    $user_id = $_SESSION["user_id"];

    // Dosya yükleme
    $image_path = "";
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $image_path = $target_dir . $filename;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
            $error = "Image upload failed.";
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO items (user_id, title, description, location, date_lost, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $title, $description, $location, $date_lost, $image_path);
        $stmt->execute();
        $success = "Item added successfully!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Add Lost Item</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Item Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Seen Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date Lost</label>
                <input type="date" name="date_lost" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Image</label>
                <input type="file" name="image" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Add Item</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>