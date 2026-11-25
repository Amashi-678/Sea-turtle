
<?php
$pageTitle = "Tours & Booking | Sea Turtle Conservation";

require_once "../config/database.php";
require_once "../includes/header.php";

// Get active tours from database
$sql = "SELECT *
        FROM tours
        WHERE status = 'active'
        ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<!-- PAGE BANNER -->
<section class="about-banner banner-tours">

    <div class="container">
        <p>EXPLORE AND LEARN</p>

        <h1>Tours &amp; Booking</h1>

        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span>/</span>
            <span>Tours &amp; Booking</span>
        </div>
    </div>

</section>


<!-- INTRODUCTION -->
<section class="section">
    <div class="container tours-intro">

        <p class="section-tag">PLAN YOUR VISIT</p>

        <h2>Explore Our Educational Tours</h2>

        <p>
            Discover the fascinating world of sea turtles through
            educational visitor experiences. Learn about different
            turtle species, conservation awareness and the importance
            of protecting marine environments.
        </p>

    </div>
</section>


<!-- DYNAMIC TOUR CARDS -->
<section class="section light-bg">

    <div class="container">

        <div class="section-heading">
            <p class="section-tag">OUR VISITOR EXPERIENCES</p>

            <h2>Choose Your Tour</h2>

            <p>
                Explore our educational tour options and plan
                your visit.
            </p>
        </div>

        <div class="card-grid tours-grid">

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($tour = $result->fetch_assoc()): ?>

                    <div class="info-card tour-card">

                        <?php
                        $image = $tour['image'] ?? '';

                        $imagePath = "../" . $image;

                        if (!empty($image) && is_file(__DIR__ . "/../" . $image)):
                        ?>

                            <img
                                src="<?= htmlspecialchars($imagePath) ?>"
                                alt="<?= htmlspecialchars($tour['tour_name']) ?>"
                            >

                        <?php else: ?>

                            <img
                                src="../assets/images/tour-1.jpg"
                                alt="Sea turtle educational experience"
                            >

                        <?php endif; ?>


                        <div class="card-content">

                            <span class="tour-label">
                                EDUCATIONAL TOUR
                            </span>

                            <h3>
                                <?= htmlspecialchars($tour['tour_name']) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars($tour['description'] ?? '')) ?>
                            </p>

                            <div class="tour-details">

                                <span>📍 Ahungalla</span>

                                <span>
                                    ⏱ Duration:
                                    <?= htmlspecialchars($tour['duration'] ?? 'Contact Centre') ?>
                                </span>

                                <span>
                                    👥 Maximum Visitors:
                                    <?= (int)$tour['max_visitors'] ?>
                                </span>

                            </div>

                            <div class="tour-price">

                                <strong>
                                    Price: Rs.
                                    <?= number_format((float)$tour['price'], 2) ?>
                                </strong>

                            </div>

                            <a
                                href="booking.php?tour_id=<?= (int)$tour['tour_id'] ?>"
                                class="btn-primary tour-button"
                            >
                                Book This Tour
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="no-tours">

                    <h3>No Tours Available</h3>

                    <p>
                        Currently, there are no active tours available.
                        Please check again later.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- BOOKING INFORMATION -->
<section class="section booking-info">

    <div class="container booking-info-content">

        <div>
            <p class="section-tag">EASY ONLINE BOOKING</p>

            <h2>Plan Your Sea Turtle Visit Today</h2>

            <p>
                Choose an available tour, select your preferred
                visiting date and submit your booking request
                through our online booking system.
            </p>
        </div>

        <a href="booking.php" class="btn-primary">
            Start Booking →
        </a>

    </div>

</section>


<!-- VISITOR GUIDELINES -->
<section class="section">

    <div class="container visitor-info">

        <div class="section-heading">
            <p class="section-tag">BEFORE YOUR VISIT</p>
            <h2>Responsible Visitor Guidelines</h2>
        </div>

        <div class="guideline-list">

            <div class="guideline-item">
                <span>✓</span>
                Respect sea turtles and their natural environment.
            </div>

            <div class="guideline-item">
                <span>✓</span>
                Follow the instructions of authorized staff.
            </div>

            <div class="guideline-item">
                <span>✓</span>
                Avoid touching or disturbing wildlife.
            </div>

            <div class="guideline-item">
                <span>✓</span>
                Keep the environment clean.
            </div>

        </div>

    </div>

</section>


<?php require_once "../includes/footer.php"; ?>
