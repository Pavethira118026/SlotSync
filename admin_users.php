<?php
session_start();
include "config/db.php";

/* Get all registered users */
$sql = "SELECT id, name, email FROM users ORDER BY id DESC";

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

<title>Admin Users - SlotSync</title>

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

/* HEADER */

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

/* TITLE */

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

/* BACK BUTTON */

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

/* TABLE */

.table-container {
    width: 90%;
    max-width: 1000px;

    margin: 20px auto 60px;

    background: white;

    border-radius: 15px;

    overflow-x: auto;

    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #2563eb;
    color: white;

    padding: 16px;

    text-align: center;
}

td {
    padding: 15px;

    text-align: center;

    border-bottom: 1px solid #e2e8f0;
}

tr:hover {
    background: #f8fafc;
}

/* USER ID */

.user-id {
    font-weight: bold;
    color: #2563eb;
}

/* USER NAME */

.user-name {
    font-weight: bold;
    color: #334155;
}

/* EMAIL */

.email {
    color: #475569;
}

/* NO USERS */

.no-users {
    text-align: center;
    padding: 60px;
    color: #64748b;
}

.no-users h2 {
    margin-bottom: 10px;
    color: #334155;
}

/* FOOTER */

footer {
    background: #0f172a;
    color: white;

    text-align: center;

    padding: 20px;

    margin-top: 50px;
}

/* MOBILE */

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

    .table-container {
        width: 95%;
    }

}

</style>

</head>

<body>

<!-- HEADER -->

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


<!-- TITLE -->

<div class="page-title">

    <h1>
        Registered Users
    </h1>

    <p>
        View all users registered in SlotSync
    </p>

</div>


<!-- BACK BUTTON -->

<a href="admin.php" class="back-btn">
    ← Back to Dashboard
</a>


<!-- USERS TABLE -->

<?php if ($result->num_rows > 0): ?>

<div class="table-container">

<table>

<thead>

<tr>

    <th>
        User ID
    </th>

    <th>
        Name
    </th>

    <th>
        Email
    </th>

</tr>

</thead>

<tbody>

<?php while ($user = $result->fetch_assoc()): ?>

<tr>

    <td class="user-id">

        <?php
        echo $user['id'];
        ?>

    </td>

    <td class="user-name">

        <?php
        echo htmlspecialchars($user['name']);
        ?>

    </td>

    <td class="email">

        <?php
        echo htmlspecialchars($user['email']);
        ?>

    </td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

<?php else: ?>

<div class="table-container">

    <div class="no-users">

        <h2>
            No Users Found
        </h2>

        <p>
            There are currently no registered users.
        </p>

    </div>

</div>

<?php endif; ?>


<!-- FOOTER -->

<footer>

    <p>
        © 2026 BookMySpace - Hourly Room Booking System
    </p>

</footer>


</body>

</html>

<?php
$conn->close();
?>