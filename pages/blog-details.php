
<?php

$pageTitle = "Blog Details | Sea Turtle Conservation";

// Connect to database
require_once "../config/database.php";

// Get blog slug from URL
$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(404);
    die("Blog not found.");
}

// Fetch selected published blog
$sql = "SELECT blog_id, title, slug, content, image, created_at
        FROM blogs
        WHERE slug = ? AND status = 'Published'
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error. Please try again later.");
}

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$blog = $result->fetch_assoc();

$stmt->close();

// Check whether blog exists
if (!$blog) {
    http_response_code(404);
}

// Include website header
require_once "../includes/header.php";

?>

<!-- Blog Details Banner -->
<section class="species-banner">
    <div class="container">

        <h1>
            <?= $blog ? htmlspecialchars($blog['title']) : "Blog Not Found" ?>
        </h1>

        <p>
            Sea Turtle Conservation | Sri Lanka
        </p>

    </div>
</section>


<?php if ($blog): ?>

    <!-- Blog Details Section -->
    <section class="section">
        <div class="container">

            <div class="info-card">

                <!-- Blog Image -->
                <?php if (!empty($blog['image'])): ?>

                    <img
                        src="../uploads/blogs/<?= htmlspecialchars(
                            rawurlencode(basename($blog['image']))
                        ) ?>"
                        alt="<?= htmlspecialchars($blog['title']) ?>"
                        style="
                            width: 100%;
                            max-height: 450px;
                            object-fit: cover;
                        "
                    >

                <?php else: ?>

                    <img
                        src="../assets/images/green-turtle.jpg"
                        alt="Sea Turtle"
                        style="
                            width: 100%;
                            max-height: 450px;
                            object-fit: cover;
                        "
                    >

                <?php endif; ?>


                <!-- Complete Blog Content -->
                <div class="card-content"
                     style="padding: 30px;">

                    <h2>
                        <?= htmlspecialchars($blog['title']) ?>
                    </h2>

                    <span class="species-status">
                        Published:
                        <?= date(
                            "d M Y",
                            strtotime($blog['created_at'])
                        ) ?>
                    </span>

                    <hr>

                    <div style="
                        line-height: 1.9;
                        font-size: 16px;
                        white-space: normal;
                    ">
                        <?= nl2br(htmlspecialchars($blog['content'])) ?>
                    </div>

                    <br>

                    <!-- Back to Blogs -->
                    <a href="blogs.php" class="btn-primary">
                        Back to Blogs
                    </a>

                </div>

            </div>

        </div>
    </section>

<?php else: ?>

    <!-- Blog Not Found -->
    <section class="section">
        <div class="container species-intro">

            <h2 class="section-heading">
                Blog Not Found
            </h2>

            <p>
                Sorry, this blog does not exist or is no longer published.
            </p>

            <a href="blogs.php" class="btn-primary">
                Back to Blogs
            </a>

        </div>
    </section>

<?php endif; ?>


<?php

require_once "../includes/footer.php";

?>
