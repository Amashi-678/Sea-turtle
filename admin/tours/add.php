
<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid request. Please refresh the page.");
    }

    $tour_name = trim($_POST['tour_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $max_visitors = filter_var($_POST['max_visitors'] ?? '', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? 'active';

    $image_path = null;

    if ($tour_name === '' || $price === false || $price < 0 ||
        $max_visitors === false || $max_visitors < 1 ||
        !in_array($status, ['active', 'inactive'])) {

        $error = "Please enter valid tour details.";

    } else {

        // Image upload
        if (isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
                $error = "Image upload failed. Maximum size is 2MB.";
            } else {

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowed[$mime]) || getimagesize($file['tmp_name']) === false) {
                    $error = "Only JPG, PNG and WEBP images are allowed.";
                } else {

                    $upload_dir = "../../uploads/tours/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $image_path = "uploads/tours/" . $filename;
                    } else {
                        $error = "Unable to save uploaded image.";
                    }
                }
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare(
                "INSERT INTO tours
                (tour_name, description, duration, price, max_visitors, image, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssdiss",
                $tour_name,
                $description,
                $duration,
                $price,
                $max_visitors,
                $image_path,
                $status
            );

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?message=added");
                exit();
            } else {
                $error = "Unable to add tour: " . $stmt->error;

                if ($image_path && is_file("../../" . $image_path)) {
                    unlink("../../" . $image_path);
                }
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Tour - Turtle Haven</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    padding: 30px;
}
.container {
    max-width: 650px;
    background: white;
    padding: 25px;
    margin: auto;
    border-radius: 8px;
}
h2 { color: #244b3c; }
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input, textarea, select {
    width: 100%;
    padding: 11px;
    margin-top: 7px;
    box-sizing: border-box;
}
button, .back {
    display: inline-block;
    padding: 11px 18px;
    margin-top: 20px;
    border: none;
    border-radius: 5px;
    color: white;
    background: #244b3c;
    text-decoration: none;
    cursor: pointer;
}
.error { color: red; }
</style>
</head>

<body>

<div class="container">

<h2>Add New Tour</h2>

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="csrf_token"
value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<label>Tour Name</label>
<input type="text" name="tour_name" required maxlength="150"
value="<?= htmlspecialchars($_POST['tour_name'] ?? '') ?>">

<label>Description</label>
<textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

<label>Duration</label>
<input type="text" name="duration" placeholder="Example: 2 Hours"
value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>">

<label>Price (Rs.)</label>
<input type="number" name="price" min="0" step="0.01" required
value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">

<label>Maximum Visitors</label>
<input type="number" name="max_visitors" min="1" required
value="<?= htmlspecialchars($_POST['max_visitors'] ?? '20') ?>">

<label>Tour Image (JPG, PNG, WEBP - Max 2MB)</label>
<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

<label>Status</label>
<select name="status">
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>

<button type="submit">Save Tour</button>

<a href="index.php" class="back">Back</a>

</form>

</div>

</body>
</html>
