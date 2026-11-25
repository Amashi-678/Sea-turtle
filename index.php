
<?php
$pageTitle = "Ahungalla Sea Turtle Conservation Centre";

// Database connection
require_once "config/database.php";

// Get active tours
$tourSql = "
    SELECT tour_id, tour_name, description, duration,
           price, max_visitors, image
    FROM tours
    WHERE status = 'active'
    ORDER BY created_at DESC
    LIMIT 3
";

$tourResult = $conn->query($tourSql);

if (!$tourResult) {
    die("Unable to load tours.");
}

// Get published blogs
$blogSql = "
    SELECT blog_id, title, slug, content, image, created_at
    FROM blogs
    WHERE status = 'Published'
    ORDER BY created_at DESC
    LIMIT 3
";

$blogResult = $conn->query($blogSql);

if (!$blogResult) {
    die("Unable to load blogs.");
}

// Website header
require_once "includes/header.php";
?>

<style>
    /* Bottom View All Button */
    .view-all-container {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
    }

    .view-all-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #176b50;
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .view-all-link .arrow-icon {
        font-size: 15px;
        transition: transform 0.3s ease;
    }

    .view-all-link:hover {
        background: #26775b;
        color: #ffffff;
    }

    .view-all-link:hover .arrow-icon {
        transform: translateX(4px);
    }

    @media (max-width: 600px) {
        .view-all-container {
            justify-content: center;
        }
    }
</style>


<!-- HERO SECTION -->
<section class="hero" id="home">

    <div class="hero-content">

        <p class="hero-tag">PROTECT MARINE LIFE</p>

        <h1>
            Protect Our Ocean,<br>
            Save Our Sea Turtles
        </h1>

        <p>
            Discover sea turtle conservation activities,
            explore educational tours and learn more
            about protecting marine life.
        </p>

        <div class="hero-buttons">

            <a href="<?= $base ?>/pages/about.php"
               class="btn-primary">
                Explore More
            </a>

            <a href="<?= $base ?>/pages/booking.php"
               class="btn-outline">
                Book a Visit
            </a>

        </div>

    </div>

</section>


<!-- ABOUT SECTION -->
<section class="section" id="about">

    <div class="container about-grid">

        <div class="about-image">

            <img
                src="<?= $base ?>/assets/images/turtle-about.jpg"
                alt="Sea turtle swimming in the ocean"
            >

        </div>

        <div class="about-content">

            <p class="section-tag">ABOUT OUR CENTRE</p>

            <h2>
                Working Together to Protect Sea Turtles
            </h2>

            <p>
                Learn about sea turtles, marine ecosystems
                and the importance of protecting these
                beautiful creatures.
            </p>

            <a href="<?= $base ?>/pages/about.php"
               class="btn-primary">
                Learn More →
            </a>

        </div>

    </div>

</section>


<!-- DATABASE TOURS SECTION -->
<section class="section" id="tours">

    <div class="container">

        <div class="section-heading">

            <p class="section-tag">EXPLORE AND LEARN</p>

            <h2>Our Available Tours</h2>

            <p>
                Explore the educational tours currently
                available for visitors.
            </p>

        </div>


        <!-- Tour Cards -->
        <div class="card-grid">

            <?php if ($tourResult->num_rows > 0): ?>

                <?php while ($tour = $tourResult->fetch_assoc()): ?>

                    <div class="info-card">

                        <?php
                        $tourImage = "";

                        if (!empty($tour['image'])) {
                            $tourImage = $base . "/" .
                                implode(
                                    "/",
                                    array_map(
                                        "rawurlencode",
                                        explode("/", ltrim($tour['image'], "/"))
                                    )
                                );
                        }
                        ?>

                        <?php if (!empty($tourImage)): ?>

                            <img
                                src="<?= htmlspecialchars($tourImage) ?>"
                                alt="<?= htmlspecialchars($tour['tour_name']) ?>"
                            >

                        <?php endif; ?>

                        <div class="card-content">

                            <h3>
                                <?= htmlspecialchars($tour['tour_name']) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars($tour['description'] ?? '')) ?>
                            </p>

                            <?php if (!empty($tour['duration'])): ?>

                                <p>
                                    <strong>Duration:</strong>
                                    <?= htmlspecialchars($tour['duration']) ?>
                                </p>

                            <?php endif; ?>

                            <p>
                                <strong>Price:</strong>
                                Rs. <?= number_format((float)$tour['price'], 2) ?>
                            </p>

                            <p>
                                <strong>Maximum Visitors:</strong>
                                <?= (int)$tour['max_visitors'] ?>
                            </p>

                            <a
                                href="<?= $base ?>/pages/booking.php?tour_id=<?= (int)$tour['tour_id'] ?>"
                                class="btn-primary"
                            >
                                Book This Tour
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="info-card">

                    <div class="card-content">

                        <h3>No Tours Available</h3>

                        <p>
                            There are currently no active tours.
                            Please check again later.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <!-- View All Tours Button at Bottom Right -->
        <div class="view-all-container">

            <a href="<?= $base ?>/pages/tours.php"
               class="view-all-link">

                View All Tours

                <span class="arrow-icon" aria-hidden="true">→</span>

            </a>

        </div>

    </div>

</section>


<!-- DATABASE BLOG SECTION -->
<section class="section light-bg" id="blogs">

    <div class="container">

        <div class="section-heading">

            <p class="section-tag">LATEST UPDATES</p>

            <h2>Latest Sea Turtle Blogs</h2>

            <p>
                Read the latest published articles
                about sea turtles and conservation.
            </p>

        </div>


        <!-- Blog Cards -->
        <div class="card-grid">

            <?php if ($blogResult->num_rows > 0): ?>

                <?php while ($blog = $blogResult->fetch_assoc()): ?>

                    <div class="info-card">

                        <?php
                        $blogImage = "";

                        if (!empty($blog['image'])) {

                            $blogImage = $base .
                                "/uploads/blogs/" .
                                rawurlencode(basename($blog['image']));
                        }
                        ?>

                        <?php if (!empty($blogImage)): ?>

                            <img
                                src="<?= htmlspecialchars($blogImage) ?>"
                                alt="<?= htmlspecialchars($blog['title']) ?>"
                            >

                        <?php endif; ?>

                        <div class="card-content">

                            <h3>
                                <?= htmlspecialchars($blog['title']) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars(
                                    mb_strimwidth(
                                        $blog['content'],
                                        0,
                                        150,
                                        "..."
                                    )
                                )) ?>
                            </p>

                            <p>
                                <small>
                                    Published:

                                    <?= date(
                                        "d M Y",
                                        strtotime($blog['created_at'])
                                    ) ?>
                                </small>
                            </p>

                            <a
                                href="<?= $base ?>/pages/blog-details.php?slug=<?= urlencode($blog['slug']) ?>"
                                class="btn-primary"
                            >
                                Read More
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="info-card">

                    <div class="card-content">

                        <h3>No Published Blogs</h3>

                        <p>
                            There are currently no published
                            blog posts available.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <!-- View All Blogs Button at Bottom Right -->
        <div class="view-all-container">

            <a href="<?= $base ?>/pages/blogs.php"
               class="view-all-link">

                View All Blogs

                <span class="arrow-icon" aria-hidden="true">→</span>

            </a>

        </div>

    </div>

</section>


<!-- CONTACT SECTION -->
<section class="section contact-section" id="contact">

    <div class="container contact-content">

        <p class="section-tag">GET IN TOUCH</p>

        <h2>Have Questions About Sea Turtles?</h2>

        <p>
            Contact us to learn more about conservation
            awareness and visitor information.
        </p>

        <a href="<?= $base ?>/pages/contact.php"
           class="btn-primary">
            Contact Us
        </a>

    </div>

</section>


<?php
// Free database results
$tourResult->free();
$blogResult->free();

// Website footer
require_once "includes/footer.php";
?>
