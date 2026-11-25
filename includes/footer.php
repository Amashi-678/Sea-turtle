<?php
// includes/footer.php  -  shared footer for every page
if (!isset($base)) {
    $projectRoot = str_replace("\\", "/", realpath(__DIR__ . "/.."));
    $docRoot     = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"]));
    $base        = rtrim(substr($projectRoot, strlen($docRoot)), "/");
}
?>

<!-- FOOTER -->
<footer class="footer">

    <div class="container footer-content">

        <div>
            <h3>🐢 Sea Turtle Conservation</h3>
            <p>
                Protecting sea turtles through awareness,
                education and conservation.
            </p>
        </div>

        <div>
            <h4>Quick Links</h4>
            <a href="<?= $base ?>/index.php">Home</a>
            <a href="<?= $base ?>/pages/about.php">About Us</a>
            <a href="<?= $base ?>/pages/blogs.php">Blogs</a>
            <a href="<?= $base ?>/pages/tours.php">Tours &amp; Booking</a>
            <a href="<?= $base ?>/pages/contact.php">Contact Us</a>
        </div>

        <div>
            <h4>Contact</h4>
            <p>Ahungalla, Sri Lanka</p>
            <p>Sea Turtle Conservation Centre</p>
            <a href="<?= $base ?>/pages/contact.php">Send an Inquiry</a>
        </div>

    </div>

    <div class="copyright">
        <p>&copy; <?= date("Y") ?> Sea Turtle Conservation Centre. All Rights Reserved.</p>
    </div>

</footer>

</body>
</html>
