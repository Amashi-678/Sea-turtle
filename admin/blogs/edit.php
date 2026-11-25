
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";

$error = "";

if (empty($_SESSION['blog_csrf'])) {
    $_SESSION['blog_csrf'] = bin2hex(random_bytes(32));
}

// Get blog ID
$blogId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$blogId || $blogId < 1) {
    header("Location: index.php");
    exit;
}

// Fetch existing blog
$stmt = $conn->prepare(
    "SELECT * FROM blogs WHERE blog_id = ?"
);

$stmt->bind_param("i", $blogId);
$stmt->execute();

$result = $stmt->get_result();
$blog = $result->fetch_assoc();

$stmt->close();

if (!$blog) {
    header("Location: index.php");
    exit;
}

$title = $blog['title'];
$content = $blog['content'];
$status = $blog['status'];
$imageName = $blog['image'];

function createBlogSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// Process update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $status = $_POST["status"] ?? "";

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['blog_csrf'], $_POST['csrf_token'])
    ) {
        $error = "Invalid request. Please try again.";

    } elseif (empty($title) || empty($content)) {

        $error = "Please fill in all required fields.";

    } elseif (strlen($title) > 200) {

        $error = "Blog title is too long.";

    } elseif (!in_array($status, ["Published", "Draft"], true)) {

        $error = "Invalid blog status.";

    } else {

        $newImageName = $imageName;
        $uploadedNewImage = false;

        // Upload replacement image if selected
        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $error = "Image upload failed.";

            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {

                $error = "Image must be smaller than 5MB.";

            } else {

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['image']['tmp_name']);

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($allowedTypes[$mime])) {

                    $error = "Only JPG, PNG and WEBP images are allowed.";

                } else {

                    $uploadDir = __DIR__ . "/../../uploads/blogs/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $newImageName = bin2hex(random_bytes(16)) .
                        "." . $allowedTypes[$mime];

                    if (move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $uploadDir . $newImageName
                    )) {
                        $uploadedNewImage = true;
                    } else {
                        $error = "Unable to upload new image.";
                    }
                }
            }
        }

        if (empty($error)) {

            // Generate updated slug
            $slug = createBlogSlug($title) . "-" .
                bin2hex(random_bytes(4));

            $sql = "UPDATE blogs
                    SET title = ?,
                        slug = ?,
                        content = ?,
                        image = ?,
                        status = ?
                    WHERE blog_id = ?";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "sssssi",
                    $title,
                    $slug,
                    $content,
                    $newImageName,
                    $status,
                    $blogId
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    // Delete previous image after successful update
                    if ($uploadedNewImage && !empty($imageName)) {

                        $oldImagePath =
                            __DIR__ . "/../../uploads/blogs/" .
                            basename($imageName);

                        if (is_file($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }

                    header("Location: index.php?success=updated");
                    exit;

                } else {

                    $error = "Unable to update blog.";

                    if ($uploadedNewImage) {
                        @unlink(
                            __DIR__ . "/../../uploads/blogs/" .
                            $newImageName
                        );
                    }
                }

                $stmt->close();

            } else {

                $error = "Database error.";

                if ($uploadedNewImage) {
                    @unlink(
                        __DIR__ . "/../../uploads/blogs/" .
                        $newImageName
                    );
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Blog | Turtle Haven Admin</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f3;
}

header {
    background: #176b50;
    color: white;
    padding: 20px;
}

.sidebar {
    width: 220px;
    background: #154d3b;
    min-height: 100vh;
    position: fixed;
    padding-top: 20px;
}

.sidebar a {
    display: block;
    color: white;
    padding: 15px;
    text-decoration: none;
}

.sidebar a:hover {
    background: #26775b;
}

.content {
    margin-left: 250px;
    padding: 30px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    margin-top: 20px;
    max-width: 750px;
}

label {
    display: block;
    margin-top: 18px;
    margin-bottom: 8px;
    font-weight: bold;
}

input, textarea, select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-family: Arial;
}

textarea {
    resize: vertical;
}

button, .back-btn {
    background: #176b50;
    color: white;
    border: none;
    padding: 11px 20px;
    cursor: pointer;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
}

button:hover, .back-btn:hover {
    background: #26775b;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 5px;
}

.current-image {
    max-width: 200px;
    max-height: 150px;
    object-fit: cover;
    margin-top: 10px;
    border-radius: 5px;
}

.logout-form {
    padding: 15px;
}

.logout-form button {
    width: 100%;
    text-align: left;
    margin-top: 0;
}

@media(max-width:768px) {
    .sidebar { width: 160px; }
    .content { margin-left: 180px; padding: 15px; }
}

</style>

</head>

<body>

<header>
    <h2>Sea Turtle Admin Panel</h2>
</header>

<div class="sidebar">

    <a href="../dashboard.php">Dashboard</a>
    <a href="../bookings/index.php">Bookings</a>
    <a href="../tours/index.php">Manage Tours</a>

    <a href="index.php">Manage Blogs</a>

    <form action="../logout.php" method="POST" class="logout-form">

        <input type="hidden" name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['admin_logout_csrf'] ?? '') ?>">

        <button type="submit">Logout</button>

    </form>

</div>

<div class="content">

    <h2>Edit Blog</h2>
    <p>Update your existing blog post.</p>

    <div class="card">

        <h3>Blog Information</h3>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['blog_csrf']) ?>">

            <label for="title">Blog Title</label>

            <input type="text"
            id="title"
            name="title"
            maxlength="200"
            value="<?= htmlspecialchars($title) ?>"
            required>

            <label for="content">Blog Content</label>

            <textarea
            id="content"
            name="content"
            rows="8"
            required><?= htmlspecialchars($content) ?></textarea>

            <label>Current Blog Image</label>

            <?php if (!empty($imageName)): ?>

                <img
                src="../../uploads/blogs/<?= rawurlencode(basename($imageName)) ?>"
                class="current-image"
                alt="Current blog image">

            <?php else: ?>

                <p>No image uploaded.</p>

            <?php endif; ?>

            <label for="image">Replace Blog Image (Optional)</label>

            <input type="file"
            id="image"
            name="image"
            accept=".jpg,.jpeg,.png,.webp">

            <small>Allowed: JPG, PNG and WEBP. Maximum 5MB.</small>

            <label for="status">Blog Status</label>

            <select name="status" id="status" required>

                <option value="Draft"
                <?= $status === "Draft" ? "selected" : "" ?>>
                    Draft
                </option>

                <option value="Published"
                <?= $status === "Published" ? "selected" : "" ?>>
                    Published
                </option>

            </select>

            <button type="submit">Update Blog</button>

            <a href="index.php" class="back-btn">Back</a>

        </form>

    </div>

</div>

</body>
</html>
