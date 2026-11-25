
<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token']) ||
    empty($_SESSION['blog_csrf']) ||
    !hash_equals($_SESSION['blog_csrf'], $_POST['csrf_token'])
) {
    die("Invalid request. Please go back and try again.");
}

// Get blog ID
$blogId = filter_input(INPUT_POST, 'blog_id', FILTER_VALIDATE_INT);

if (!$blogId || $blogId < 1) {
    header("Location: index.php");
    exit;
}

// Get image filename before deleting blog
$stmt = $conn->prepare(
    "SELECT image FROM blogs WHERE blog_id = ?"
);

$stmt->bind_param("i", $blogId);
$stmt->execute();

$result = $stmt->get_result();
$blog = $result->fetch_assoc();

$stmt->close();

if (!$blog) {
    header("Location: index.php?error=notfound");
    exit;
}

// Delete blog from database
$stmt = $conn->prepare(
    "DELETE FROM blogs WHERE blog_id = ?"
);

$stmt->bind_param("i", $blogId);

if ($stmt->execute()) {

    $stmt->close();

    // Delete associated image
    if (!empty($blog['image'])) {

        $imagePath =
            __DIR__ . "/../../uploads/blogs/" .
            basename($blog['image']);

        if (is_file($imagePath)) {
            @unlink($imagePath);
        }
    }

    header("Location: index.php?success=deleted");
    exit;

} else {

    $stmt->close();

    header("Location: index.php?error=deletefailed");
    exit;
}

?>
