
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";

// Generate CSRF tokens
if (empty($_SESSION['blog_csrf'])) {
    $_SESSION['blog_csrf'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['admin_logout_csrf'])) {
    $_SESSION['admin_logout_csrf'] = bin2hex(random_bytes(32));
}

// Fetch all blogs
$sql = "SELECT blog_id, admin_id, title, slug, image, status, created_at
        FROM blogs
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load blogs. Please try again later.");
}

// Success and error messages
$success = "";
$error = "";

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $success = "Blog added successfully!";
    } elseif ($_GET['success'] === 'updated') {
        $success = "Blog updated successfully!";
    } elseif ($_GET['success'] === 'deleted') {
        $success = "Blog deleted successfully!";
    }

}

if (isset($_GET['error'])) {

    if ($_GET['error'] === 'notfound') {
        $error = "Blog not found.";
    } elseif ($_GET['error'] === 'deletefailed') {
        $error = "Unable to delete blog. Please try again.";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Blogs | Turtle Haven Admin</title>

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
            overflow-x: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        button, .add-btn {
            background: #176b50;
            color: white;
            border: none;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
        }

        button:hover, .add-btn:hover {
            background: #26775b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 750px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #e6f0eb;
            color: #154d3b;
        }

        tr:hover {
            background: #f8faf9;
        }

        .status {
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .published {
            background: #d4edda;
            color: #155724;
        }

        .draft {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .edit-btn {
            background: #2878a8;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .edit-btn:hover {
            background: #1d6089;
        }

        .delete-btn {
            background: #c0392b;
            color: white;
            padding: 7px 12px;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background: #a93226;
        }

        .action-buttons form {
            margin: 0;
        }

        .logout-form {
            padding: 15px;
        }

        .logout-form button {
            width: 100%;
            text-align: left;
        }

        .empty-message {
            text-align: center;
            padding: 25px;
            color: #666;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-top: 15px;
        }

        @media (max-width: 768px) {

            .sidebar {
                width: 160px;
            }

            .content {
                margin-left: 180px;
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<header>
    <h2>Sea Turtle Admin Panel</h2>
</header>

<!-- Sidebar -->

<div class="sidebar">

    <a href="../dashboard.php">Dashboard</a>

    <a href="../bookings/index.php">Bookings</a>

    <a href="../tours/index.php">Manage Tours</a>



    <a href="index.php">Manage Blogs</a>

    <!-- Secure Logout -->

    <form action="../logout.php" method="POST" class="logout-form">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['admin_logout_csrf']) ?>"
        >

        <button type="submit">Logout</button>

    </form>

</div>

<!-- Main Content -->

<div class="content">

    <div class="top-bar">

        <div>

            <h2>Manage Blogs</h2>

            <p>View and manage Turtle Haven blog posts.</p>

        </div>

        <!-- Add Blog Button -->

        <a href="add.php" class="add-btn">
            + Add New Blog
        </a>

    </div>

    <!-- Success Message -->

    <?php if (!empty($success)): ?>

        <div class="success-message">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>

    <!-- Error Message -->

    <?php if (!empty($error)): ?>

        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <!-- Blog Table -->

    <div class="card">

        <h3>All Blog Posts</h3>

        <table>

            <thead>

                <tr>

                    <th>Blog ID</th>

                    <th>Title</th>

                    <th>Image</th>

                    <th>Status</th>

                    <th>Created Date</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($blog = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= (int)$blog['blog_id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($blog['title']) ?>
                            </td>

                            <td>

                                <?php if (!empty($blog['image'])): ?>

                                    <?= htmlspecialchars(basename($blog['image'])) ?>

                                <?php else: ?>

                                    No image

                                <?php endif; ?>

                            </td>

                            <td>

                                <span class="status
                                    <?= strtolower($blog['status']) === 'published'
                                        ? 'published'
                                        : 'draft' ?>">

                                    <?= htmlspecialchars(ucfirst($blog['status'])) ?>

                                </span>

                            </td>

                            <td>
                                <?= htmlspecialchars($blog['created_at']) ?>
                            </td>

                            <td>

                                <div class="action-buttons">

                                    <!-- Edit Blog -->

                                    <a
                                        href="edit.php?id=<?= (int)$blog['blog_id'] ?>"
                                        class="edit-btn">

                                        Edit

                                    </a>

                                    <!-- Delete Blog -->

                                    <form
                                        action="delete.php"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this blog?');">

                                        <input
                                            type="hidden"
                                            name="blog_id"
                                            value="<?= (int)$blog['blog_id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['blog_csrf']) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-btn">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6" class="empty-message">

                            No blog posts available.
                            Click Add New Blog to create your first blog.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>

<?php
$result->free();
?>
