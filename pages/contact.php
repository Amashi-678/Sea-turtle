
<?php
session_start();

$name = "";
$email = "";
$subject = "";
$message = "";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (
        empty($name) ||
        empty($email) ||
        empty($subject) ||
        empty($message)
    ) {
        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";

    } elseif (
        strlen($name) > 100 ||
        strlen($email) > 150 ||
        strlen($subject) > 200 ||
        strlen($message) > 5000
    ) {
        $error = "Some fields exceed the allowed length.";

    } else {

        // Receiver email address
        $receiver = "tharushinirmani422@gmail.com";

        // Email subject
        $emailSubject = "Turtle Haven Contact: " . $subject;

        // Email message
        $emailBody =
            "Dear Turtle Haven Team,\n\n" .
            "Visitor Name: " . $name . "\n" .
            "Visitor Email: " . $email . "\n" .
            "Subject: " . $subject . "\n\n" .
            "Message:\n" . $message . "\n\n" .
            "Thank you.";

        // Open Gmail Compose with pre-filled details
        $gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1" .
            "&to=" . rawurlencode($receiver) .
            "&su=" . rawurlencode($emailSubject) .
            "&body=" . rawurlencode($emailBody);

        header("Location: " . $gmailUrl);
        exit;
    }
}

$pageTitle = "Contact Us | Sea Turtle Conservation";

require_once __DIR__ . "/../includes/header.php";
?>

<section class="contact-section">

    <div class="contact-container">

        <div class="contact-info">

            <h1>Contact Us</h1>

            <p>
                Have questions about sea turtles or our conservation
                activities? Feel free to contact us.
            </p>

            <div class="contact-details">

                <h3>Our Location</h3>
                <p>Ahungalla, Sri Lanka</p>

                <h3>Email</h3>
                <p>tharushinirmani422@gmail.com</p>

                <h3>Opening Hours</h3>
                <p>Monday - Sunday</p>

            </div>

        </div>

        <div class="contact-form-box">

            <h2>Send Us a Message</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <label for="name">Full Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="Enter your name"
                    maxlength="100"
                    required
                >

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Enter your email"
                    maxlength="150"
                    required
                >

                <label for="subject">Subject</label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= htmlspecialchars($subject) ?>"
                    placeholder="Enter subject"
                    maxlength="200"
                    required
                >

                <label for="message">Your Message</label>

                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    maxlength="5000"
                    placeholder="Write your message here..."
                    required
                ><?= htmlspecialchars($message) ?></textarea>

                <button type="submit">
                    Send Message
                </button>

            </form>

        </div>

    </div>

</section>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
