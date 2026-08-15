<?php

session_start();

require_once "config/database.php";


// Count Lost Items
$lostQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM items
     WHERE type = 'Lost'"
);

$lostCount = 0;

if ($lostQuery) {

    $lostData = $lostQuery->fetch_assoc();

    $lostCount = (int) $lostData["total"];
}


// Count Found Items
$foundQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM items
     WHERE type = 'Found'"
);

$foundCount = 0;

if ($foundQuery) {

    $foundData = $foundQuery->fetch_assoc();

    $foundCount = (int) $foundData["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Lost & Found</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

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

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

            <a
                href="register.php"
                class="nav-register"
            >
                Register
            </a>

        <?php endif; ?>

    </nav>

</header>


<section class="hero">

    <div class="hero-content">

        <p class="small-title">
            COMMUNITY LOST & FOUND
        </p>

        <h1>
            Lost something?
            <span>Let's find it.</span>
        </h1>

        <p>
            Report lost items, post found items and help
            people reconnect with their belongings.
        </p>

        <div class="hero-buttons">

            <a
                href="report.php?type=Lost"
                class="btn primary"
            >
                Report Lost Item
            </a>

            <a
                href="report.php?type=Found"
                class="btn secondary"
            >
                Report Found Item
            </a>

        </div>

    </div>

</section>


<section class="stats">

    <div class="stat-card">

        <h2>
            <?php echo $lostCount; ?>
        </h2>

        <p>
            Lost Items
        </p>

    </div>


    <div class="stat-card">

        <h2>
            <?php echo $foundCount; ?>
        </h2>

        <p>
            Found Items
        </p>

    </div>


    <div class="stat-card">

        <h2>
            <?php
            echo $lostCount + $foundCount;
            ?>
        </h2>

        <p>
            Total Reports
        </p>

    </div>

</section>


<section class="how">

    <h2>
        How It Works
    </h2>

    <p class="section-description">
        Helping people recover their belongings
        in three simple steps.
    </p>


    <div class="steps">


        <div class="step">

            <div>📝</div>

            <h3>
                Report
            </h3>

            <p>
                Report an item you lost or found.
            </p>

        </div>


        <div class="step">

            <div>🔎</div>

            <h3>
                Search
            </h3>

            <p>
                Browse items reported by the community.
            </p>

        </div>


        <div class="step">

            <div>🤝</div>

            <h3>
                Reconnect
            </h3>

            <p>
                Help return the item to its owner.
            </p>

        </div>


    </div>

</section>


<footer>

    <p>
        © 2026 Lost & Found.
        Built with PHP, HTML, CSS & JavaScript.
    </p>

</footer>


</body>

</html>