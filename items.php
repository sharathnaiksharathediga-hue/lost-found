<?php

$dataFile = "data/items.json";

$items = [];

if (file_exists($dataFile)) {

    $json = file_get_contents($dataFile);

    $items = json_decode($json, true);

    if (!is_array($items)) {
        $items = [];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Browse Items - Lost & Found</title>

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

<section class="browse">

    <h1>Browse Lost & Found Items</h1>

    <p>Search through community reports.</p>


    <div class="search-box">

        <input
            type="text"
            id="searchInput"
            placeholder="Search items..."
        >


        <select id="typeFilter">

            <option value="All">All</option>

            <option value="Lost">Lost</option>

            <option value="Found">Found</option>

        </select>


        <select id="categoryFilter">

            <option value="All">All Categories</option>

            <option value="Mobile">Mobile</option>

            <option value="Laptop">Laptop</option>

            <option value="Bag">Bag</option>

            <option value="Wallet">Wallet</option>

            <option value="Keys">Keys</option>

            <option value="Documents">Documents</option>

            <option value="Jewelry">Jewelry</option>

            <option value="Clothing">Clothing</option>

        </select>

    </div>


    <div class="items-grid" id="itemsGrid">


        <?php foreach ($items as $item): ?>

            <div class="item-card"
                 data-title="<?php echo strtolower($item["title"]); ?>"
                 data-type="<?php echo $item["type"]; ?>"
                 data-category="<?php echo $item["category"]; ?>">


                <div class="item-image">

                    <?php if (!empty($item["image"])): ?>

                        <img
                            src="<?php echo htmlspecialchars($item["image"]); ?>"
                            alt="Item"
                        >

                    <?php else: ?>

                        <div class="no-image">
                            📦
                        </div>

                    <?php endif; ?>

                </div>


                <div class="item-content">

                    <span class="<?php echo $item["type"] === "Lost" ? "lost" : "found"; ?>">

                        <?php echo $item["type"]; ?>

                    </span>


                    <h3>
                        <?php echo htmlspecialchars($item["title"]); ?>
                    </h3>


                    <p>
                        📍 <?php echo htmlspecialchars($item["location"]); ?>
                    </p>


                    <p>
                        📅 <?php echo htmlspecialchars($item["date"]); ?>
                    </p>


                    <a
                        href="details.php?id=<?php echo $item["id"]; ?>"
                        class="details-btn"
                    >
                        View Details →
                    </a>

                </div>

            </div>

        <?php endforeach; ?>


    </div>


    <?php if (empty($items)): ?>

        <div class="empty">

            <h2>No reports yet</h2>

            <p>Be the first person to report an item.</p>

            <a href="report.php" class="btn primary">
                Report Item
            </a>

        </div>

    <?php endif; ?>


</section>


<footer>

    <p>© 2026 Lost & Found</p>

</footer>


<script src="js/script.js"></script>

</body>

</html>