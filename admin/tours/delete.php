
<?php
session_start();

require_once "../../config/database.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// Validate tour ID
$tour_id = filter_input(INPUT_POST, 'tour_id', FILTER_VALIDATE_INT);

if (!$tour_id || $tour_id < 1) {
    header("Location: index.php?message=invalid");
    exit();
}

// Check existing bookings
$check = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE tour_id = ?"
);

$check->bind_param("i", $tour_id);
$check->execute();

$count = $check->get_result()->fetch_assoc()['total'];

$check->close();

// Prevent deletion if bookings exist
if ($count > 0) {
    header("Location: index.php?message=has_bookings");
    exit();
}

// Get tour image
$stmt = $conn->prepare(
    "SELECT image FROM tours WHERE tour_id = ?"
);

$stmt->bind_param("i", $tour_id);
$stmt->execute();

$tour = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$tour) {
    header("Location: index.php?message=not_found");
    exit();
}

// Delete tour
$delete = $conn->prepare(
    "DELETE FROM tours WHERE tour_id = ?"
);

$delete->bind_param("i", $tour_id);

if ($delete->execute()) {

    $delete->close();

    // Delete uploaded image
    if (
        !empty($tour['image']) &&
        preg_match(
            '/^uploads\/tours\/[a-f0-9]{32}\.(jpg|png|webp)$/',
            $tour['image']
        )
    ) {
        $image_path = "../../" . $tour['image'];

        if (is_file($image_path)) {
            unlink($image_path);
        }
    }

    header("Location: index.php?message=deleted");
    exit();

} else {

    $delete->close();

    header("Location: index.php?message=error");
    exit();
}
?>
