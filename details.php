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


$id = isset($_GET["id"]) ? $_GET["id"] : 0;

$selectedItem = null;


foreach ($items as $item) {

    if ($item["id"] == $id) {

        $selectedItem = $item;

        break;
    }
}


if (!$selectedItem) {

    die("Item not found.");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($selectedItem["title"]); ?>
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<header class="header">

    <div class="logo">
        🔎 Lost<span>&</span>Found
    </div>

    <nav>

        <a href="index.php">Home</a>

        <a href="items.php">Browse Items</a>

        <a href="report.php">Report Item</a>

    </nav>

</header>


<section class="details-container">


    <div class="details-card">


        <div class="details-image">

            <?php if (!empty($selectedItem["image"])): ?>

                <img
                    src="<?php echo htmlspecialchars($selectedItem["image"]); ?>"
                    alt="Item"
                >

            <?php else: ?>

                <div class="large-placeholder">
                    📦
                </div>

            <?php endif; ?>

        </div>


        <div class="details-info">


            <span class="<?php echo $selectedItem["type"] === "Lost" ? "lost" : "found"; ?>">

                <?php echo $selectedItem["type"]; ?>

            </span>


            <h1>
                <?php echo htmlspecialchars($selectedItem["title"]); ?>
            </h1>


            <p class="category">
                Category:
                <strong>
                    <?php echo htmlspecialchars($selectedItem["category"]); ?>
                </strong>
            </p>


            <p>
                📍
                <?php echo htmlspecialchars($selectedItem["location"]); ?>
            </p>


            <p>
                📅
                <?php echo htmlspecialchars($selectedItem["date"]); ?>
            </p>


            <h3>Description</h3>

            <p>
                <?php echo nl2br(htmlspecialchars($selectedItem["description"])); ?>
            </p>


            <div class="contact-box">

                <h3>Contact</h3>

                <p>
                    <?php echo htmlspecialchars($selectedItem["contact"]); ?>
                </p>

            </div>


            <a href="items.php" class="btn secondary">
                ← Back to Items
            </a>


        </div>

    </div>

</section>


<footer>

    <p>© 2026 Lost & Found</p>

</footer>


</body>

</html>