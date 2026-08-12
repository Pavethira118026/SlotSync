<?php
session_start();

include "config/db.php";

/* =========================
   DELETE ROOM
========================= */

if (isset($_GET['delete'])) {

    $room_id = intval($_GET['delete']);

    $delete_sql = "DELETE FROM rooms WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);

    if ($delete_stmt) {

        $delete_stmt->bind_param("i", $room_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        header("Location: admin_rooms.php");
        exit();

    } else {

        die("Delete Error: " . $conn->error);
    }
}


/* =========================
   GET ALL ROOMS
========================= */

$sql = "SELECT 
            id,
            room_name,
            room_type,
            capacity,
            price_per_hour,
            description,
            image,
            status
        FROM rooms
        ORDER BY id DESC";

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

    <title>Manage Rooms - SlotSync</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #172033;
        }

        /* =========================
           HEADER
        ========================= */

        header {
            background: #0f172a;
            color: white;
            padding: 18px 40px;
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


        /* =========================
           PAGE
        ========================= */

        .container {
            width: 94%;
            max-width: 1250px;
            margin: 35px auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .page-title h2 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .page-title p {
            color: #64748b;
            font-size: 16px;
        }


        /* =========================
           ADD ROOM BUTTON
        ========================= */

        .top-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .add-btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 7px;
            font-weight: bold;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }


        /* =========================
           TABLE
        ========================= */

        .table-container {
            background: white;
            border-radius: 14px;
            overflow-x: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
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
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8fafc;
        }


        /* =========================
           ROOM IMAGE
        ========================= */

        .room-image {
            width: 110px;
            height: 75px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
            margin: auto;
        }

        .no-image {
            width: 110px;
            height: 75px;
            background: #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            color: #64748b;
            font-size: 13px;
        }


        /* =========================
           ROOM NAME
        ========================= */

        .room-name {
            font-weight: bold;
            color: #0f172a;
        }

        .room-type {
            color: #64748b;
            font-size: 13px;
            margin-top: 4px;
        }


        /* =========================
           PRICE
        ========================= */

        .price {
            font-weight: bold;
            color: #15803d;
            white-space: nowrap;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .available {
            background: #dcfce7;
            color: #166534;
        }

        .unavailable {
            background: #fee2e2;
            color: #991b1b;
        }


        /* =========================
           ACTION BUTTONS
        ========================= */

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .edit-btn {
            display: inline-block;
            padding: 8px 13px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .edit-btn:hover {
            background: #15803d;
        }

        .delete-btn {
            display: inline-block;
            padding: 8px 13px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }


        /* =========================
           EMPTY MESSAGE
        ========================= */

        .no-rooms {
            text-align: center;
            padding: 50px;
            color: #64748b;
            font-size: 18px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            text-align: center;
            padding: 25px;
            margin-top: 40px;
            background: #0f172a;
            color: white;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 700px) {

            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            nav a {
                margin: 0 8px;
                font-size: 14px;
            }

            .page-title h2 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     HEADER
========================= -->

<header>

    <h1>SlotSync Admin</h1>

    <nav>

        <a href="index.php">Home</a>

        <a href="admin_rooms.php">Rooms</a>

        <a href="dashboard.php">Dashboard</a>

        <a href="logout.php">Logout</a>

    </nav>

</header>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="container">


    <div class="page-title">

        <h2>Manage Rooms</h2>

        <p>Add, edit and delete rooms from the SlotSync booking system.</p>

    </div>


    <div class="top-section">

        <a href="add_room.php" class="add-btn">
            + Add New Room
        </a>

    </div>


    <div class="table-container">

        <?php if ($result->num_rows > 0): ?>

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Image</th>

                    <th>Room Name</th>

                    <th>Room Type</th>

                    <th>Capacity</th>

                    <th>Price / Hour</th>

                    <th>Description</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php while ($room = $result->fetch_assoc()): ?>

                <tr>

                    <!-- ID -->

                    <td>
                        <?php echo htmlspecialchars($room['id']); ?>
                    </td>


                    <!-- IMAGE -->

                    <td>

                        <?php

                        if (!empty($room['image'])) {

                            /*
                             * Images are stored inside:
                             *
                             * SlotSync/rooms/
                             */

                            $image_path = "rooms/" . basename($room['image']);

                        ?>

                            <img
                                src="<?php echo htmlspecialchars($image_path); ?>"
                                alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                                class="room-image"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >

                            <div class="no-image" style="display:none;">
                                No Image
                            </div>

                        <?php

                        } else {

                        ?>

                            <div class="no-image">
                                No Image
                            </div>

                        <?php

                        }

                        ?>

                    </td>


                    <!-- ROOM NAME -->

                    <td>

                        <div class="room-name">

                            <?php
                            echo htmlspecialchars($room['room_name']);
                            ?>

                        </div>

                    </td>


                    <!-- ROOM TYPE -->

                    <td>

                        <div class="room-type">

                            <?php
                            echo htmlspecialchars($room['room_type']);
                            ?>

                        </div>

                    </td>


                    <!-- CAPACITY -->

                    <td>

                        <?php
                        echo htmlspecialchars($room['capacity']);
                        ?>

                        Person(s)

                    </td>


                    <!-- PRICE PER HOUR -->

                    <td>

                        <div class="price">

                            ₹<?php
                            echo number_format(
                                (float)$room['price_per_hour'],
                                2
                            );
                            ?>

                            / hour

                        </div>

                    </td>


                    <!-- DESCRIPTION -->

                    <td>

                        <?php

                        $description = $room['description'];

                        if (strlen($description) > 100) {

                            echo htmlspecialchars(
                                substr($description, 0, 100)
                            ) . "...";

                        } else {

                            echo htmlspecialchars($description);

                        }

                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <?php

                        $status = strtolower(
                            trim($room['status'] ?? 'Available')
                        );

                        if ($status === 'available') {

                        ?>

                            <span class="status available">
                                Available
                            </span>

                        <?php

                        } else {

                        ?>

                            <span class="status unavailable">
                                <?php
                                echo htmlspecialchars(
                                    $room['status']
                                );
                                ?>
                            </span>

                        <?php

                        }

                        ?>

                    </td>


                    <!-- ACTION -->

                    <td>

                        <div class="action-buttons">

                            <!-- EDIT BUTTON -->

                            <a
                                href="edit_room.php?id=<?php echo $room['id']; ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>


                            <!-- DELETE BUTTON -->

                            <a
                                href="admin_rooms.php?delete=<?php echo $room['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this room?');"
                            >
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>


        <?php else: ?>


            <div class="no-rooms">

                <p>No rooms found.</p>

                <a href="add_room.php" class="add-btn">
                    Add Your First Room
                </a>

            </div>


        <?php endif; ?>

    </div>

</div>


<!-- =========================
     FOOTER
========================= -->

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