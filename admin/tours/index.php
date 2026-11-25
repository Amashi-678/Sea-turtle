
<?php
session_start();

require_once "../../config/database.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get all tours
$sql = "SELECT * FROM tours ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour Management - Turtle Haven</title>

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

        .top-bar {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 9px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 3px;
        }

        .add {
            background: #198754;
        }

        .back {
            background: #244b3c;
        }

        .edit {
            background: #0d6efd;
        }

        .delete {
            background: #dc3545;
        }

        .activate {
            background: #198754;
        }

        .deactivate {
            background: #6c757d;
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

        .active {
            color: green;
            font-weight: bold;
        }

        .inactive {
            color: red;
            font-weight: bold;
        }

        .description {
            max-width: 220px;
            overflow-wrap: anywhere;
        }
    </style>
</head>

<body>

<h2>Tour Management</h2>

<div class="top-bar">

    <a href="../dashboard.php" class="btn back">
        Back to Dashboard
    </a>

    <a href="add.php" class="btn add">
        + Add New Tour
    </a>

</div>

<table>

    <tr>
        <th>Tour ID</th>
        <th>Tour Name</th>
        <th>Description</th>
        <th>Duration</th>
        <th>Price (Rs.)</th>
        <th>Max Visitors</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($row['tour_id']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['tour_name']) ?>
                </td>

                <td class="description">
                    <?= htmlspecialchars($row['description'] ?? '') ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['duration'] ?? '') ?>
                </td>

                <td>
                    <?= number_format((float)$row['price'], 2) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['max_visitors']) ?>
                </td>

                <td class="<?= htmlspecialchars($row['status']) ?>">
                    <?= ucfirst(htmlspecialchars($row['status'])) ?>
                </td>

                <td>

                    <a href="edit.php?id=<?= (int)$row['tour_id'] ?>"
                       class="btn edit">
                       Edit
                    </a>

                    <form action="delete.php" method="POST"
                          style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to delete this tour?');">

                        <input type="hidden" name="tour_id"
                               value="<?= (int)$row['tour_id'] ?>">

                        <button type="submit" class="btn delete"
                                style="border:none; cursor:pointer;">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        <?php endwhile; ?>

    <?php else: ?>

        <tr>
            <td colspan="8">No tours found.</td>
        </tr>

    <?php endif; ?>

</table>

</body>
</html>
