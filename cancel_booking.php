<?php

session_start();

include "config/db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check booking ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID is missing.");
}

$booking_id = intval($_GET['id']);

// Cancel only the logged-in user's booking
$sql = "UPDATE bookings
        SET booking_status = 'Cancelled'
        WHERE id = ?
        AND user_id = ?
        AND booking_status = 'Confirmed'";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $booking_id,
    $user_id
);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        header("Location: my_bookings.php?cancelled=1");
        exit();

    } else {

        echo "
        <h2>Unable to cancel booking.</h2>
        <p>The booking may already be cancelled.</p>
        <a href='my_bookings.php'>Back to My Bookings</a>
        ";

    }

} else {

    echo "
    <h2>Cancellation Error</h2>
    <p>" . htmlspecialchars($stmt->error) . "</p>
    <a href='my_bookings.php'>Back to My Bookings</a>
    ";

}

$stmt->close();
$conn->close();

?>