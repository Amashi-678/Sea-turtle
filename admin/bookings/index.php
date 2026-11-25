
<?php
session_start();

require_once "../../config/database.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Update booking status
if (isset($_GET['action'], $_GET['id'])) {

    $booking_id = (int) $_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['confirmed', 'cancelled'])) {

        $stmt = $conn->prepare(
            "UPDATE bookings
             SET booking_status = ?
             WHERE booking_id = ?"
        );

        $stmt->bind_param("si", $action, $booking_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit();
}

// Get all bookings
$sql = "
SELECT
    bookings.booking_id,
    users.full_name,
    users.email,
    users.phone,
    tours.tour_name,

    bookings.visit_date,
    bookings.number_of_visitors,
    bookings.special_request,
    bookings.booking_status,
    bookings.created_at

FROM bookings

INNER JOIN users
    ON bookings.user_id = users.user_id

INNER JOIN tours
    ON bookings.tour_id = tours.tour_id

ORDER BY bookings.created_at DESC;
";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        h2 {
            color: #244b3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #244b3c;
            color: white;
        }

        .status {
            font-weight: bold;
        }

        .pending {
            color: orange;
        }

        .confirmed {
            color: green;
        }

        .cancelled {
            color: red;
        }

        a {
            text-decoration: none;
            padding: 7px 10px;
            color: white;
            border-radius: 4px;
        }

        .confirm {
            background: green;
        }

        .cancel {
            background: #d9534f;
        }

        .back {
            background: #244b3c;
        }
    </style>
</head>

<body>

<h2>Booking Management</h2>

<a href="../dashboard.php" class="back">Back to Dashboard</a>

<table>

<tr>
    <th>Booking ID</th>
    <th>Customer Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Tour Name</th>

    <th>Visit Date</th>
    <th>Visitors</th>
    <th>Special Request</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

 <tr>
    <td><?= htmlspecialchars($row['booking_id']) ?></td>

    <td><?= htmlspecialchars($row['full_name']) ?></td>

    <td><?= htmlspecialchars($row['email']) ?></td>

    <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>

    <td><?= htmlspecialchars($row['tour_name']) ?></td>



    <td><?= htmlspecialchars($row['visit_date']) ?></td>

    <td><?= htmlspecialchars($row['number_of_visitors']) ?></td>

    <td><?= htmlspecialchars($row['special_request'] ?? '') ?></td>

    <td class="status <?= htmlspecialchars($row['booking_status']) ?>">
        <?= ucfirst(htmlspecialchars($row['booking_status'])) ?>
    </td>

    <td>
        <?php if ($row['booking_status'] == 'pending'): ?>

            <a class="confirm"
               href="index.php?action=confirmed&id=<?= $row['booking_id'] ?>">
               Confirm
            </a>

            <a class="cancel"
               href="index.php?action=cancelled&id=<?= $row['booking_id'] ?>">
               Cancel
            </a>

        <?php else: ?>
            No Actions
        <?php endif; ?>
    </td>
</tr>
        <?php endwhile; ?>

    <?php else: ?>

<tr>
    <td colspan="11">No bookings found.</td>
</tr>

    <?php endif; ?>

</table>

</body>
</html>
