
<?php
session_start();

require_once "../config/database.php";

$error = "";

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Invalid form submission. Please try again.";

    } else {

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        // Validate input
        if ($email === "" || $password === "") {

            $error = "Please enter your email and password.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        } else {

            // =====================================
            // FIRST: CHECK ADMIN ACCOUNT
            // =====================================

            $sql = "SELECT admin_id, username, email, password
                    FROM admins
                    WHERE email = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $error = "Database query error.";

            } else {

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $admin = $result->fetch_assoc();

                    // Verify admin password
                    if (password_verify($password, $admin["password"])) {

                        // Regenerate session ID
                        session_regenerate_id(true);

                        // Remove visitor session
                        unset(
                            $_SESSION["user_id"],
                            $_SESSION["user_name"],
                            $_SESSION["user_email"]
                        );

                        // Store admin information
                        $_SESSION["admin_id"] = $admin["admin_id"];
                        $_SESSION["admin_username"] = $admin["username"];
                        $_SESSION["admin_email"] = $admin["email"];

                        // Generate logout CSRF token
                        $_SESSION["admin_logout_csrf"] =
                            bin2hex(random_bytes(32));

                        // Generate new form CSRF token
                        $_SESSION["csrf_token"] =
                            bin2hex(random_bytes(32));

                        // Redirect to Admin Dashboard
                        header("Location: ../admin/dashboard.php");
                        exit();

                    } else {

                        $error = "Incorrect email address or password.";

                    }

                } else {

                    // =====================================
                    // SECOND: CHECK VISITOR ACCOUNT
                    // =====================================

                    $sql = "SELECT user_id, full_name, email, password
                            FROM users
                            WHERE email = ?
                            LIMIT 1";

                    $userStmt = $conn->prepare($sql);

                    if (!$userStmt) {

                        $error = "Database query error.";

                    } else {

                        $userStmt->bind_param("s", $email);
                        $userStmt->execute();

                        $userResult = $userStmt->get_result();

                        if ($userResult->num_rows === 1) {

                            $user = $userResult->fetch_assoc();

                            // Verify visitor password
                            if (password_verify($password, $user["password"])) {

                                // Regenerate session ID
                                session_regenerate_id(true);

                                // Remove admin session
                                unset(
                                    $_SESSION["admin_id"],
                                    $_SESSION["admin_username"],
                                    $_SESSION["admin_email"],
                                    $_SESSION["admin_logout_csrf"]
                                );

                                // Store visitor information
                                $_SESSION["user_id"] = $user["user_id"];
                                $_SESSION["user_name"] = $user["full_name"];
                                $_SESSION["user_email"] = $user["email"];

                                // Generate new CSRF token
                                $_SESSION["csrf_token"] =
                                    bin2hex(random_bytes(32));

                                // Redirect to Visitor Website
                                header("Location: ../index.php");
                                exit();

                            } else {

                                $error = "Incorrect email address or password.";

                            }

                        } else {

                            $error = "Incorrect email address or password.";

                        }

                        $userStmt->close();
                    }
                }

                $stmt->close();
            }
        }
    }
}
?>

<?php
$pageTitle = "Login | Sea Turtle Conservation";
require_once "../includes/header.php";
?>

<section class="booking-section">

    <div class="booking-container">

        <div class="booking-intro">

            <h2>Welcome Back!</h2>

            <p>
                Log in to your Sea Turtle account.
            </p>

        </div>

        <?php if ($error !== ""): ?>

            <p class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php endif; ?>

        <form class="booking-form"
              action="login.php"
              method="POST">

            <input type="hidden"
                   name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

            <div class="form-group">

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    autocomplete="email"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button type="submit" class="booking-submit">
                Login
            </button>

        </form>

        <p class="booking-note">

            Don't have an account?

            <a href="register.php">Register here</a>

        </p>

    </div>

</section>

<?php require_once "../includes/footer.php"; ?>
