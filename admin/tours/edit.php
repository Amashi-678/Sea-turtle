
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

$tour_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$tour_id || $tour_id < 1) {
    die("Invalid tour ID.");
}

// Get existing tour
$stmt = $conn->prepare("SELECT * FROM tours WHERE tour_id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();
$stmt->close();

if (!$tour) {
    die("Tour not found.");
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
    $status = $_POST['status'] ?? '';

    $image_path = $tour['image'];
    $new_image = null;

    if ($tour_name === '' || $price === false || $price < 0 ||
        $max_visitors === false || $max_visitors < 1 ||
        !in_array($status, ['active', 'inactive'])) {

        $error = "Please enter valid tour details.";

    } else {

        // Optional new image
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
                        $new_image = $image_path;
                    } else {
                        $error = "Unable to upload new image.";
                    }
                }
            }
        }

        if ($error === "") {

            $update = $conn->prepare(
                "UPDATE tours SET
                tour_name = ?,
                description = ?,
                duration = ?,
                price = ?,
                max_visitors = ?,
                image = ?,
                status = ?
                WHERE tour_id = ?"
            );

            $update->bind_param(
                "sssdissi",
                $tour_name,
                $description,
                $duration,
                $price,
                $max_visitors,
                $image_path,
                $status,
                $tour_id
            );

            if ($update->execute()) {

                $update->close();

                // Remove old image only after successful update
                if ($new_image && $tour['image'] &&
                    preg_match('/\Auploads\/tours\/[a-f0-9]{32}\.(jpg|png|webp)\z/', $tour['image'])) {

                    $old_image = "../../" . $tour['image'];

                    if (is_file($old_image)) {
                        unlink($old_image);
                    }
                }

                header("Location: index.php?message=updated");
                exit();

            } else {

                $error = "Unable to update tour: " . $update->error;
                $update->close();

                if ($new_image && is_file("../../" . $new_image)) {
                    unlink("../../" . $new_image);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Tour - Turtle Haven</title>

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
.current-image {
    max-width: 180px;
    display: block;
    margin-top: 10px;
}
</style>
</head>

<body>

<div class="container">

<h2>Edit Tour</h2>

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="csrf_token"
value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<label>Tour Name</label>
<input type="text" name="tour_name" required maxlength="150"
value="<?= htmlspecialchars($_POST['tour_name'] ?? $tour['tour_name']) ?>">

<label>Description</label>
<textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? $tour['description'] ?? '') ?></textarea>

<label>Duration</label>
<input type="text" name="duration"
value="<?= htmlspecialchars($_POST['duration'] ?? $tour['duration'] ?? '') ?>">

<label>Price (Rs.)</label>
<input type="number" name="price" min="0" step="0.01" required
value="<?= htmlspecialchars($_POST['price'] ?? $tour['price']) ?>">

<label>Maximum Visitors</label>
<input type="number" name="max_visitors" min="1" required
value="<?= htmlspecialchars($_POST['max_visitors'] ?? $tour['max_visitors']) ?>">

<label>Current Tour Image</label>

<?php if ($tour['image']): ?>
<img class="current-image"
src="../../<?= htmlspecialchars($tour['image']) ?>"
alt="Current tour image">
<?php else: ?>
<p>No image uploaded.</p>
<?php endif; ?>

<label>Upload New Image (Optional)</label>
<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

<label>Status</label>
<select name="status">
<option value="active"
<?= (($_POST['status'] ?? $tour['status']) === 'active') ? 'selected' : '' ?>>
Active
</option>

<option value="inactive"
<?= (($_POST['status'] ?? $tour['status']) === 'inactive') ? 'selected' : '' ?>>
Inactive
</option>
</select>

<button type="submit">Update Tour</button>

<a href="index.php" class="back">Back</a>

</form>

</div>

</body>
</html>
