<?php
session_start();

// Database connection
$conn = new mysqli(
    "db",
    "slotsync_user",
    "slotsync_password",
    "slotsync"
);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Get all rooms
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Choose Your Room - BookMySapce</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        header {
            background: #ffffff;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .logo {
            font-size: 26px;
            font-weight: bold;
            color: #2563eb;
        }

        nav a {
            text-decoration: none;
            margin-left: 20px;
            color: #333;
            font-weight: 500;
        }

        nav a:hover {
            color: #2563eb;
        }

        .page-title {
            text-align: center;
            padding: 40px 20px 20px;
        }

        .page-title h1 {
            color: #1e293b;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #64748b;
        }

        .room-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto 60px;

            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));

            gap: 25px;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;

            box-shadow: 0 5px 15px rgba(0,0,0,0.08);

            transition: 0.3s;
        }

        .room-card:hover {
            transform: translateY(-5px);

            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .room-details {
            padding: 20px;
        }

        .room-details h2 {
            margin-top: 0;
            color: #1e293b;
        }

        .room-details p {
            color: #64748b;
            line-height: 1.6;
        }

        .location {
            font-weight: bold;
            color: #475569;
        }

        .price {
            font-size: 20px;
            color: #16a34a;
            font-weight: bold;
            margin: 15px 0;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;

            padding: 12px 20px;

            text-decoration: none;

            border-radius: 8px;

            font-weight: bold;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .no-room {
            text-align: center;
            font-size: 20px;
            color: red;
        }

        footer {
            background: #1e293b;
            color: white;
            text-align: center;
            padding: 20px;
        }

    </style>

</head>

<body>

<header>

    <div class="logo">
        BookMySapce
    </div>

    <nav>

        <a href="index.php">Home</a>

        <a href="rooms.php">Rooms</a>

        <a href="booking_status.php">Bookings</a>

        <?php if (isset($_SESSION['user_id'])) { ?>

            <a href="logout.php">Logout</a>

        <?php } else { ?>

            <a href="login.php">Login</a>

            <a href="register.php">Register</a>

        <?php } ?>

    </nav>

</header>


<section class="page-title">

    <h1>Choose Your Room</h1>

    <p>
        Select your preferred room and book it according to your required hours.
    </p>

</section>


<div class="room-container">

<?php

if ($result && $result->num_rows > 0) {

    while ($room = $result->fetch_assoc()) {

?>

        <div class="room-card">

            <!-- ROOM IMAGE -->

            <img
                src="<?php echo htmlspecialchars($room['image'] ?? ''); ?>"
                alt="<?php echo htmlspecialchars($room['name'] ?? 'Room'); ?>"
                class="room-image"
            >


            <div class="room-details">

                <!-- ROOM NAME -->

                <h2>
                    <?php echo htmlspecialchars($room['name'] ?? 'Room'); ?>
                </h2>


                <!-- DESCRIPTION -->

                <p>
                    <?php echo htmlspecialchars($room['description'] ?? 'No description available.'); ?>
                </p>


                <!-- LOCATION -->

                <p class="location">

                    📍 Location:
                    <?php echo htmlspecialchars($room['location'] ?? 'N/A'); ?>

                </p>


                <!-- PRICE -->

                <div class="price">

                    ₹<?php echo number_format((float)$room['price_per_hour'], 2); ?>

                    / hour

                </div>


                <!-- BOOK NOW -->

                <?php if (isset($_SESSION['user_id'])) { ?>

                    <a
                        href="booking.php?room_id=<?php echo $room['id']; ?>"
                        class="btn"
                    >
                        Book Now
                    </a>

                <?php } else { ?>

                    <a
                        href="login.php"
                        class="btn"
                    >
                        Login to Book
                    </a>

                <?php } ?>

            </div>

        </div>

<?php

    }

} else {

?>

    <p class="no-room">

        No rooms available.

    </p>

<?php

}

?>

</div>


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