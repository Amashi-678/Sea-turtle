
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";

$error = "";
$title = "";
$content = "";
$status = "Draft";

// Generate CSRF token
if (empty($_SESSION['blog_csrf'])) {
    $_SESSION['blog_csrf'] = bin2hex(random_bytes(32));
}

// Create slug from title
function createSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $status = $_POST["status"] ?? "Draft";

    // Validate CSRF
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

    } elseif (empty($_SESSION['admin_id'])) {

        $error = "Admin session not found.";

    } else {

        $imageName = "";

        // Image upload
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

                    $imageName = bin2hex(random_bytes(16)) .
                        "." . $allowedTypes[$mime];

                    if (!move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $uploadDir . $imageName
                    )) {
                        $error = "Unable to save uploaded image.";
                        $imageName = "";
                    }
                }
            }
        }

        if (empty($error)) {

            $adminId = (int)$_SESSION['admin_id'];

            $slug = createSlug($title) . "-" .
                bin2hex(random_bytes(4));

            $sql = "INSERT INTO blogs
                    (admin_id, title, slug, content, image, status)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "isssss",
                    $adminId,
                    $title,
                    $slug,
                    $content,
                    $imageName,
                    $status
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    header("Location: index.php?success=added");
                    exit;

                } else {

                    $error = "Unable to add blog. Please try again.";

                    if (!empty($imageName)) {
                        @unlink(__DIR__ . "/../../uploads/blogs/" . $imageName);
                    }
                }

                $stmt->close();

            } else {

                $error = "Database error. Please try again.";

                if (!empty($imageName)) {
                    @unlink(__DIR__ . "/../../uploads/blogs/" . $imageName);
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

<title>Add Blog | Turtle Haven Admin</title>

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

    <h2>Add New Blog</h2>
    <p>Create a new Turtle Haven blog post.</p>

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
            placeholder="Enter blog title"
            required>

            <label for="content">Blog Content</label>

            <textarea
            id="content"
            name="content"
            rows="8"
            placeholder="Write your blog content..."
            required><?= htmlspecialchars($content) ?></textarea>

            <label for="image">Blog Image</label>

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

            <button type="submit">Save Blog</button>

            <a href="index.php" class="back-btn">Back</a>

        </form>

    </div>

</div>

</body>
</html>
