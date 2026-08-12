<?php
session_start();

include "config/db.php";

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* Check room */
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    die("Room not selected.");
}

$room_id = intval($_GET['room_id']);


/* Get room details */
$sql = "SELECT *
        FROM rooms
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Room not found.");
}

$room = $result->fetch_assoc();

$stmt->close();


$message = "";
$message_type = "";


/* =====================================================
   BOOKING
   ===================================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_date = $_POST['booking_date'] ?? "";
    $start_time   = $_POST['start_time'] ?? "";
    $end_time     = $_POST['end_time'] ?? "";


    /* Basic validation */

    if (
        empty($booking_date) ||
        empty($start_time) ||
        empty($end_time)
    ) {

        $message = "Please select date, start time and end time.";
        $message_type = "error";

    } else {

        /*
         * Convert HH:MM into minutes
         */

        list($start_hour, $start_minute) =
            array_map('intval', explode(':', $start_time));

        list($end_hour, $end_minute) =
            array_map('intval', explode(':', $end_time));


        $start_minutes =
            ($start_hour * 60) + $start_minute;

        $end_minutes =
            ($end_hour * 60) + $end_minute;


        /* Check end time */

        if ($end_minutes <= $start_minutes) {

            $message =
                "End time must be later than start time.";

            $message_type = "error";

        } else {

            /*
             * Calculate total minutes
             */

            $total_minutes =
                $end_minutes - $start_minutes;


            /*
             * Convert minutes to hours
             *
             * Example:
             * 60 minutes = 1 hour
             * 120 minutes = 2 hours
             * 90 minutes = 1.5 hours
             */

            $total_hours =
                $total_minutes / 60;


            /*
             * Minimum 1 hour
             */

            if ($total_minutes < 60) {

                $message =
                    "Please select at least 1 hour.";

                $message_type = "error";

            } else {

                /* Check previous date */

                $today = date("Y-m-d");

                if ($booking_date < $today) {

                    $message =
                        "You cannot book a previous date.";

                    $message_type = "error";

                } else {

                    /*
                     * Check overlapping booking
                     */

                    $check_sql = "
                        SELECT id
                        FROM bookings
                        WHERE room_id = ?
                        AND booking_date = ?
                        AND booking_status = 'Confirmed'
                        AND start_time < ?
                        AND end_time > ?
                    ";

                    $check_stmt =
                        $conn->prepare($check_sql);

                    if (!$check_stmt) {
                        die(
                            "Database Error: "
                            . $conn->error
                        );
                    }


                    $check_stmt->bind_param(
                        "isss",
                        $room_id,
                        $booking_date,
                        $end_time,
                        $start_time
                    );


                    $check_stmt->execute();

                    $check_result =
                        $check_stmt->get_result();


                    if ($check_result->num_rows > 0) {

                        $message =
                            "Sorry! This room is already booked for the selected time.";

                        $message_type = "error";

                    } else {

                        /*
                         * Calculate amount
                         */

                        $price_per_hour =
                            floatval(
                                $room['price_per_hour']
                            );


                        $total_amount =
                            $total_hours *
                            $price_per_hour;


                        /*
                         * Insert booking
                         */

                        $insert_sql = "
                            INSERT INTO bookings
                            (
                                user_id,
                                room_id,
                                booking_date,
                                start_time,
                                end_time,
                                total_hours,
                                total_amount,
                                booking_status,
                                created_at
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
                                'Confirmed',
                                NOW()
                            )
                        ";


                        $insert_stmt =
                            $conn->prepare($insert_sql);


                        if (!$insert_stmt) {

                            die(
                                "Database Error: "
                                . $conn->error
                            );

                        }


                        $insert_stmt->bind_param(
                            "iisssdd",
                            $user_id,
                            $room_id,
                            $booking_date,
                            $start_time,
                            $end_time,
                            $total_hours,
                            $total_amount
                        );


                        if ($insert_stmt->execute()) {

                            $booking_id =
                                $conn->insert_id;


                            header(
                                "Location: booking_success.php?id="
                                . $booking_id
                            );

                            exit();

                        } else {

                            $message =
                                "Booking failed. Please try again.";

                            $message_type = "error";

                        }


                        $insert_stmt->close();

                    }


                    $check_stmt->close();

                }

            }

        }

    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Book Room - SlotSync</title>


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


.booking-container {
    width: 90%;
    max-width: 900px;

    margin: 20px auto 60px;

    background: white;

    padding: 30px;

    border-radius: 15px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);
}


.room-info {
    text-align: center;
    margin-bottom: 30px;
}


.room-info img {
    width: 100%;
    max-width: 450px;
    height: 260px;

    object-fit: cover;

    border-radius: 12px;

    margin-bottom: 20px;
}


.room-info h2 {
    font-size: 28px;
    color: #0f172a;
}


.room-type {
    color: #64748b;
    margin-top: 8px;
}


.price {
    margin-top: 12px;

    font-size: 22px;

    font-weight: bold;

    color: #15803d;
}


.message {
    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

    font-weight: bold;
}


.error {
    background: #fee2e2;

    color: #b91c1c;

    border: 1px solid #fecaca;
}


.form-group {
    margin-bottom: 20px;
}


.form-group label {
    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #334155;
}


.form-group input {
    width: 100%;

    padding: 13px;

    border: 1px solid #cbd5e1;

    border-radius: 7px;

    font-size: 15px;
}


.form-group input:focus {
    outline: none;

    border-color: #2563eb;
}


.time-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}


.price-preview {
    background: #eff6ff;

    border: 1px solid #bfdbfe;

    padding: 20px;

    border-radius: 10px;

    text-align: center;

    margin: 25px 0;
}


.price-preview p {
    margin: 8px 0;
}


#hours {
    font-weight: bold;
    color: #2563eb;
}


#amount {
    font-size: 25px;

    font-weight: bold;

    color: #15803d;
}


.buttons {
    display: flex;

    gap: 15px;
}


.back-btn {
    flex: 1;

    padding: 14px;

    background: #64748b;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 7px;

    font-weight: bold;
}


.book-btn {
    flex: 1;

    padding: 14px;

    background: #2563eb;

    color: white;

    border: none;

    border-radius: 7px;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}


.book-btn:hover {
    background: #1d4ed8;
}


.back-btn:hover {
    background: #475569;
}


footer {
    background: #0f172a;

    color: white;

    text-align: center;

    padding: 20px;

    margin-top: 50px;
}


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

    .time-grid {
        grid-template-columns: 1fr;
    }

    .buttons {
        flex-direction: column;
    }

    .booking-container {
        width: 95%;
        padding: 20px;
    }

}

</style>

</head>


<body>


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


<div class="page-title">

    <h1>
        Book Your Room
    </h1>

    <p>
        Select your date and preferred hours
    </p>

</div>


<div class="booking-container">


    <!-- ROOM -->

    <div class="room-info">

        <?php if (!empty($room['image'])): ?>

            <img
                src="image/rooms/<?php
                    echo htmlspecialchars(
                        $room['image']
                    );
                ?>"
                alt="Room Image"
            >

        <?php endif; ?>


        <h2>

            <?php
            echo htmlspecialchars(
                $room['room_name']
            );
            ?>

        </h2>


        <p class="room-type">

            <?php
            echo htmlspecialchars(
                $room['room_type']
            );
            ?>

        </p>


        <p class="price">

            ₹<?php
            echo number_format(
                $room['price_per_hour'],
                2
            );
            ?>

            / hour

        </p>

    </div>


    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="message error">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <form method="POST">


        <!-- DATE -->

        <div class="form-group">

            <label for="booking_date">
                Booking Date
            </label>

            <input
                type="date"
                id="booking_date"
                name="booking_date"
                min="<?php echo date('Y-m-d'); ?>"
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
                    id="start_time"
                    name="start_time"
                    required
                >

            </div>


            <div class="form-group">

                <label for="end_time">
                    End Time
                </label>

                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                    required
                >

            </div>


        </div>


        <!-- PRICE -->

        <div class="price-preview">

            <p>

                Booking Duration:

                <span id="hours">
                    0
                </span>

                hour(s)

            </p>


            <p>

                Estimated Total:

                <span id="amount">
                    ₹0.00
                </span>

            </p>

        </div>


        <!-- BUTTONS -->

        <div class="buttons">

            <a
                href="rooms.php"
                class="back-btn"
            >
                ← Back to Rooms
            </a>


            <button
                type="submit"
                class="book-btn"
            >
                Confirm Booking
            </button>

        </div>


    </form>


</div>


<footer>

    <p>
        © 2026 SlotSync -
        Hourly Room Booking System
    </p>

</footer>


<script>

/* =====================================================
   PRICE
   ===================================================== */

const pricePerHour =
    <?php echo floatval($room['price_per_hour']); ?>;


/* =====================================================
   ELEMENTS
   ===================================================== */

const startInput =
    document.getElementById("start_time");

const endInput =
    document.getElementById("end_time");

const hoursDisplay =
    document.getElementById("hours");

const amountDisplay =
    document.getElementById("amount");


/* =====================================================
   CALCULATE HOURS
   ===================================================== */

function calculatePrice() {

    if (
        startInput.value === "" ||
        endInput.value === ""
    ) {

        hoursDisplay.textContent = "0";

        amountDisplay.textContent = "₹0.00";

        return;

    }


    const startParts =
        startInput.value.split(":");

    const endParts =
        endInput.value.split(":");


    const startMinutes =
        (parseInt(startParts[0]) * 60)
        +
        parseInt(startParts[1]);


    const endMinutes =
        (parseInt(endParts[0]) * 60)
        +
        parseInt(endParts[1]);


    if (endMinutes <= startMinutes) {

        hoursDisplay.textContent = "0";

        amountDisplay.textContent = "₹0.00";

        return;

    }


    const totalMinutes =
        endMinutes - startMinutes;


    const totalHours =
        totalMinutes / 60;


    const totalAmount =
        totalHours * pricePerHour;


    hoursDisplay.textContent =
        totalHours.toFixed(2);


    amountDisplay.textContent =
        "₹" + totalAmount.toFixed(2);

}


/* =====================================================
   UPDATE PRICE
   ===================================================== */

startInput.addEventListener(
    "change",
    calculatePrice
);

endInput.addEventListener(
    "change",
    calculatePrice
);


/* =====================================================
   FORM VALIDATION
   ===================================================== */

function validateBooking() {

    const start =
        startInput.value;

    const end =
        endInput.value;


    if (!start || !end) {

        alert(
            "Please select start time and end time."
        );

        return false;

    }


    const startParts =
        start.split(":");

    const endParts =
        end.split(":");


    const startMinutes =
        (parseInt(startParts[0]) * 60)
        +
        parseInt(startParts[1]);


    const endMinutes =
        (parseInt(endParts[0]) * 60)
        +
        parseInt(endParts[1]);


    const totalMinutes =
        endMinutes - startMinutes;


    if (totalMinutes < 60) {

        alert(
            "Please select at least 1 hour."
        );

        return false;

    }


    return true;

}

</script>


</body>

</html>


<?php
$conn->close();
?>