<?php
session_start();

include "config/db.php";

/* =====================================================
   CHECK USER LOGIN
   ===================================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION['user_id'];


/* =====================================================
   CANCEL BOOKING
   ===================================================== */

if (isset($_GET['cancel'])) {

    $booking_id = intval($_GET['cancel']);

    if ($booking_id > 0) {

        $cancel_sql = "
            UPDATE bookings
            SET booking_status = 'Cancelled'
            WHERE id = ?
            AND user_id = ?
        ";

        $cancel_stmt = $conn->prepare($cancel_sql);

        if ($cancel_stmt) {

            $cancel_stmt->bind_param(
                "ii",
                $booking_id,
                $user_id
            );

            $cancel_stmt->execute();

            $cancel_stmt->close();

            header("Location: my_bookings.php");
            exit();

        } else {

            die("Database Error: " . $conn->error);

        }
    }
}


/* =====================================================
   GET USER BOOKINGS
   ===================================================== */

$sql = "
    SELECT
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
        rooms.image

    FROM bookings

    INNER JOIN rooms
        ON bookings.room_id = rooms.id

    WHERE bookings.user_id = ?

    ORDER BY bookings.created_at DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database Error: " . $conn->error);

}

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Bookings - SlotSync</title>


<style>

/* =====================================================
   RESET
   ===================================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =====================================================
   BODY
   ===================================================== */

body {

    font-family: Arial, sans-serif;

    background: #f1f5f9;

    color: #1e293b;

    min-height: 100vh;

}


/* =====================================================
   HEADER
   ===================================================== */

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


/* =====================================================
   PAGE TITLE
   ===================================================== */

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


/* =====================================================
   BACK BUTTON
   ===================================================== */

.back-btn {

    display: inline-block;

    margin: 0 0 20px 2%;

    padding: 11px 20px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    border-radius: 7px;

    font-weight: bold;

}


.back-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   TABLE CONTAINER
   ===================================================== */

.table-container {

    width: 96%;

    max-width: 1500px;

    margin: 20px auto 60px;

    background: white;

    border-radius: 15px;

    overflow-x: auto;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);

}


/* =====================================================
   TABLE
   ===================================================== */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1250px;

}


th {

    background: #2563eb;

    color: white;

    padding: 15px 10px;

    text-align: center;

    font-size: 14px;

}


td {

    padding: 14px 10px;

    text-align: center;

    border-bottom: 1px solid #e2e8f0;

    font-size: 14px;

}


tr:hover {

    background: #f8fafc;

}


/* =====================================================
   ROOM NAME
   ===================================================== */

.room-name {

    font-weight: bold;

    color: #0f172a;

}


.room-type {

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;

}


/* =====================================================
   HOURS
   ===================================================== */

.hours {

    font-weight: bold;

    color: #334155;

}


/* =====================================================
   TOTAL AMOUNT
   ===================================================== */

.amount {

    color: #15803d;

    font-weight: bold;

    font-size: 15px;

}


/* =====================================================
   STATUS
   ===================================================== */

.status {

    display: inline-block;

    padding: 7px 13px;

    border-radius: 20px;

    font-weight: bold;

    font-size: 13px;

}


.confirmed {

    background: #dcfce7;

    color: #15803d;

}


.cancelled {

    background: #fee2e2;

    color: #dc2626;

}


/* =====================================================
   ACTION BUTTONS
   ===================================================== */

.action-buttons {

    display: flex;

    justify-content: center;

    gap: 7px;

    flex-wrap: wrap;

}


/* VIEW DETAILS */

.view-btn {

    display: inline-block;

    padding: 8px 13px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 13px;

    font-weight: bold;

}


.view-btn:hover {

    background: #1d4ed8;

}


/* CANCEL */

.cancel-btn {

    display: inline-block;

    padding: 8px 13px;

    background: #dc2626;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 13px;

    font-weight: bold;

}


.cancel-btn:hover {

    background: #b91c1c;

}


/* NO ACTION */

.no-action {

    color: #64748b;

    font-size: 13px;

    font-weight: bold;

}


/* =====================================================
   NO BOOKINGS
   ===================================================== */

.no-bookings {

    text-align: center;

    padding: 70px 20px;

    color: #64748b;

}


.no-bookings h2 {

    margin-bottom: 10px;

    color: #334155;

}


.book-room-btn {

    display: inline-block;

    margin-top: 20px;

    padding: 12px 20px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    border-radius: 7px;

    font-weight: bold;

}


.book-room-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   FOOTER
   ===================================================== */

footer {

    background: #0f172a;

    color: white;

    text-align: center;

    padding: 20px;

    margin-top: 50px;

}


/* =====================================================
   MOBILE
   ===================================================== */

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


    .page-title h1 {

        font-size: 28px;

    }


    .table-container {

        width: 94%;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
     ===================================================== -->

<header>

    <div class="logo">

        SlotSync

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


<!-- =====================================================
     PAGE TITLE
     ===================================================== -->

<div class="page-title">

    <h1>
        My Bookings
    </h1>

    <p>
        View and manage your room bookings
    </p>

</div>


<!-- =====================================================
     BACK BUTTON
     ===================================================== -->

<a
    href="dashboard.php"
    class="back-btn"
>
    ← Back to Dashboard
</a>


<!-- =====================================================
     BOOKINGS
     ===================================================== -->

<?php if ($result->num_rows > 0): ?>


<div class="table-container">

<table>


<thead>

<tr>

    <th>
        Booking ID
    </th>

    <th>
        Room
    </th>

    <th>
        Date
    </th>

    <th>
        Start Time
    </th>

    <th>
        End Time
    </th>

    <th>
        Hours
    </th>

    <th>
        Total Amount
    </th>

    <th>
        Status
    </th>

    <th>
        Action
    </th>

</tr>

</thead>


<tbody>


<?php while ($booking = $result->fetch_assoc()): ?>


<tr>


<!-- BOOKING ID -->

<td>

    <strong>

        #<?php

        echo $booking['id'];

        ?>

    </strong>

</td>


<!-- ROOM -->

<td>

    <div class="room-name">

        <?php

        echo htmlspecialchars(
            $booking['room_name']
        );

        ?>

    </div>


    <div class="room-type">

        <?php

        echo htmlspecialchars(
            $booking['room_type']
        );

        ?>

    </div>

</td>


<!-- DATE -->

<td>

    <?php

    echo date(
        "d-m-Y",
        strtotime(
            $booking['booking_date']
        )
    );

    ?>

</td>


<!-- START TIME -->

<td>

    <?php

    echo date(
        "h:i A",
        strtotime(
            $booking['start_time']
        )
    );

    ?>

</td>


<!-- END TIME -->

<td>

    <?php

    echo date(
        "h:i A",
        strtotime(
            $booking['end_time']
        )
    );

    ?>

</td>


<!-- HOURS -->

<td class="hours">

    <?php

    echo number_format(
        $booking['total_hours'],
        2
    );

    ?>

    hour(s)

</td>


<!-- TOTAL -->

<td class="amount">

    ₹<?php

    echo number_format(
        $booking['total_amount'],
        2
    );

    ?>

</td>


<!-- STATUS -->

<td>


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


    <span class="status">

        <?php

        echo htmlspecialchars(
            $booking['booking_status']
        );

        ?>

    </span>


<?php endif; ?>


</td>


<!-- ACTION -->

<td>


<div class="action-buttons">


    <!-- VIEW DETAILS -->

    <a
        href="booking_details.php?id=<?php echo $booking['id']; ?>"
        class="view-btn"
    >

        View Details

    </a>


    <!-- CANCEL -->

    <?php if (
        $booking['booking_status']
        == 'Confirmed'
    ): ?>


        <a
            href="my_bookings.php?cancel=<?php echo $booking['id']; ?>"
            class="cancel-btn"
            onclick="return confirm('Are you sure you want to cancel this booking?');"
        >

            Cancel

        </a>


    <?php else: ?>


        <span class="no-action">

            No Action

        </span>


    <?php endif; ?>


</div>


</td>


</tr>


<?php endwhile; ?>


</tbody>


</table>

</div>


<?php else: ?>


<!-- =====================================================
     NO BOOKINGS
     ===================================================== -->

<div class="table-container">

    <div class="no-bookings">

        <h2>
            No Bookings Found
        </h2>

        <p>
            You have not made any room bookings yet.
        </p>


        <a
            href="rooms.php"
            class="book-room-btn"
        >

            Browse Rooms

        </a>

    </div>

</div>


<?php endif; ?>


<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer>

    <p>
        © 2026 BookMySapces - Hourly Room Booking System
    </p>

</footer>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>