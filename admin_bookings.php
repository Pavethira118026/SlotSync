<?php
session_start();
include "config/db.php";

/* =========================================================
   CANCEL BOOKING
   ========================================================= */

if (isset($_GET['cancel'])) {

    $booking_id = intval($_GET['cancel']);

    if ($booking_id > 0) {

        $cancel_sql = "UPDATE bookings
                       SET booking_status = 'Cancelled'
                       WHERE id = ?";

        $cancel_stmt = $conn->prepare($cancel_sql);

        if ($cancel_stmt) {

            $cancel_stmt->bind_param("i", $booking_id);
            $cancel_stmt->execute();

            $cancel_stmt->close();

            header("Location: admin_bookings.php");
            exit();

        } else {

            die("Cancel Booking Error: " . $conn->error);
        }
    }
}


/* =========================================================
   GET ALL BOOKINGS
   ========================================================= */

$sql = "SELECT
            bookings.id,
            bookings.booking_date,
            bookings.start_time,
            bookings.end_time,
            bookings.total_hours,
            bookings.total_amount,
            bookings.booking_status,
            bookings.created_at,
            users.name,
            users.email,
            rooms.room_name
        FROM bookings
        INNER JOIN users
            ON bookings.user_id = users.id
        INNER JOIN rooms
            ON bookings.room_id = rooms.id
        ORDER BY bookings.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Bookings - SlotSync</title>


<style>

/* =========================================================
   RESET
   ========================================================= */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


/* =========================================================
   BODY
   ========================================================= */

body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
    min-height: 100vh;
}


/* =========================================================
   HEADER
   ========================================================= */

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


/* =========================================================
   PAGE TITLE
   ========================================================= */

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


/* =========================================================
   BACK BUTTON
   ========================================================= */

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


/* =========================================================
   TABLE CONTAINER
   ========================================================= */

.table-container {

    width: 96%;

    max-width: 1500px;

    margin: 20px auto 60px;

    background: white;

    border-radius: 15px;

    overflow-x: auto;

    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}


/* =========================================================
   TABLE
   ========================================================= */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1200px;
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


/* =========================================================
   TOTAL AMOUNT
   ========================================================= */

.amount {

    color: #15803d;

    font-weight: bold;

    font-size: 15px;
}


/* =========================================================
   HOURS
   ========================================================= */

.hours {

    font-weight: bold;

    color: #334155;
}


/* =========================================================
   STATUS
   ========================================================= */

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


/* =========================================================
   CANCEL BUTTON
   ========================================================= */

.cancel-btn {

    display: inline-block;

    padding: 8px 14px;

    background: #dc2626;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 13px;

    font-weight: bold;

    transition: 0.3s;
}


.cancel-btn:hover {

    background: #b91c1c;

    transform: translateY(-1px);
}


/* =========================================================
   NO ACTION TEXT
   ========================================================= */

.cancelled-text {

    color: #64748b;

    font-size: 13px;

    font-weight: bold;
}


/* =========================================================
   NO BOOKINGS
   ========================================================= */

.no-bookings {

    text-align: center;

    padding: 60px;

    color: #64748b;
}


.no-bookings h2 {

    margin-bottom: 10px;

    color: #334155;
}


/* =========================================================
   FOOTER
   ========================================================= */

footer {

    background: #0f172a;

    color: white;

    text-align: center;

    padding: 20px;

    margin-top: 50px;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 700px) {

    header {

        flex-direction: column;

        gap: 15px;

        text-align: center;
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
        SlotSync Admin
    </div>


    <nav>

        <a href="admin.php">
            Dashboard
        </a>


        <a href="admin_rooms.php">
            Rooms
        </a>


        <a href="admin_bookings.php">
            Bookings
        </a>


        <a href="index.php">
            Home
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
        All Bookings
    </h1>

    <p>
        Manage and monitor customer room bookings
    </p>

</div>



<!-- =====================================================
     BACK BUTTON
     ===================================================== -->

<a href="admin.php" class="back-btn">
    ← Back to Dashboard
</a>



<!-- =====================================================
     DISPLAY BOOKINGS
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
        Customer
    </th>


    <th>
        Email
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

    <?php

    echo $booking['id'];

    ?>

</td>



<!-- CUSTOMER -->

<td>

    <?php

    echo htmlspecialchars(
        $booking['name']
    );

    ?>

</td>



<!-- EMAIL -->

<td>

    <?php

    echo htmlspecialchars(
        $booking['email']
    );

    ?>

</td>



<!-- ROOM -->

<td>

    <?php

    echo htmlspecialchars(
        $booking['room_name']
    );

    ?>

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



<!-- TOTAL AMOUNT -->

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


<?php

$status = $booking['booking_status'];

?>


<?php if ($status == "Confirmed"): ?>


    <span class="status confirmed">

        Confirmed

    </span>


<?php elseif ($status == "Cancelled"): ?>


    <span class="status cancelled">

        Cancelled

    </span>


<?php else: ?>


    <span class="status">

        <?php

        echo htmlspecialchars(
            $status
        );

        ?>

    </span>


<?php endif; ?>


</td>



<!-- ACTION -->

<td>


<?php if ($status == "Confirmed"): ?>


    <a
        href="admin_bookings.php?cancel=<?php echo $booking['id']; ?>"
        class="cancel-btn"
        onclick="return confirm('Are you sure you want to cancel this booking?');"
    >

        Cancel

    </a>


<?php else: ?>


    <span class="cancelled-text">

        No Action

    </span>


<?php endif; ?>


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
            There are currently no room bookings.
        </p>

    </div>

</div>


<?php endif; ?>



<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer>

    <p>
        © 2026 BookMySapce- Hourly Room Booking System
    </p>

</footer>



</body>

</html>


<?php

$conn->close();

?>