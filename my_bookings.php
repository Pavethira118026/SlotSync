<?php
session_start();

/* DATABASE CONNECTION */
$conn = new mysqli(
    "db",
    "slotsync_user",
    "slotsync_password",
    "slotsync"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

/* GET USER BOOKINGS */
$stmt = $conn->prepare("
    SELECT
        bookings.id,
        rooms.name AS room_name,
        bookings.booking_date,
        bookings.start_time,
        bookings.end_time,
        bookings.duration,
        bookings.total_amount,
        bookings.booking_status
    FROM bookings
    INNER JOIN rooms ON bookings.room_id = rooms.id
    WHERE bookings.user_id = ?
    ORDER BY bookings.booking_date DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings - SlotSync</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 14px;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-confirmed {
            padding: 6px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-weight: bold;
        }

        .status-cancelled {
            padding: 6px 12px;
            border-radius: 20px;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 22px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .cancel-btn {
            display: inline-block;
            padding: 8px 14px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .cancel-btn:hover {
            background: #b91c1c;
        }

        .empty {
            text-align: center;
            padding: 30px;
            font-size: 18px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>My Bookings</h1>

    <?php if (isset($_GET["message"]) && $_GET["message"] === "cancelled"): ?>

        <p style="
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        ">
            Booking cancelled successfully.
        </p>

    <?php endif; ?>


    <?php if ($result->num_rows > 0): ?>

        <table>

            <tr>
                <th>Room</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>


            <?php while ($booking = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars($booking["room_name"]);
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars($booking["booking_date"]);
                        ?>
                    </td>

                    <td>
                        <?php
                        echo date(
                            "h:i A",
                            strtotime($booking["start_time"])
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo date(
                            "h:i A",
                            strtotime($booking["end_time"])
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars($booking["duration"]);
                        ?>
                        hour(s)
                    </td>

                    <td>
                        ₹<?php
                        echo number_format(
                            $booking["total_amount"],
                            2
                        );
                        ?>
                    </td>


                    <!-- BOOKING STATUS -->

                    <td>

                        <?php if ($booking["booking_status"] === "Confirmed"): ?>

                            <span class="status-confirmed">
                                Confirmed
                            </span>

                        <?php else: ?>

                            <span class="status-cancelled">
                                Cancelled
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- CANCEL ACTION -->

                    <td>

                        <?php if ($booking["booking_status"] === "Confirmed"): ?>

                            <a
                                href="cancel_booking.php?id=<?php echo $booking["id"]; ?>"
                                class="cancel-btn"
                                onclick="return confirm('Are you sure you want to cancel this booking?');"
                            >
                                Cancel
                            </a>

                        <?php else: ?>

                            Cancelled

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        </table>


    <?php else: ?>

        <div class="empty">

            <p>No bookings found.</p>

            <a href="rooms.php" class="btn">
                Book a Room
            </a>

        </div>

    <?php endif; ?>


    <a href="rooms.php" class="btn">
        ← Back to Rooms
    </a>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>