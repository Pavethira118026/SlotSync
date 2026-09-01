<?php

session_start();

/* =========================================================
   SLOTSYNC - BOOKING PAGE
   ========================================================= */


/* =========================================================
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

$conn = new mysqli(
    "db",
    "slotsync_user",
    "slotsync_password",
    "slotsync"
);


if ($conn->connect_error) {

    die("Database connection failed: " . $conn->connect_error);

}


$conn->set_charset("utf8mb4");


/* =========================================================
   USER
   ========================================================= */

$user_id = (int) $_SESSION['user_id'];

$user_name = $_SESSION['user_name'] ?? "User";


/* =========================================================
   VARIABLES
   ========================================================= */

$message = "";
$message_type = "";

$selected_room = "";
$selected_date = "";
$selected_start = "";
$selected_end = "";


/* =========================================================
   ROOM FROM URL
   Example:
   booking.php?room_id=1
   ========================================================= */

if (isset($_GET['room_id'])) {

    $selected_room = (int) $_GET['room_id'];

}


/* =========================================================
   PROCESS BOOKING
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $selected_room = isset($_POST['room_id'])
        ? (int) $_POST['room_id']
        : 0;

    $selected_date = $_POST['booking_date'] ?? "";

    $selected_start = $_POST['start_time'] ?? "";

    $selected_end = $_POST['end_time'] ?? "";


    /* =====================================================
       BASIC VALIDATION
       ===================================================== */

    if (
        $selected_room <= 0 ||
        empty($selected_date) ||
        empty($selected_start) ||
        empty($selected_end)
    ) {

        $message =
            "Please select a room, date, start time and end time.";

        $message_type = "error";

    } else {


        /* =================================================
           DATE VALIDATION
           ================================================= */

        $today = date("Y-m-d");


        if ($selected_date < $today) {

            $message =
                "You cannot book a previous date.";

            $message_type = "error";

        } else {


            /* =================================================
               TIME VALIDATION
               ================================================= */

            $start_timestamp =
                strtotime($selected_start);

            $end_timestamp =
                strtotime($selected_end);


            if (
                $start_timestamp === false ||
                $end_timestamp === false
            ) {

                $message =
                    "Invalid time selected.";

                $message_type = "error";

            } elseif ($end_timestamp <= $start_timestamp) {

                $message =
                    "End time must be later than starting time.";

                $message_type = "error";

            } else {


                /* =================================================
                   CALCULATE DURATION
                   ================================================= */

                $start_minutes =
                    ((int) date("H", $start_timestamp) * 60)
                    + (int) date("i", $start_timestamp);


                $end_minutes =
                    ((int) date("H", $end_timestamp) * 60)
                    + (int) date("i", $end_timestamp);


                $duration_minutes =
                    $end_minutes - $start_minutes;


                /* Only complete hours */

                if ($duration_minutes % 60 !== 0) {

                    $message =
                        "Please select a whole number of hours.";

                    $message_type = "error";

                } else {


                    $duration =
                        $duration_minutes / 60;


                    if ($duration < 1) {

                        $message =
                            "Please select at least one hour.";

                        $message_type = "error";

                    } else {


                        /* =================================================
                           GET ROOM
                           ================================================= */

                        $room_sql = "
                            SELECT
                                id,
                                name,
                                description,
                                location,
                                price_per_hour,
                                image
                            FROM rooms
                            WHERE id = ?
                            LIMIT 1
                        ";


                        $room_stmt =
                            $conn->prepare($room_sql);


                        if (!$room_stmt) {

                            $message =
                                "Room query error: "
                                . $conn->error;

                            $message_type = "error";

                        } else {


                            $room_stmt->bind_param(
                                "i",
                                $selected_room
                            );


                            $room_stmt->execute();


                            $room_result =
                                $room_stmt->get_result();


                            if ($room_result->num_rows === 0) {

                                $message =
                                    "Selected room does not exist.";

                                $message_type = "error";

                            } else {


                                $room =
                                    $room_result->fetch_assoc();


                                $room_name =
                                    $room['name'];


                                $price_per_hour =
                                    (float) $room['price_per_hour'];


                                /* =================================================
                                   CHECK BOOKING CONFLICT
                                   ================================================= */

                                $conflict_sql = "
                                    SELECT id
                                    FROM bookings
                                    WHERE room_id = ?
                                    AND booking_date = ?
                                    AND booking_status = 'Confirmed'
                                    AND start_time < ?
                                    AND end_time > ?
                                    LIMIT 1
                                ";


                                $conflict_stmt =
                                    $conn->prepare(
                                        $conflict_sql
                                    );


                                if (!$conflict_stmt) {

                                    $message =
                                        "Booking check error: "
                                        . $conn->error;

                                    $message_type = "error";

                                } else {


                                    $conflict_stmt->bind_param(
                                        "isss",
                                        $selected_room,
                                        $selected_date,
                                        $selected_end,
                                        $selected_start
                                    );


                                    $conflict_stmt->execute();


                                    $conflict_result =
                                        $conflict_stmt->get_result();


                                    /* =============================================
                                       CONFLICT FOUND
                                       ============================================= */

                                    if (
                                        $conflict_result->num_rows > 0
                                    ) {

                                        $message =
                                            "This room is already booked for the selected time.";

                                        $message_type = "error";

                                    } else {


                                        /* =================================================
                                           CALCULATE TOTAL
                                           ================================================= */

                                        $total_amount =
                                            $duration * $price_per_hour;


                                        /* =================================================
                                           INSERT BOOKING
                                           ================================================= */

                                        $insert_sql = "
                                            INSERT INTO bookings
                                            (
                                                user_id,
                                                room_id,
                                                booking_date,
                                                start_time,
                                                end_time,
                                                duration,
                                                total_amount,
                                                booking_status
                                            )
                                            VALUES
                                            (
                                                ?,
                                                ?,
                                                ?,
                                                ?,
                                                ?,
                                                ?,
                                                ?,
                                                'Confirmed'
                                            )
                                        ";


                                        $insert_stmt =
                                            $conn->prepare(
                                                $insert_sql
                                            );


                                        if (!$insert_stmt) {

                                            $message =
                                                "Booking creation error: "
                                                . $conn->error;

                                            $message_type = "error";

                                        } else {


                                            $insert_stmt->bind_param(
                                                "iisssdd",
                                                $user_id,
                                                $selected_room,
                                                $selected_date,
                                                $selected_start,
                                                $selected_end,
                                                $duration,
                                                $total_amount
                                            );


                                            if (
                                                $insert_stmt->execute()
                                            ) {

                                                $booking_id =
                                                    $conn->insert_id;


                                                $message =
                                                    "Booking successful! "
                                                    . "Your Booking ID is #"
                                                    . $booking_id;


                                                $message_type =
                                                    "success";


                                                /* Clear form */

                                                $selected_room = "";

                                                $selected_date = "";

                                                $selected_start = "";

                                                $selected_end = "";

                                            } else {

                                                $message =
                                                    "Booking failed: "
                                                    . $insert_stmt->error;

                                                $message_type =
                                                    "error";

                                            }


                                            $insert_stmt->close();

                                        }

                                    }


                                    $conflict_stmt->close();

                                }

                            }


                            $room_stmt->close();

                        }

                    }

                }

            }

        }

    }

}


/* =========================================================
   GET ALL ROOMS
   ========================================================= */

$rooms_sql = "
    SELECT
        id,
        name,
        description,
        location,
        price_per_hour,
        image
    FROM rooms
    ORDER BY name ASC
";


$rooms_result =
    $conn->query($rooms_sql);

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
        Book Room - SlotSync
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family: Arial, Helvetica, sans-serif;

            background: #f4f7fb;

            color: #1f2937;

        }


        /* =========================
           NAVBAR
           ========================= */

        .navbar {

            background: #2563eb;

            color: white;

            padding: 16px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.15);

        }


        .logo {

            font-size: 24px;

            font-weight: bold;

        }


        .nav-links {

            display: flex;

            gap: 8px;

            align-items: center;

            flex-wrap: wrap;

        }


        .nav-links a {

            color: white;

            text-decoration: none;

            padding: 9px 12px;

            border-radius: 6px;

            font-size: 14px;

        }


        .nav-links a:hover {

            background:
                rgba(255,255,255,0.15);

        }


        /* =========================
           CONTAINER
           ========================= */

        .container {

            width: 90%;

            max-width: 1000px;

            margin: 40px auto;

        }


        .page-title {

            text-align: center;

            margin-bottom: 25px;

        }


        .page-title h1 {

            font-size: 32px;

            margin-bottom: 8px;

            color: #111827;

        }


        .page-title p {

            color: #6b7280;

        }


        /* =========================
           MESSAGE
           ========================= */

        .message {

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-weight: bold;

        }


        .message.success {

            background: #dcfce7;

            color: #166534;

            border:
                1px solid #86efac;

        }


        .message.error {

            background: #fee2e2;

            color: #991b1b;

            border:
                1px solid #fca5a5;

        }


        /* =========================
           BOOKING CARD
           ========================= */

        .booking-card {

            background: white;

            border-radius: 12px;

            padding: 30px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);

        }


        .section-title {

            font-size: 21px;

            font-weight: bold;

            margin-bottom: 20px;

            color: #111827;

        }


        /* =========================
           FORM
           ========================= */

        .form-group {

            margin-bottom: 20px;

        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;

            color: #374151;

        }


        select,
        input[type="date"],
        input[type="time"] {

            width: 100%;

            padding: 12px;

            border:
                1px solid #d1d5db;

            border-radius: 7px;

            font-size: 15px;

            background: white;

        }


        select:focus,
        input:focus {

            outline: none;

            border-color: #2563eb;

            box-shadow:
                0 0 0 2px
                rgba(37,99,235,0.12);

        }


        .time-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;

        }


        /* =========================
           ROOM INFORMATION
           ========================= */

        .room-info {

            display: none;

            background: #eff6ff;

            border:
                1px solid #bfdbfe;

            padding: 18px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        .room-info h3 {

            color: #1d4ed8;

            margin-bottom: 8px;

        }


        .room-info p {

            margin: 5px 0;

            color: #374151;

        }


        /* =========================
           PRICE
           ========================= */

        .price-box {

            background: #f9fafb;

            border:
                1px solid #e5e7eb;

            border-radius: 8px;

            padding: 18px;

            margin-bottom: 20px;

        }


        .price-row {

            display: flex;

            justify-content:
                space-between;

            margin-bottom: 8px;

        }


        .price-total {

            border-top:
                1px solid #d1d5db;

            margin-top: 10px;

            padding-top: 12px;

            font-size: 21px;

            font-weight: bold;

            color: #2563eb;

        }


        /* =========================
           BUTTON
           ========================= */

        .book-btn {

            width: 100%;

            padding: 14px;

            background: #2563eb;

            color: white;

            border: none;

            border-radius: 8px;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;

        }


        .book-btn:hover {

            background: #1d4ed8;

        }


        /* =========================
           ROOMS
           ========================= */

        .rooms-section {

            margin-top: 30px;

        }


        .rooms-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(220px, 1fr)
                );

            gap: 15px;

        }


        .room-card {

            border:
                1px solid #e5e7eb;

            padding: 18px;

            border-radius: 8px;

            background: white;

        }


        .room-card h3 {

            color: #111827;

            margin-bottom: 8px;

        }


        .room-card p {

            color: #6b7280;

            font-size: 14px;

            margin: 5px 0;

        }


        .room-price {

            color: #2563eb !important;

            font-weight: bold;

        }


        /* =========================
           RESPONSIVE
           ========================= */

        @media (max-width: 700px) {

            .navbar {

                flex-direction: column;

                gap: 12px;

            }


            .time-grid {

                grid-template-columns: 1fr;

            }


            .container {

                width: 94%;

            }


            .booking-card {

                padding: 20px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVIGATION
     ===================================================== -->

<nav class="navbar">


    <div class="logo">

        SlotSync

    </div>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="rooms.php">
            Rooms
        </a>

        <a href="my_bookings.php">
            My Bookings
        </a>

        <a href="cancel_booking.php">
            Cancel Booking
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>


</nav>


<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <div class="page-title">

        <h1>
            Book a Room
        </h1>

        <p>
            Welcome,
            <?php
            echo htmlspecialchars($user_name);
            ?>.
            Select your room and hourly slot.
        </p>

    </div>


    <!-- =================================================
         MESSAGE
         ================================================= -->

    <?php if (!empty($message)): ?>

        <div
            class="message
            <?php
            echo htmlspecialchars(
                $message_type
            );
            ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         BOOKING FORM
         ================================================= -->

    <div class="booking-card">


        <div class="section-title">

            Room Booking Details

        </div>


        <form
            method="POST"
            action="booking.php"
            onsubmit="return validateBooking();"
        >


            <!-- ROOM -->

            <div class="form-group">

                <label for="room_id">

                    Select Room

                </label>


                <select
                    name="room_id"
                    id="room_id"
                    required
                    onchange="updateRoomInfo();"
                >

                    <option value="">

                        -- Select a Room --

                    </option>


                    <?php

                    if (
                        $rooms_result &&
                        $rooms_result->num_rows > 0
                    ):

                        while (
                            $room =
                            $rooms_result->fetch_assoc()
                        ):

                    ?>

                        <option
                            value="<?php
                            echo (int)$room['id'];
                            ?>"
                            data-name="<?php
                            echo htmlspecialchars(
                                $room['name']
                            );
                            ?>"
                            data-description="<?php
                            echo htmlspecialchars(
                                $room['description'] ?? ""
                            );
                            ?>"
                            data-location="<?php
                            echo htmlspecialchars(
                                $room['location'] ?? ""
                            );
                            ?>"
                            data-price="<?php
                            echo htmlspecialchars(
                                $room['price_per_hour']
                            );
                            ?>"
                            <?php

                            if (
                                $selected_room ==
                                $room['id']
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $room['name']
                            );
                            ?>

                            -

                            ₹<?php
                            echo number_format(
                                $room['price_per_hour'],
                                2
                            );
                            ?>

                            /hour

                        </option>


                    <?php

                        endwhile;

                    else:

                    ?>

                        <option value="">

                            No rooms available

                        </option>

                    <?php

                    endif;

                    ?>

                </select>

            </div>


            <!-- ROOM INFORMATION -->

            <div
                id="roomInfo"
                class="room-info"
            >

                <h3 id="roomName">
                    Room
                </h3>


                <p id="roomDescription">
                </p>


                <p>

                    <strong>
                        Location:
                    </strong>

                    <span id="roomLocation">
                    </span>

                </p>


                <p>

                    <strong>
                        Price:
                    </strong>

                    ₹<span id="roomPrice">
                    </span>

                    / hour

                </p>

            </div>


            <!-- DATE -->

            <div class="form-group">

                <label for="booking_date">

                    Select Date

                </label>


                <input
                    type="date"
                    name="booking_date"
                    id="booking_date"
                    min="<?php
                    echo date('Y-m-d');
                    ?>"
                    value="<?php
                    echo htmlspecialchars(
                        $selected_date
                    );
                    ?>"
                    required
                >

            </div>


            <!-- TIME -->

            <div class="time-grid">


                <div class="form-group">

                    <label for="start_time">

                        Start Time

                    </label>


                    <input
                        type="time"
                        name="start_time"
                        id="start_time"
                        value="<?php
                        echo htmlspecialchars(
                            $selected_start
                        );
                        ?>"
                        required
                        onchange="calculatePrice();"
                    >

                </div>


                <div class="form-group">

                    <label for="end_time">

                        End Time

                    </label>


                    <input
                        type="time"
                        name="end_time"
                        id="end_time"
                        value="<?php
                        echo htmlspecialchars(
                            $selected_end
                        );
                        ?>"
                        required
                        onchange="calculatePrice();"
                    >

                </div>


            </div>


            <!-- PRICE -->

            <div class="price-box">


                <div class="price-row">

                    <span>
                        Price per hour
                    </span>


                    <span>

                        ₹<span id="pricePerHour">
                            0.00
                        </span>

                    </span>

                </div>


                <div class="price-row">

                    <span>
                        Number of hours
                    </span>


                    <span id="numberOfHours">

                        0

                    </span>

                </div>


                <div class="price-row price-total">

                    <span>
                        Total Amount
                    </span>


                    <span>

                        ₹<span id="totalPrice">
                            0.00
                        </span>

                    </span>

                </div>


            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="book-btn"
            >

                Confirm Booking

            </button>


        </form>


    </div>


    <!-- =================================================
         AVAILABLE ROOMS
         ================================================= -->

    <div class="rooms-section">


        <div class="section-title">

            Available Rooms

        </div>


        <div class="rooms-grid">


            <?php

            $display_sql = "
                SELECT
                    id,
                    name,
                    description,
                    location,
                    price_per_hour
                FROM rooms
                ORDER BY name ASC
            ";


            $display_result =
                $conn->query($display_sql);


            if (
                $display_result &&
                $display_result->num_rows > 0
            ):

                while (
                    $display_room =
                    $display_result->fetch_assoc()
                ):

            ?>


                <div class="room-card">


                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $display_room['name']
                        );
                        ?>

                    </h3>


                    <p>

                        <?php
                        echo htmlspecialchars(
                            $display_room['description']
                            ?? ""
                        );
                        ?>

                    </p>


                    <p>

                        Location:

                        <?php
                        echo htmlspecialchars(
                            $display_room['location']
                            ?? ""
                        );
                        ?>

                    </p>


                    <p class="room-price">

                        ₹<?php
                        echo number_format(
                            $display_room[
                                'price_per_hour'
                            ],
                            2
                        );
                        ?>

                        / hour

                    </p>


                </div>


            <?php

                endwhile;

            else:

            ?>


                <p>

                    No rooms are currently available.

                </p>


            <?php

            endif;

            ?>


        </div>


    </div>


</div>


<!-- =====================================================
     JAVASCRIPT
     ===================================================== -->

<script>


/* =====================================================
   ROOM INFORMATION
   ===================================================== */

function updateRoomInfo() {


    const select =
        document.getElementById("room_id");


    const option =
        select.options[
            select.selectedIndex
        ];


    const roomInfo =
        document.getElementById("roomInfo");


    if (
        !option ||
        !option.value
    ) {

        roomInfo.style.display = "none";

        document.getElementById(
            "pricePerHour"
        ).textContent = "0.00";

        calculatePrice();

        return;

    }


    const name =
        option.getAttribute(
            "data-name"
        );


    const description =
        option.getAttribute(
            "data-description"
        );


    const location =
        option.getAttribute(
            "data-location"
        );


    const price =
        option.getAttribute(
            "data-price"
        );


    document.getElementById(
        "roomName"
    ).textContent =
        name;


    document.getElementById(
        "roomDescription"
    ).textContent =
        description ||
        "Room available for booking.";


    document.getElementById(
        "roomLocation"
    ).textContent =
        location ||
        "Not specified";


    document.getElementById(
        "roomPrice"
    ).textContent =
        parseFloat(price)
        .toFixed(2);


    document.getElementById(
        "pricePerHour"
    ).textContent =
        parseFloat(price)
        .toFixed(2);


    roomInfo.style.display =
        "block";


    calculatePrice();

}


/* =====================================================
   PRICE CALCULATION
   ===================================================== */

function calculatePrice() {


    const start =
        document.getElementById(
            "start_time"
        ).value;


    const end =
        document.getElementById(
            "end_time"
        ).value;


    const room =
        document.getElementById(
            "room_id"
        );


    const option =
        room.options[
            room.selectedIndex
        ];


    let price = 0;


    if (
        option &&
        option.value
    ) {

        price =
            parseFloat(
                option.getAttribute(
                    "data-price"
                )
            ) || 0;

    }


    document.getElementById(
        "pricePerHour"
    ).textContent =
        price.toFixed(2);


    if (
        !start ||
        !end
    ) {

        document.getElementById(
            "numberOfHours"
        ).textContent =
            "0";


        document.getElementById(
            "totalPrice"
        ).textContent =
            "0.00";


        return;

    }


    const startParts =
        start.split(":");


    const endParts =
        end.split(":");


    const startMinutes =
        parseInt(startParts[0]) * 60 +
        parseInt(startParts[1]);


    const endMinutes =
        parseInt(endParts[0]) * 60 +
        parseInt(endParts[1]);


    const difference =
        endMinutes - startMinutes;


    if (difference <= 0) {

        document.getElementById(
            "numberOfHours"
        ).textContent =
            "0";


        document.getElementById(
            "totalPrice"
        ).textContent =
            "0.00";


        return;

    }


    if (
        difference % 60 !== 0
    ) {

        document.getElementById(
            "numberOfHours"
        ).textContent =
            "Invalid";


        document.getElementById(
            "totalPrice"
        ).textContent =
            "0.00";


        return;

    }


    const hours =
        difference / 60;


    const total =
        hours * price;


    document.getElementById(
        "numberOfHours"
    ).textContent =
        hours;


    document.getElementById(
        "totalPrice"
    ).textContent =
        total.toFixed(2);

}


/* =====================================================
   FORM VALIDATION
   ===================================================== */

function validateBooking() {


    const room =
        document.getElementById(
            "room_id"
        ).value;


    const date =
        document.getElementById(
            "booking_date"
        ).value;


    const start =
        document.getElementById(
            "start_time"
        ).value;


    const end =
        document.getElementById(
            "end_time"
        ).value;


    if (!room) {

        alert(
            "Please select a room."
        );

        return false;

    }


    if (!date) {

        alert(
            "Please select a booking date."
        );

        return false;

    }


    if (
        !start ||
        !end
    ) {

        alert(
            "Please select start and end time."
        );

        return false;

    }


    if (end <= start) {

        alert(
            "End time must be later than starting time."
        );

        return false;

    }


    const startParts =
        start.split(":");


    const endParts =
        end.split(":");


    const startMinutes =
        parseInt(startParts[0]) * 60 +
        parseInt(startParts[1]);


    const endMinutes =
        parseInt(endParts[0]) * 60 +
        parseInt(endParts[1]);


    const difference =
        endMinutes - startMinutes;


    if (
        difference % 60 !== 0
    ) {

        alert(
            "Please select a whole number of hours."
        );

        return false;

    }


    if (
        difference < 60
    ) {

        alert(
            "Please select at least one hour."
        );

        return false;

    }


    return true;

}


/* =====================================================
   PAGE LOAD
   ===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        updateRoomInfo();

        calculatePrice();

    }
);

</script>


</body>

</html>

<?php

$conn->close();

?>