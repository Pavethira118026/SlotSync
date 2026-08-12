<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - SlotSync</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

    <header>

        <div class="logo">
            BookMySapce
        </div>

        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="logout.php">
                Logout
            </a>

        </nav>

    </header>


    <section style="
        min-height:80vh;
        display:flex;
        justify-content:center;
        align-items:center;
        text-align:center;
    ">

        <div>

            <h1>
                Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
            </h1>

            <p style="margin-top:15px;">
                You have successfully logged in to SlotSync.
            </p>

            <br>

            <a href="index.php"
               class="btn">
                Go to Home
            </a>

        </div>

    </section>

</body>

</html>