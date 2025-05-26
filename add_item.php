<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'qr/qrlib.php'; // ✅ QR Code generator

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = sanitize_input($_POST["title"]);
    $description = sanitize_input($_POST["description"]);
    $location    = sanitize_input($_POST["location"]);
    $date_lost   = $_POST["date_lost"]; // Optional: validate format
    $image_path  = "";

    // ✅ Image handling
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_type = $_FILES["image"]["type"];
        if (!in_array($file_type, $allowed_types)) {
            $error = "❌ Only JPG and PNG images are allowed.";
        } else {
            $unique_name = time() . "_" . basename($_FILES["image"]["name"]);
            $image_path = $target_dir . $unique_name;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                $error = "❌ Failed to upload the image.";
            }
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO items (user_id, title, description, location, date_lost, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $title, $description, $location, $date_lost, $image_path);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $item_id = $stmt->insert_id;

            // ✅ Generate QR Code
            $qr_path = "uploads/qr_item_$item_id.png";
            $qr_url = "http://localhost/lost-found/item_view.php?id=$item_id";
            QRcode::png($qr_url, $qr_path, QR_ECLEVEL_L, 4);

            log_event("ITEM ADDED: User #$user_id added item #$item_id");
            $success = "✅ Item successfully added!";
        } else {
            $error = "❌ Something went wrong while saving the item.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-plus-square"></i> Add Lost Item</h3>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Item Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Black Backpack" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Details about the item..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Seen Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Central Park" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date Lost</label>
                        <input type="date" name="date_lost" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Image (optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>