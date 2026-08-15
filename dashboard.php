<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Lost & Found</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<header class="header">

    <div class="logo">
        🔎 Lost<span>&</span>Found
    </div>


    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="items.php">
            Browse Items
        </a>

        <a href="report.php">
            Report Item
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<section class="dashboard">

    <div class="welcome">

        <p>Welcome back 👋</p>

        <h1>
            <?php
            echo htmlspecialchars(
                $_SESSION["user_name"]
            );
            ?>
        </h1>

        <p>
            <?php
            echo htmlspecialchars(
                $_SESSION["user_email"]
            );
            ?>
        </p>

    </div>


    <div class="dashboard-grid">


        <div class="dashboard-card">

            <div class="dashboard-icon">
                📝
            </div>

            <h2>Report Lost Item</h2>

            <p>
                Tell the community about something
                you have lost.
            </p>

            <a
                href="report.php?type=Lost"
                class="btn primary"
            >
                Report Lost
            </a>

        </div>


        <div class="dashboard-card">

            <div class="dashboard-icon">
                🎁
            </div>

            <h2>Report Found Item</h2>

            <p>
                Help someone find their lost belongings.
            </p>

            <a
                href="report.php?type=Found"
                class="btn primary"
            >
                Report Found
            </a>

        </div>


        <div class="dashboard-card">

            <div class="dashboard-icon">
                🔎
            </div>

            <h2>Browse Items</h2>

            <p>
                Search through lost and found reports.
            </p>

            <a
                href="items.php"
                class="btn secondary"
            >
                Browse Items
            </a>

        </div>


    </div>

</section>


<footer>

    <p>
        © 2026 Lost & Found
    </p>

</footer>


</body>

</html>