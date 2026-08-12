<?php
session_start();

include "config/db.php";

// Check admin login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get room ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid room ID.");
}

$room_id = intval($_GET['id']);

$message = "";
$error = "";

// Fetch room details
$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Room not found.");
}

$room = $result->fetch_assoc();

$stmt->close();


// -----------------------------
// UPDATE ROOM
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $room_name = trim($_POST['room_name']);
    $room_type = trim($_POST['room_type']);
    $capacity = intval($_POST['capacity']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    // Basic validation
    if (
        empty($room_name) ||
        empty($room_type) ||
        $capacity <= 0 ||
        $price_per_hour <= 0 ||
        empty($description) ||
        empty($status)
    ) {

        $error = "Please fill all fields correctly.";

    } else {

        // Update database
        $update_sql = "
            UPDATE rooms
            SET
                room_name = ?,
                room_type = ?,
                capacity = ?,
                price_per_hour = ?,
                description = ?,
                status = ?
            WHERE id = ?
        ";

        $update_stmt = $conn->prepare($update_sql);

        $update_stmt->bind_param(
            "ssidssi",
            $room_name,
            $room_type,
            $capacity,
            $price_per_hour,
            $description,
            $status,
            $room_id
        );

        if ($update_stmt->execute()) {

            $message = "Room updated successfully!";

            // Update displayed values
            $room['room_name'] = $room_name;
            $room['room_type'] = $room_type;
            $room['capacity'] = $capacity;
            $room['price_per_hour'] = $price_per_hour;
            $room['description'] = $description;
            $room['status'] = $status;

        } else {

            $error = "Database Error: " . $update_stmt->error;
        }

        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Room - SlotSync</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #172033;
        }

        header {
            background: #0f172a;
            color: white;
            padding: 18px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 25px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 25px;
            font-weight: bold;
        }

        nav a:hover {
            color: #38bdf8;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 45px auto;
        }

        .form-card {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        .form-card h2 {
            text-align: center;
            color: #0f172a;
            margin-bottom: 30px;
        }

        .message {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #334155;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .update-btn {
            flex: 1;
            padding: 13px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #15803d;
        }

        .back-btn {
            flex: 1;
            padding: 13px;
            background: #475569;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
        }

        .back-btn:hover {
            background: #334155;
        }

        .current-image {
            margin-top: 10px;
            text-align: center;
        }

        .current-image img {
            width: 220px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
        }

        footer {
            text-align: center;
            padding: 25px;
            margin-top: 50px;
            color: #64748b;
        }

        @media (max-width: 600px) {

            header {
                padding: 18px;
                flex-direction: column;
                gap: 15px;
            }

            nav a {
                margin: 0 8px;
            }

            .container {
                width: 95%;
            }

            .form-card {
                padding: 22px;
            }

            .buttons {
                flex-direction: column;
            }
        }

    </style>

</head>

<body>

<header>

    <h1>SlotSync</h1>

    <nav>
        <a href="admin_rooms.php">Admin Rooms</a>
        <a href="rooms.php">Rooms</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>


<div class="container">

    <div class="form-card">

        <h2>Edit Room</h2>

        <?php if (!empty($message)): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- Room Name -->

            <div class="form-group">

                <label for="room_name">
                    Room Name
                </label>

                <input
                    type="text"
                    id="room_name"
                    name="room_name"
                    value="<?php echo htmlspecialchars($room['room_name']); ?>"
                    required
                >

            </div>


            <!-- Room Type -->

            <div class="form-group">

                <label for="room_type">
                    Room Type
                </label>

                <select
                    id="room_type"
                    name="room_type"
                    required
                >

                    <option value="Single Room"
                        <?php if ($room['room_type'] == 'Single Room') echo 'selected'; ?>>
                        Single Room
                    </option>

                    <option value="Family Room"
                        <?php if ($room['room_type'] == 'Family Room') echo 'selected'; ?>>
                        Family Room
                    </option>

                    <option value="Pool Type Room"
                        <?php if ($room['room_type'] == 'Pool Type Room') echo 'selected'; ?>>
                        Pool Type Room
                    </option>

                    <option value="Deluxe Room"
                        <?php if ($room['room_type'] == 'Deluxe Room') echo 'selected'; ?>>
                        Deluxe Room
                    </option>

                    <option value="Double Sharing Bed Room"
                        <?php if ($room['room_type'] == 'Double Sharing Bed Room') echo 'selected'; ?>>
                        Double Sharing Bed Room
                    </option>

                    <option value="Restaurant Room"
                        <?php if ($room['room_type'] == 'Restaurant Room') echo 'selected'; ?>>
                        Restaurant Room
                    </option>

                </select>

            </div>


            <!-- Capacity -->

            <div class="form-group">

                <label for="capacity">
                    Capacity
                </label>

                <input
                    type="number"
                    id="capacity"
                    name="capacity"
                    min="1"
                    value="<?php echo htmlspecialchars($room['capacity']); ?>"
                    required
                >

            </div>


            <!-- Price Per Hour -->

            <div class="form-group">

                <label for="price_per_hour">
                    Price Per Hour (₹)
                </label>

                <input
                    type="number"
                    id="price_per_hour"
                    name="price_per_hour"
                    min="1"
                    step="0.01"
                    value="<?php echo htmlspecialchars($room['price_per_hour']); ?>"
                    required
                >

            </div>


            <!-- Description -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    required
                ><?php echo htmlspecialchars($room['description']); ?></textarea>

            </div>


            <!-- Status -->

            <div class="form-group">

                <label for="status">
                    Room Status
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <option value="Available"
                        <?php if ($room['status'] == 'Available') echo 'selected'; ?>>
                        Available
                    </option>

                    <option value="Unavailable"
                        <?php if ($room['status'] == 'Unavailable') echo 'selected'; ?>>
                        Unavailable
                    </option>

                </select>

            </div>


            <!-- Current Image -->

            <?php if (!empty($room['image'])): ?>

                <div class="form-group">

                    <label>
                        Current Room Image
                    </label>

                    <div class="current-image">

                        <img
                            src="rooms/<?php echo htmlspecialchars($room['image']); ?>"
                            alt="Room Image"
                        >

                    </div>

                </div>

            <?php endif; ?>


            <!-- Buttons -->

            <div class="buttons">

                <button
                    type="submit"
                    class="update-btn"
                >
                    Update Room
                </button>

                <a
                    href="admin_rooms.php"
                    class="back-btn"
                >
                    Back
                </a>

            </div>

        </form>

    </div>

</div>


<footer>

    © 2026 BookMySpaceS- Hourly Room Booking System

</footer>

</body>

</html>