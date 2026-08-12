<?php
session_start();

include "config/db.php";

/* Check user login */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* Check booking ID */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid booking ID.");
}

$booking_id = intval($_GET['id']);


/* Get booking details */

$sql = "SELECT
            bookings.id,
            bookings.booking_date,
            bookings.start_time,
            bookings.end_time,
            bookings.total_hours,
            bookings.total_amount,
            bookings.booking_status,
            bookings.created_at,

            rooms.room_name,
            rooms.room_type,
            rooms.price_per_hour,
            rooms.description,
            rooms.image

        FROM bookings

        INNER JOIN rooms
            ON bookings.room_id = rooms.id

        WHERE bookings.id = ?
        AND bookings.user_id = ?";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $booking_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


/* Check booking */

if ($result->num_rows == 0) {
    die("Booking not found.");
}


$booking = $result->fetch_assoc();

$stmt->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Booking Details - SlotSync</title>


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


body {

    font-family: Arial, sans-serif;

    background: #f1f5f9;

    color: #1e293b;

}


/* HEADER */

header {

    background: #0f172a;

    color: white;

    padding: 20px 5%;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.logo {

    font-size: 27px;

    font-weight: bold;

}


nav {

    display: flex;

    gap: 25px;

}


nav a {

    color: white;

    text-decoration: none;

    font-weight: bold;

}


nav a:hover {

    color: #38bdf8;

}


/* TITLE */

.page-title {

    text-align: center;

    padding: 40px 20px 25px;

}


.page-title h1 {

    font-size: 34px;

    color: #0f172a;

}


.page-title p {

    margin-top: 10px;

    color: #64748b;

}


/* MAIN CARD */

.details-container {

    width: 90%;

    max-width: 900px;

    margin: 20px auto 60px;

    background: white;

    border-radius: 15px;

    padding: 30px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);

}


/* ROOM IMAGE */

.room-image {

    text-align: center;

    margin-bottom: 25px;

}


.room-image img {

    width: 100%;

    max-width: 500px;

    height: 280px;

    object-fit: cover;

    border-radius: 12px;

}


/* ROOM NAME */

.room-name {

    text-align: center;

    margin-bottom: 25px;

}


.room-name h2 {

    color: #0f172a;

    font-size: 27px;

}


.room-name p {

    margin-top: 8px;

    color: #64748b;

}


/* DETAILS GRID */

.details-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

}


/* DETAIL BOX */

.detail-box {

    background: #f8fafc;

    padding: 18px;

    border-radius: 8px;

    border: 1px solid #e2e8f0;

}


.detail-box strong {

    display: block;

    color: #64748b;

    font-size: 13px;

    margin-bottom: 7px;

}


.detail-box span {

    font-size: 16px;

    font-weight: bold;

    color: #1e293b;

}


/* TOTAL AMOUNT */

.total-box {

    margin-top: 20px;

    padding: 20px;

    background: #ecfdf5;

    border: 1px solid #bbf7d0;

    border-radius: 10px;

    text-align: center;

}


.total-box strong {

    display: block;

    color: #166534;

    margin-bottom: 8px;

}


.total-box span {

    font-size: 28px;

    font-weight: bold;

    color: #15803d;

}


/* STATUS */

.status {

    display: inline-block;

    padding: 7px 14px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

}


.confirmed {

    background: #dcfce7;

    color: #15803d;

}


.cancelled {

    background: #fee2e2;

    color: #dc2626;

}


/* BUTTONS */

.buttons {

    display: flex;

    gap: 15px;

    margin-top: 25px;

}


.back-btn {

    flex: 1;

    padding: 13px;

    background: #475569;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 7px;

    font-weight: bold;

}


.rooms-btn {

    flex: 1;

    padding: 13px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 7px;

    font-weight: bold;

}


.back-btn:hover {

    background: #334155;

}


.rooms-btn:hover {

    background: #1d4ed8;

}


/* FOOTER */

footer {

    background: #0f172a;

    color: white;

    text-align: center;

    padding: 20px;

    margin-top: 50px;

}


/* MOBILE */

@media (max-width: 700px) {

    header {

        flex-direction: column;

        gap: 15px;

    }


    nav {

        flex-wrap: wrap;

        justify-content: center;

        gap: 15px;

    }


    .details-grid {

        grid-template-columns: 1fr;

    }


    .details-container {

        width: 95%;

        padding: 20px;

    }


    .buttons {

        flex-direction: column;

    }

}

</style>

</head>


<body>


<!-- HEADER -->

<header>

    <div class="logo">

        BookMySpace

    </div>


    <nav>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="rooms.php">
            Rooms
        </a>

        <a href="my_bookings.php">
            My Bookings
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<!-- TITLE -->

<div class="page-title">

    <h1>
        Booking Details
    </h1>

    <p>
        Complete information about your booking
    </p>

</div>


<!-- DETAILS -->

<div class="details-container">


    <!-- ROOM IMAGE -->

    <?php if (!empty($booking['image'])): ?>

        <div class="room-image">

            <img
                src="rooms/<?php echo htmlspecialchars($booking['image']); ?>"
                alt="Room Image"
            >

        </div>

    <?php endif; ?>


    <!-- ROOM NAME -->

    <div class="room-name">

        <h2>

            <?php

            echo htmlspecialchars(
                $booking['room_name']
            );

            ?>

        </h2>

        <p>

            <?php

            echo htmlspecialchars(
                $booking['room_type']
            );

            ?>

        </p>

    </div>


    <!-- DETAILS GRID -->

    <div class="details-grid">


        <div class="detail-box">

            <strong>
                Booking ID
            </strong>

            <span>

                #<?php

                echo $booking['id'];

                ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Booking Date
            </strong>

            <span>

                <?php

                echo date(
                    "d-m-Y",
                    strtotime(
                        $booking['booking_date']
                    )
                );

                ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Start Time
            </strong>

            <span>

                <?php

                echo date(
                    "h:i A",
                    strtotime(
                        $booking['start_time']
                    )
                );

                ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                End Time
            </strong>

            <span>

                <?php

                echo date(
                    "h:i A",
                    strtotime(
                        $booking['end_time']
                    )
                );

                ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Total Hours
            </strong>

            <span>

                <?php

                echo number_format(
                    $booking['total_hours'],
                    2
                );

                ?>

                hour(s)

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Price Per Hour
            </strong>

            <span>

                ₹<?php

                echo number_format(
                    $booking['price_per_hour'],
                    2
                );

                ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Booking Status
            </strong>

            <span>

                <?php if (
                    $booking['booking_status']
                    == 'Confirmed'
                ): ?>

                    <span class="status confirmed">
                        Confirmed
                    </span>

                <?php elseif (
                    $booking['booking_status']
                    == 'Cancelled'
                ): ?>

                    <span class="status cancelled">
                        Cancelled
                    </span>

                <?php else: ?>

                    <?php

                    echo htmlspecialchars(
                        $booking['booking_status']
                    );

                    ?>

                <?php endif; ?>

            </span>

        </div>


        <div class="detail-box">

            <strong>
                Room Description
            </strong>

            <span>

                <?php

                echo htmlspecialchars(
                    $booking['description']
                );

                ?>

            </span>

        </div>

    </div>


    <!-- TOTAL -->

    <div class="total-box">

        <strong>
            Total Booking Amount
        </strong>

        <span>

            ₹<?php

            echo number_format(
                $booking['total_amount'],
                2
            );

            ?>

        </span>

    </div>


    <!-- BUTTONS -->

    <div class="buttons">

        <a
            href="my_bookings.php"
            class="back-btn"
        >
            ← My Bookings
        </a>


        <a
            href="rooms.php"
            class="rooms-btn"
        >
            Book Another Room
        </a>

    </div>


</div>


<!-- FOOTER -->

<footer>

    <p>
        © 2026 BookMySapce - Hourly Room Booking System
    </p>

</footer>


</body>

</html>


<?php

$conn->close();

?>