<?php
session_start();
include "config/db.php";

/* =====================================================
   GET TOTAL USERS
   ===================================================== */

$user_sql = "SELECT COUNT(*) AS total_users FROM users";
$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();
$total_users = $user_data['total_users'];


/* =====================================================
   GET TOTAL ROOMS
   ===================================================== */

$room_sql = "SELECT COUNT(*) AS total_rooms FROM rooms";
$room_result = $conn->query($room_sql);
$room_data = $room_result->fetch_assoc();
$total_rooms = $room_data['total_rooms'];


/* =====================================================
   GET TOTAL BOOKINGS
   ===================================================== */

$booking_sql = "SELECT COUNT(*) AS total_bookings FROM bookings";
$booking_result = $conn->query($booking_sql);
$booking_data = $booking_result->fetch_assoc();
$total_bookings = $booking_data['total_bookings'];


/* =====================================================
   GET CONFIRMED BOOKINGS
   ===================================================== */

$confirmed_sql = "SELECT COUNT(*) AS confirmed_bookings
                  FROM bookings
                  WHERE booking_status = 'Confirmed'";

$confirmed_result = $conn->query($confirmed_sql);
$confirmed_data = $confirmed_result->fetch_assoc();

$confirmed_bookings = $confirmed_data['confirmed_bookings'];


/* =====================================================
   GET CANCELLED BOOKINGS
   ===================================================== */

$cancelled_sql = "SELECT COUNT(*) AS cancelled_bookings
                  FROM bookings
                  WHERE booking_status = 'Cancelled'";

$cancelled_result = $conn->query($cancelled_sql);
$cancelled_data = $cancelled_result->fetch_assoc();

$cancelled_bookings = $cancelled_data['cancelled_bookings'];


/* =====================================================
   GET TOTAL REVENUE
   ===================================================== */

$revenue_sql = "SELECT COALESCE(SUM(total_amount), 0) AS total_revenue
                FROM bookings
                WHERE booking_status = 'Confirmed'";

$revenue_result = $conn->query($revenue_sql);
$revenue_data = $revenue_result->fetch_assoc();

$total_revenue = $revenue_data['total_revenue'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard - SlotSync</title>


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
    font-size: 28px;
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
    padding: 45px 20px 30px;
}


.page-title h1 {
    font-size: 36px;
    color: #0f172a;
}


.page-title p {
    margin-top: 10px;
    color: #64748b;
}


/* =====================================================
   DASHBOARD CONTAINER
   ===================================================== */

.dashboard {
    width: 92%;
    max-width: 1200px;
    margin: auto;
}


/* =====================================================
   STATISTICS CARDS
   ===================================================== */

.stats-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(220px, 1fr));

    gap: 25px;

    margin-bottom: 40px;
}


.stat-card {
    background: white;

    padding: 28px;

    border-radius: 15px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);

    transition: 0.3s;
}


.stat-card:hover {
    transform: translateY(-5px);
}


.stat-icon {
    font-size: 38px;
    margin-bottom: 12px;
}


.stat-card h2 {
    font-size: 32px;
    color: #2563eb;
    margin-bottom: 8px;
}


.stat-card p {
    color: #64748b;
    font-weight: bold;
}


/* =====================================================
   DIFFERENT CARD COLORS
   ===================================================== */

.users h2 {
    color: #7c3aed;
}


.rooms h2 {
    color: #0891b2;
}


.bookings h2 {
    color: #2563eb;
}


.confirmed h2 {
    color: #16a34a;
}


.cancelled h2 {
    color: #dc2626;
}


.revenue h2 {
    color: #ca8a04;
}


/* =====================================================
   QUICK ACTIONS
   ===================================================== */

.quick-actions {
    background: white;

    padding: 30px;

    border-radius: 15px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);

    margin-bottom: 50px;
}


.quick-actions h2 {
    text-align: center;

    margin-bottom: 25px;

    color: #0f172a;
}


.action-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(200px, 1fr));

    gap: 20px;
}


.action-btn {
    display: block;

    padding: 16px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 8px;

    font-weight: bold;

    transition: 0.3s;
}


.action-btn:hover {
    background: #1d4ed8;
}


.rooms-btn {
    background: #0891b2;
}


.rooms-btn:hover {
    background: #0e7490;
}


.bookings-btn {
    background: #16a34a;
}


.bookings-btn:hover {
    background: #15803d;
}


.users-btn {
    background: #7c3aed;
}


.users-btn:hover {
    background: #6d28d9;
}


/* =====================================================
   FOOTER
   ===================================================== */

footer {
    background: #0f172a;

    color: white;

    text-align: center;

    padding: 22px;

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

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
     ===================================================== -->

<header>

    <div class="logo">
        BookMySpace Admin
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
        Admin Dashboard
    </h1>

    <p>
        Manage SlotSync room booking system
    </p>

</div>



<!-- =====================================================
     DASHBOARD
     ===================================================== -->

<div class="dashboard">


<!-- =====================================================
     STATISTICS
     ===================================================== -->

<div class="stats-grid">


    <!-- USERS -->

    <div class="stat-card users">

        <div class="stat-icon">
            👥
        </div>

        <h2>
            <?php echo $total_users; ?>
        </h2>

        <p>
            Total Users
        </p>

    </div>



    <!-- ROOMS -->

    <div class="stat-card rooms">

        <div class="stat-icon">
            🏠
        </div>

        <h2>
            <?php echo $total_rooms; ?>
        </h2>

        <p>
            Total Rooms
        </p>

    </div>



    <!-- BOOKINGS -->

    <div class="stat-card bookings">

        <div class="stat-icon">
            📅
        </div>

        <h2>
            <?php echo $total_bookings; ?>
        </h2>

        <p>
            Total Bookings
        </p>

    </div>



    <!-- CONFIRMED -->

    <div class="stat-card confirmed">

        <div class="stat-icon">
            ✅
        </div>

        <h2>
            <?php echo $confirmed_bookings; ?>
        </h2>

        <p>
            Confirmed Bookings
        </p>

    </div>



    <!-- CANCELLED -->

    <div class="stat-card cancelled">

        <div class="stat-icon">
            ❌
        </div>

        <h2>
            <?php echo $cancelled_bookings; ?>
        </h2>

        <p>
            Cancelled Bookings
        </p>

    </div>



    <!-- REVENUE -->

    <div class="stat-card revenue">

        <div class="stat-icon">
            💰
        </div>

        <h2>
            ₹<?php echo number_format($total_revenue, 2); ?>
        </h2>

        <p>
            Total Revenue
        </p>

    </div>


</div>



<!-- =====================================================
     QUICK ACTIONS
     ===================================================== -->

<div class="quick-actions">

    <h2>
        Quick Actions
    </h2>


    <div class="action-grid">


        <a
            href="admin_rooms.php"
            class="action-btn rooms-btn"
        >
            🏠 Manage Rooms
        </a>


        <a
            href="admin_bookings.php"
            class="action-btn bookings-btn"
        >
            📅 Manage Bookings
        </a>


        <a
            href="admin_users.php"
            class="action-btn users-btn"
        >
            👥 Manage Users
        </a>


        <a
            href="rooms.php"
            class="action-btn"
        >
            🔎 View Rooms
        </a>


    </div>

</div>


</div>



<!-- =====================================================
     FOOTER
     ===================================================== -->

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