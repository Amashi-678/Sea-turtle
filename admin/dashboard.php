
<?php
require_once "includes/auth.php";

// Generate secure logout CSRF token
$_SESSION['admin_logout_csrf'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Sea Turtle</title>

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
        }

        button {
            background: #176b50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #26775b;
        }

        .logout-form {
            padding: 15px;
        }

        .logout-form button {
            width: 100%;
            text-align: left;
        }
    </style>
</head>

<body>

<header>
    <h2>Sea Turtle Admin Panel</h2>
</header>

<!-- Sidebar -->
<div class="sidebar">

    <a href="dashboard.php">Dashboard</a>

    <a href="bookings/index.php">Bookings</a>

    <a href="tours/index.php">Manage Tours</a>

    <a href="blogs/index.php">Manage Blogs</a>

    <!-- Secure Logout Form -->
    <form action="logout.php" method="POST" class="logout-form">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['admin_logout_csrf']); ?>"
        >

        <button type="submit">Logout</button>

    </form>

</div>

<!-- Main Content -->
<div class="content">

    <h2>
        Welcome,
        <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
    </h2>

    <p>Welcome to your Sea Turtle Conservation Admin Dashboard.</p>

    <div class="card">

        <h3>Admin Dashboard</h3>

        <p>Manage your website from this control panel.</p>

    </div>

</div>

</body>
</html>
