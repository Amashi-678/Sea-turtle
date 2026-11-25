<?php
// includes/header.php  -  shared header for every page
// NOTE: no blank line or space before the opening PHP tag (it breaks session_start / redirects).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token (used by the logout form)
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Automatic base URL, e.g. "/sea_turtle_project" (works in any folder name)
if (!isset($base)) {
    $projectRoot = str_replace("\\", "/", realpath(__DIR__ . "/.."));
    $docRoot     = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"]));
    $base        = rtrim(substr($projectRoot, strlen($docRoot)), "/");
}

$currentPage = basename($_SERVER["PHP_SELF"]);
$pageTitle   = $pageTitle ?? "Sea Turtle Conservation Centre";

// Helper for the "active" menu class
function navActive(string $file, string $current): string
{
    return $file === $current ? "active" : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css?v=3">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="container navbar">

        <a href="<?= $base ?>/index.php" class="logo">🐢 Sea Turtle</a>

        <nav>
            <ul class="nav-links">
                <li><a href="<?= $base ?>/index.php" class="<?= navActive('index.php', $currentPage) ?>">Home</a></li>
                <li><a href="<?= $base ?>/pages/about.php" class="<?= navActive('about.php', $currentPage) ?>">About Us</a></li>
                <li><a href="<?= $base ?>/pages/blogs.php" class="<?= navActive('blogs.php', $currentPage) ?>">Blogs</a></li>
                <li><a href="<?= $base ?>/pages/tours.php" class="<?= navActive('tours.php', $currentPage) ?>">Tours &amp; Booking</a></li>
                <li><a href="<?= $base ?>/pages/contact.php" class="<?= navActive('contact.php', $currentPage) ?>">Contact Us</a></li>
            </ul>
        </nav>

        <div class="header-account">
            <?php if (isset($_SESSION["user_id"])): ?>

                <span class="welcome-user">
                    Welcome, <?= htmlspecialchars($_SESSION["user_name"] ?? "User") ?>
                </span>

                <form action="<?= $base ?>/pages/logout.php" method="POST" class="logout-form">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                    <button type="submit" class="login-btn">Logout</button>
                </form>

            <?php else: ?>

                <a href="<?= $base ?>/pages/login.php" class="login-btn">Login</a>
                <a href="<?= $base ?>/pages/register.php" class="register-btn">Register</a>

            <?php endif; ?>

            <a href="<?= $base ?>/pages/booking.php" class="nav-btn">Book a Visit</a>
        </div>

    </div>
</header>
