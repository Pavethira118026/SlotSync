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

/* CHECK BOOKING ID */
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = (int) $_GET["id"];
$user_id = (int) $_SESSION["user_id"];

/* CANCEL ONLY THE LOGGED-IN USER'S BOOKING */
$stmt = $conn->prepare("
    UPDATE bookings
    SET booking_status = 'Cancelled'
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    header("Location: my_bookings.php?message=cancelled");
    exit();
} else {
    echo "Unable to cancel booking.";
}

$stmt->close();
$conn->close();
?>