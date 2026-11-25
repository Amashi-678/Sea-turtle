
<?php

// Protect the booking page
require_once "../includes/auth_user.php";

// Connect to database
require_once "../config/database.php";

$success = "";
$error = "";

// Get active tours from database
$tour_sql = "SELECT tour_id, tour_name, max_visitors
             FROM tours
             WHERE status = 'active'
             ORDER BY tour_name ASC";

$tour_result = $conn->query($tour_sql);

if (!$tour_result) {
    die("Unable to load tours.");
}

// Process booking form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check CSRF token
    if (
        !isset($_POST["csrf_token"]) ||
        !isset($_SESSION["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {

        $error = "Invalid form submission. Please try again.";

    } else {

        // Get form values
        $user_id = (int) $_SESSION["user_id"];

        $tour_id = filter_var(
            $_POST["tour_id"] ?? "",
            FILTER_VALIDATE_INT
        );

        $visit_date = trim($_POST["visit_date"] ?? "");

        $number_of_visitors = filter_var(
            $_POST["number_of_visitors"] ?? "",
            FILTER_VALIDATE_INT
        );

        $special_request = trim(
            $_POST["special_request"] ?? ""
        );

        // Validate tour ID
        if ($tour_id === false || $tour_id < 1) {

            $error = "Please select a valid tour.";

        // Validate visit date
        } elseif (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date) ||
            !checkdate(
                (int) substr($visit_date, 5, 2),
                (int) substr($visit_date, 8, 2),
                (int) substr($visit_date, 0, 4)
            ) ||
            $visit_date <= date("Y-m-d")
        ) {

            $error = "Please select a date from tomorrow onwards.";

        // Validate number of visitors
        } elseif (
            $number_of_visitors === false ||
            $number_of_visitors < 1 ||
            $number_of_visitors > 30
        ) {

            $error = "Number of visitors must be between 1 and 30.";

        // Validate additional message
        } elseif (strlen($special_request) > 5000) {

            $error = "Your additional message is too long.";

        } else {

            // Check selected tour
            $check_sql = "SELECT tour_id, max_visitors
                          FROM tours
                          WHERE tour_id = ?
                          AND status = 'active'";

            $check_stmt = $conn->prepare($check_sql);

            if (!$check_stmt) {

                $error = "Database query error.";

            } else {

                $check_stmt->bind_param("i", $tour_id);
                $check_stmt->execute();

                $tour_data = $check_stmt->get_result();

                if ($tour_data->num_rows !== 1) {

                    $error = "Selected tour is unavailable.";

                } else {

                    $selected_tour = $tour_data->fetch_assoc();

                    // Check maximum visitor limit of selected tour
                    if (
                        $number_of_visitors >
                        (int) $selected_tour["max_visitors"]
                    ) {

                        $error = "The selected tour allows a maximum of "
                               . (int) $selected_tour["max_visitors"]
                               . " visitors.";

                    } else {

                        // Insert booking into database
                        $insert_sql = "INSERT INTO bookings
                        (
                            user_id,
                            tour_id,
                            visit_date,
                            number_of_visitors,
                            special_request
                        )
                        VALUES (?, ?, ?, ?, ?)";

                        $insert_stmt = $conn->prepare($insert_sql);

                        if (!$insert_stmt) {

                            $error = "Unable to prepare booking.";

                        } else {

                            $insert_stmt->bind_param(
                                "iisis",
                                $user_id,
                                $tour_id,
                                $visit_date,
                                $number_of_visitors,
                                $special_request
                            );

                            if ($insert_stmt->execute()) {

                                $success = "Your booking request was submitted successfully!";

                                // Generate a new CSRF token
                                $_SESSION["csrf_token"] =
                                    bin2hex(random_bytes(32));

                            } else {

                                $error = "Booking failed. Please try again.";

                            }

                            $insert_stmt->close();
                        }
                    }
                }

                $check_stmt->close();
            }
        }
    }
}

?>

<?php

$pageTitle = "Book a Visit | Sea Turtle Conservation";

require_once "../includes/header.php";

?>

<section class="booking-banner">

    <div class="container">

        <h2>Book Your Visit</h2>

        <p>
            Send us a request to plan your sea turtle learning experience.
        </p>

    </div>

</section>


<section class="booking-section">

    <div class="booking-container">

        <div class="booking-intro">

            <h2>Online Booking Form</h2>

            <p>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["user_name"]); ?>.
                Please complete your booking details.
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
              action="booking.php"
              method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
            >


            <!-- Select Tour -->
            <div class="form-group">

                <label for="tour_id">Select a Tour</label>

                <select
                    id="tour_id"
                    name="tour_id"
                    required
                >

                    <option value="">-- Select a Tour --</option>

                    <?php while ($tour = $tour_result->fetch_assoc()): ?>

                        <option
                            value="<?php echo (int) $tour["tour_id"]; ?>"
                            <?php
                            if (
                                isset($_POST["tour_id"]) &&
                                (string) $_POST["tour_id"] ===
                                (string) $tour["tour_id"]
                            ) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php echo htmlspecialchars($tour["tour_name"]); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Preferred Visit Date -->
            <div class="form-group">

                <label for="visit_date">Preferred Visit Date</label>

                <input
                    type="date"
                    id="visit_date"
                    name="visit_date"
                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                    value="<?php echo htmlspecialchars($_POST["visit_date"] ?? ""); ?>"
                    required
                >

            </div>


            <!-- Number of Visitors -->
            <div class="form-group">

                <label for="number_of_visitors">
                    Number of Visitors
                </label>

                <input
                    type="number"
                    id="number_of_visitors"
                    name="number_of_visitors"
                    min="1"
                    max="30"
                    value="<?php echo htmlspecialchars($_POST["number_of_visitors"] ?? "1"); ?>"
                    required
                >

            </div>


            <!-- Additional Message -->
            <div class="form-group">

                <label for="special_request">
                    Additional Message
                </label>

                <textarea
                    id="special_request"
                    name="special_request"
                    rows="5"
                    maxlength="5000"
                    placeholder="Write any additional requests here..."
                ><?php echo htmlspecialchars($_POST["special_request"] ?? ""); ?></textarea>

            </div>


            <!-- Submit Button -->
            <button
                type="submit"
                class="booking-submit"
                <?php echo $tour_result->num_rows === 0 ? "disabled" : ""; ?>
            >

                Submit Booking Request

            </button>


            <?php if ($tour_result->num_rows === 0): ?>

                <p class="error-message">
                    No active tours are currently available.
                </p>

            <?php endif; ?>

        </form>

        <?php endif; ?>


        <p class="booking-note">

            Submitting this form is a booking request,
            not a confirmed reservation.

        </p>

    </div>

</section>


<?php require_once "../includes/footer.php"; ?>
