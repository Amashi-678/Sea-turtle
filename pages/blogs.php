
<?php

$pageTitle = "Blogs | Sea Turtle Conservation";

// Connect to database
require_once "../config/database.php";

// Fetch published blogs
$sql = "SELECT blog_id, title, slug, content, image, created_at
        FROM blogs
        WHERE status = 'Published'
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load blogs. Please try again later.");
}

// Include website header
require_once "../includes/header.php";

?>

<!-- PAGE BANNER -->
<section class="about-banner banner-blogs">

    <div class="container">
        <p>LATEST STORIES &amp; NEWS</p>

        <h1>Sea Turtle Blogs</h1>

        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>/</span>
            <span>Blogs</span>
        </div>
    </div>

</section>


<!-- Introduction -->
<section class="section">
    <div class="container species-intro">

        <span class="section-tag">Marine Life</span>

        <h2 class="section-heading">
            Explore Our Blogs
        </h2>

        <p>
            Learn more about sea turtles, marine conservation,
            ocean ecosystems, and the importance of protecting
            marine wildlife through our latest articles.
        </p>

    </div>
</section>


<!-- Dynamic Blog Cards -->
<section class="section light-bg">
    <div class="container">

        <div class="card-grid species-grid">

            <?php if ($result->num_rows > 0): ?>

                <?php while ($blog = $result->fetch_assoc()): ?>

                    <div class="info-card species-card">

                        <!-- Blog Image -->
                        <?php if (!empty($blog['image'])): ?>

                            <img
                                src="../uploads/blogs/<?= htmlspecialchars(
                                    rawurlencode(basename($blog['image']))
                                ) ?>"
                                alt="<?= htmlspecialchars($blog['title']) ?>"
                            >

                        <?php else: ?>

                            <img
                                src="../assets/images/green-turtle.jpg"
                                alt="Sea Turtle"
                            >

                        <?php endif; ?>


                        <!-- Blog Content -->
                        <div class="card-content">

                            <h3>
                                <?= htmlspecialchars($blog['title']) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars(
                                    mb_strimwidth(
                                        $blog['content'],
                                        0,
                                        180,
                                        "..."
                                    )
                                )) ?>
                            </p>

                            <span class="species-status">
                                Published:
                                <?= date(
                                    "d M Y",
                                    strtotime($blog['created_at'])
                                ) ?>
                            </span>

                            <br><br>

                            <a
                                href="blog-details.php?slug=<?= urlencode($blog['slug']) ?>"
                                class="btn-primary"
                            >
                                Read More
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <!-- No Blogs Available -->
                <div class="info-card">
                    <div class="card-content">

                        <h3>No Blogs Available</h3>

                        <p>
                            There are currently no published blog posts.
                            Please check back soon for new articles.
                        </p>

                    </div>
                </div>

            <?php endif; ?>

        </div>

    </div>
</section>


<!-- Conservation Message -->
<section class="section">
    <div class="container species-message">

        <h2>Protect Every Sea Turtle</h2>

        <p>
            Every sea turtle plays an important role in the marine
            environment. Protecting their habitats and nesting sites
            helps ensure their survival for future generations.
        </p>

        <a href="conservation.php" class="btn-primary">
            Learn About Conservation
        </a>

    </div>
</section>


<?php

// Free database result
$result->free();

// Include website footer
require_once "../includes/footer.php";

?>
