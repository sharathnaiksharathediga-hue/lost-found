<?php

session_start();

require_once "config/functions.php";


// User must be logged in
requireLogin();


// JSON file
$itemFile = "data/items.json";


// Read items
$items = readJSON($itemFile);


// Current logged-in user
$currentUserID = $_SESSION["user_id"];


// Store user's reports
$myReports = [];


// Find user's reports
foreach ($items as $item) {

    if (
        isset($item["user_id"]) &&
        $item["user_id"] === $currentUserID
    ) {

        $myReports[] = $item;
    }
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

    <title>
        My Reports - Lost & Found
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<!-- HEADER -->

<header>

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

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="report.php">
            Report Item
        </a>

        <a href="my-reports.php">
            My Reports
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<!-- MAIN -->

<main class="reports-container">


    <div class="reports-header">

        <div>

            <p class="small-title">
                YOUR ACTIVITY
            </p>

            <h1>
                My Reports
            </h1>

            <p>
                Manage the lost and found items
                you have reported.
            </p>

        </div>


        <a
            href="report.php"
            class="btn primary"
        >
            + Report Item
        </a>

    </div>


    <?php if (count($myReports) === 0): ?>


        <!-- NO REPORTS -->

        <div class="empty-reports">

            <div class="empty-icon">
                📋
            </div>


            <h2>
                No Reports Yet
            </h2>


            <p>
                You haven't reported any lost
                or found items yet.
            </p>


            <a
                href="report.php"
                class="btn primary"
            >
                + Create Your First Report
            </a>

        </div>


    <?php else: ?>


        <!-- REPORT COUNT -->

        <div class="report-count">

            <strong>
                <?php echo count($myReports); ?>
            </strong>

            report(s) found

        </div>


        <!-- REPORTS GRID -->

        <div class="reports-grid">


            <?php foreach ($myReports as $item): ?>


                <div class="report-card">


                    <!-- IMAGE -->

                    <div class="report-image">

                        <?php

                        if (
                            !empty($item["image"]) &&
                            file_exists($item["image"])
                        ):

                        ?>

                            <img
                                src="<?php
                                    echo htmlspecialchars(
                                        $item["image"]
                                    );
                                ?>"
                                alt="<?php
                                    echo htmlspecialchars(
                                        $item["title"]
                                    );
                                ?>"
                            >

                        <?php else: ?>

                            <div class="no-image">
                                📦
                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- CARD CONTENT -->

                    <div class="report-content">


                        <!-- TYPE -->

                        <?php if (
                            isset($item["type"]) &&
                            $item["type"] === "Lost"
                        ): ?>

                            <span class="badge lost">
                                Lost
                            </span>

                        <?php else: ?>

                            <span class="badge found">
                                Found
                            </span>

                        <?php endif; ?>


                        <!-- TITLE -->

                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $item["title"]
                            );
                            ?>

                        </h2>


                        <!-- CATEGORY -->

                        <p class="category">

                            📁

                            <?php
                            echo htmlspecialchars(
                                $item["category"]
                            );
                            ?>

                        </p>


                        <!-- LOCATION -->

                        <p>

                            📍

                            <?php
                            echo htmlspecialchars(
                                $item["location"]
                            );
                            ?>

                        </p>


                        <!-- DATE -->

                        <p>

                            📅

                            <?php
                            echo htmlspecialchars(
                                $item["date"]
                            );
                            ?>

                        </p>


                        <!-- STATUS -->

                        <?php

                        $status =
                            $item["status"]
                            ?? "Active";

                        ?>


                        <span
                            class="status
                            <?php
                            echo strtolower(
                                $status
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $status
                            );
                            ?>

                        </span>


                        <!-- BUTTONS -->

                        <div class="report-actions">


                            <a
                                href="details.php?id=<?php
                                    echo urlencode(
                                        $item["id"]
                                    );
                                ?>"
                                class="btn view-btn"
                            >
                                👁 View
                            </a>


                            <a
                                href="edit-item.php?id=<?php
                                    echo urlencode(
                                        $item["id"]
                                    );
                                ?>"
                                class="btn edit-btn"
                            >
                                ✏️ Edit
                            </a>


                            <a
                                href="delete-item.php?id=<?php
                                    echo urlencode(
                                        $item["id"]
                                    );
                                ?>"
                                class="btn delete-btn"
                                onclick="return confirm(
                                    'Are you sure you want to delete this report?'
                                );"
                            >
                                🗑 Delete
                            </a>


                        </div>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</main>


<!-- FOOTER -->

<footer>

    <p>
        © 2026 Lost & Found
    </p>

</footer>


<script src="js/script.js"></script>


</body>

</html>