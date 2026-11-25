
<?php
session_start();

require_once "../config/database.php";

$success = "";
$error = "";

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check CSRF token
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {

        $error = "Invalid form submission. Please try again.";

    } else {

        // Get form values
        $full_name = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";

        // Validate inputs
        if (
            $full_name === "" ||
            $email === "" ||
            $phone === "" ||
            $password === "" ||
            $confirm_password === ""
        ) {

            $error = "Please fill in all required fields.";

        } elseif (strlen($full_name) > 100) {

            $error = "Name is too long.";

        // Validate email length
        } elseif (strlen($email) > 255) {

            $error = "Email address is too long.";

        // Validate email format
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email)
        ) {

            $error = "Please enter a valid email address.";

        // Validate Sri Lankan phone number
        } elseif (
            !preg_match('/^(?:0\d{9}|\+94\d{9}|94\d{9})$/', $phone)
        ) {

            $error = "Please enter a valid Sri Lankan phone number (e.g. 0771234567 or +94771234567).";

        } elseif (strlen($password) < 8) {

            $error = "Password must contain at least 8 characters.";

        } elseif (strlen($password) > 72) {

            $error = "Password is too long.";

        } elseif ($password !== $confirm_password) {

            $error = "Passwords do not match.";

        } else {

            // Check whether email already exists
            $check_sql = "SELECT user_id FROM users WHERE email = ?";

            $check_stmt = $conn->prepare($check_sql);

            if (!$check_stmt) {

                $error = "Database query error.";

            } else {

                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {

                    $error = "This email address is already registered.";

                } else {

                    // Hash password
                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    // Insert visitor into database
                    $insert_sql = "INSERT INTO users
                    (full_name, email, phone, password)
                    VALUES (?, ?, ?, ?)";

                    $insert_stmt = $conn->prepare($insert_sql);

                    if (!$insert_stmt) {

                        $error = "Unable to prepare registration.";

                    } else {

                        $insert_stmt->bind_param(
                            "ssss",
                            $full_name,
                            $email,
                            $phone,
                            $hashed_password
                        );

                        if ($insert_stmt->execute()) {

                            $success = "Registration successful! You can now log in.";

                            // Generate a new CSRF token
                            $_SESSION["csrf_token"] =
                                bin2hex(random_bytes(32));

                        } else {

                            $error = "Registration failed. Please try again.";

                        }

                        $insert_stmt->close();
                    }
                }

                $check_stmt->close();
            }
        }
    }
}
?>

<?php
$pageTitle = "Visitor Registration | Sea Turtle Conservation";
require_once "../includes/header.php";
?>

<section class="booking-section">

    <div class="booking-container">

        <div class="booking-intro">

            <h2>Create Your Visitor Account</h2>

            <p>
                Register with us to enjoy our sea turtle
                conservation visitor services.
            </p>

        </div>

        <?php if ($success !== ""): ?>

            <p class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </p>

        <?php endif; ?>

        <?php if ($error !== ""): ?>

            <p class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php endif; ?>

        <?php if ($success === ""): ?>

        <form class="booking-form"
              action="register.php"
              method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
            >

            <!-- Full Name -->
            <div class="form-group">

                <label for="full_name">Full Name</label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    maxlength="100"
                    autocomplete="name"
                    value="<?php echo htmlspecialchars($_POST["full_name"] ?? ""); ?>"
                    required
                >

            </div>

            <!-- Email Address -->
            <div class="form-group">

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    maxlength="255"
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

            </div>

<!-- Contact Number -->
<div class="form-group">

    <label for="phone">Contact Number</label>

    <input
        type="tel"
        id="phone"
        name="phone"
        placeholder="0771234567 or +94771234567"
        maxlength="12"
        pattern="(0[0-9]{9}|\+94[0-9]{9}|94[0-9]{9})"
        title="Enter a valid Sri Lankan phone number."
        autocomplete="tel"
        value="<?php echo htmlspecialchars($_POST["phone"] ?? ""); ?>"
        oninput="this.value = this.value.replace(/[^0-9+]/g, '').replace(/(?!^)\+/g, '');"
        required
    >

</div>

            <!-- Password -->
            <div class="form-group">

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >

            </div>

            <!-- Confirm Password -->
            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >

            </div>

            <!-- Submit Button -->
            <button type="submit" class="booking-submit">
                Create Account
            </button>

        </form>

        <?php endif; ?>

        <p class="booking-note">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </div>

</section>

<?php require_once "../includes/footer.php"; ?>
