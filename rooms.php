<?php

/* =====================================
   ERROR REPORTING
===================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);


/* =====================================
   START SESSION
===================================== */

session_start();


/* =====================================
   DATABASE CONNECTION
===================================== */

include "config/db.php";


/* =====================================
   GET ROOMS
===================================== */

$sql = "SELECT * FROM rooms ORDER BY id ASC";

$result = $conn->query($sql);


if (!$result) {

    die(
        "Room database error: " .
        $conn->error
    );

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        BookMySapce - Rooms
    </title>


    <style>

        /* =====================================
           GENERAL
        ===================================== */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    rgba(240, 248, 250, 0.95),
                    rgba(240, 248, 250, 0.95)
                );

            color: #333;
        }


        /* =====================================
           HEADER
        ===================================== */

        header {

            background: #ffffff;

            padding: 18px 7%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow:
                0 2px 10px
                rgba(0, 0, 0, 0.10);

            position: sticky;

            top: 0;

            z-index: 100;
        }


        .logo {

            font-size: 29px;

            font-weight: bold;

            color: #15616d;
        }


        nav {

            display: flex;

            align-items: center;

            gap: 25px;
        }


        nav a {

            text-decoration: none;

            color: #333;

            font-weight: bold;

            transition: 0.3s;
        }


        nav a:hover {

            color: #15616d;
        }


        .login-btn {

            background: #15616d;

            color: white;

            padding:
                10px 18px;

            border-radius: 7px;
        }


        .login-btn:hover {

            color: white;

            background: #0f4f59;
        }


        /* =====================================
           PAGE HEADER
        ===================================== */

        .page-header {

            text-align: center;

            padding:
                55px 20px 35px;
        }


        .page-header h1 {

            margin: 0;

            color: #15616d;

            font-size: 38px;
        }


        .page-header p {

            margin-top: 12px;

            color: #666;

            font-size: 17px;
        }


        /* =====================================
           ROOM GRID
        ===================================== */

        .rooms-container {

            width: 90%;

            max-width: 1200px;

            margin:
                0 auto 60px;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 28px;
        }


        /* =====================================
           ROOM CARD
        ===================================== */

        .room-card {

            background: white;

            border-radius: 15px;

            overflow: hidden;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.10);

            transition:
                transform 0.3s,
                box-shadow 0.3s;
        }


        .room-card:hover {

            transform:
                translateY(-6px);

            box-shadow:
                0 14px 30px
                rgba(0, 0, 0, 0.15);
        }


        /* =====================================
           ROOM IMAGE
        ===================================== */

        .room-image {

            width: 100%;

            height: 230px;

            object-fit: cover;

            display: block;
        }


        /* =====================================
           ROOM CONTENT
        ===================================== */

        .room-content {

            padding: 22px;
        }


        .room-content h2 {

            margin:
                0 0 8px;

            color: #15616d;

            font-size: 24px;
        }


        .room-type {

            display: inline-block;

            padding:
                5px 10px;

            border-radius: 20px;

            background: #e7f5f7;

            color: #15616d;

            font-size: 13px;

            font-weight: bold;

            margin-bottom: 12px;
        }


        .description {

            color: #666;

            line-height: 1.6;

            min-height: 52px;

            margin-bottom: 15px;
        }


        /* =====================================
           ROOM INFO
        ===================================== */

        .room-info {

            display: flex;

            justify-content:
                space-between;

            gap: 10px;

            padding:
                12px 0;

            border-top:
                1px solid #eeeeee;

            border-bottom:
                1px solid #eeeeee;

            margin-bottom: 18px;
        }


        .info-item {

            text-align: center;

            flex: 1;
        }


        .info-label {

            display: block;

            font-size: 12px;

            color: #777;

            margin-bottom: 4px;
        }


        .info-value {

            font-weight: bold;

            color: #333;
        }


        /* =====================================
           PRICE
        ===================================== */

        .price {

            font-size: 23px;

            font-weight: bold;

            color: #15616d;

            margin:
                0 0 18px;
        }


        .price span {

            font-size: 14px;

            font-weight: normal;

            color: #777;
        }


        /* =====================================
           AVAILABLE BUTTON
        ===================================== */

        .book-btn {

            display: block;

            width: 100%;

            padding: 13px;

            text-align: center;

            text-decoration: none;

            background: #15616d;

            color: white;

            border-radius: 8px;

            font-size: 16px;

            font-weight: bold;

            transition:
                background 0.3s;
        }


        .book-btn:hover {

            background: #0f4f59;

            color: white;
        }


        /* =====================================
           NO ROOMS
        ===================================== */

        .no-rooms {

            width: 90%;

            max-width: 700px;

            margin:
                40px auto;

            background: white;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);
        }


        /* =====================================
           FOOTER
        ===================================== */

        footer {

            background: #15616d;

            color: white;

            text-align: center;

            padding: 25px;

            margin-top: 50px;
        }


        /* =====================================
           MOBILE
        ===================================== */

        @media (max-width: 900px) {

            .rooms-container {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            header {

                flex-direction: column;

                gap: 15px;
            }


            nav {

                flex-wrap: wrap;

                justify-content: center;

                gap: 15px;
            }


            .rooms-container {

                grid-template-columns: 1fr;
            }


            .page-header h1 {

                font-size: 30px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     HEADER
===================================== -->

<header>

    <div class="logo">
        BookMySapce
    </div>


    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="rooms.php">
            Rooms
        </a>


        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="dashboard.php">
                My Bookings
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a
                href="login.php"
                class="login-btn"
            >
                Login
            </a>

        <?php endif; ?>

    </nav>

</header>



<!-- =====================================
     PAGE TITLE
===================================== -->

<section class="page-header">

    <h1>
        Choose Your Room
    </h1>

    <p>
        Book comfortable rooms by the hour
        with BookMySapce.
    </p>

</section>



<!-- =====================================
     ROOM LIST
===================================== -->

<?php if ($result->num_rows > 0): ?>


<div class="rooms-container">


<?php while ($room = $result->fetch_assoc()): ?>


    <!-- ROOM CARD -->

    <div class="room-card">


        <!-- ROOM IMAGE -->

        <img
            src="rooms/<?php
                echo htmlspecialchars(
                    $room['image']
                );
            ?>"
            alt="<?php
                echo htmlspecialchars(
                    $room['room_name']
                );
            ?>"
            class="room-image"
        >


        <!-- ROOM CONTENT -->

        <div class="room-content">


            <!-- ROOM NAME -->

            <h2>

                <?php
                echo htmlspecialchars(
                    $room['room_name']
                );
                ?>

            </h2>


            <!-- ROOM TYPE -->

            <div class="room-type">

                <?php

                if (
                    isset($room['room_type'])
                    &&
                    !empty($room['room_type'])
                ) {

                    echo htmlspecialchars(
                        $room['room_type']
                    );

                } else {

                    echo "Room";

                }

                ?>

            </div>


            <!-- DESCRIPTION -->

            <p class="description">

                <?php

                if (
                    isset($room['description'])
                    &&
                    !empty($room['description'])
                ) {

                    echo htmlspecialchars(
                        $room['description']
                    );

                } else {

                    echo
                    "Comfortable room available for hourly booking.";

                }

                ?>

            </p>


            <!-- ROOM INFORMATION -->

            <div class="room-info">


                <div class="info-item">

                    <span class="info-label">
                        Capacity
                    </span>

                    <span class="info-value">

                        <?php

                        if (
                            isset($room['capacity'])
                            &&
                            $room['capacity'] != ""
                        ) {

                            echo htmlspecialchars(
                                $room['capacity']
                            );

                        } else {

                            echo "N/A";

                        }

                        ?>

                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Booking
                    </span>

                    <span class="info-value">
                        Hourly
                    </span>

                </div>


            </div>


            <!-- PRICE -->

            <div class="price">

                ₹<?php

                echo number_format(
                    floatval(
                        $room['price_per_hour']
                    ),
                    2
                );

                ?>

                <span>
                    / hour
                </span>

            </div>


            <!-- =================================
                 AVAILABLE / BOOK BUTTON

                 IMPORTANT:
                 room_id is passed here
            ================================== -->

            <?php if (isset($_SESSION['user_id'])): ?>

                <a
                    href="booking.php?room_id=<?php
                        echo intval(
                            $room['id']
                        );
                    ?>"
                    class="book-btn"
                >

                    Available - Book Now

                </a>

            <?php else: ?>

                <a
                    href="login.php"
                    class="book-btn"
                >

                    Login to Book

                </a>

            <?php endif; ?>


        </div>

    </div>


<?php endwhile; ?>


</div>


<?php else: ?>


<!-- =====================================
     NO ROOMS
===================================== -->

<div class="no-rooms">

    <h2>
        No Rooms Available
    </h2>

    <p>
        There are currently no rooms
        available for booking.
    </p>

</div>


<?php endif; ?>



<!-- =====================================
     FOOTER
===================================== -->

<footer>

    <p>
        © 2026 BookMySapce -
        Hourly Room Booking System
    </p>

</footer>


</body>

</html>