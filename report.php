<?php

$dataFile = "data/items.json";

$type = isset($_GET["type"]) ? $_GET["type"] : "Lost";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $items = [];

    if (file_exists($dataFile)) {

        $json = file_get_contents($dataFile);
        $items = json_decode($json, true);

        if (!is_array($items)) {
            $items = [];
        }
    }

    $newItem = [

        "id" => time(),

        "title" => $_POST["title"],

        "category" => $_POST["category"],

        "description" => $_POST["description"],

        "location" => $_POST["location"],

        "date" => $_POST["date"],

        "type" => $_POST["type"],

        "contact" => $_POST["contact"],

        "image" => ""

    ];


    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {

        $uploadDir = "uploads/";

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);

        $target = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {

            $newItem["image"] = $target;

        }
    }


    $items[] = $newItem;

    file_put_contents(
        $dataFile,
        json_encode($items, JSON_PRETTY_PRINT)
    );


    header("Location: items.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Report Item - Lost & Found</title>

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


<div class="form-container">

    <div class="form-card">

        <h1>Report an Item</h1>

        <p>
            Tell us about the item you lost or found.
        </p>


        <form method="POST"
              enctype="multipart/form-data">


            <label>Item Type</label>

            <select name="type" required>

                <option value="Lost"
                    <?php echo $type === "Lost" ? "selected" : ""; ?>>
                    Lost Item
                </option>

                <option value="Found"
                    <?php echo $type === "Found" ? "selected" : ""; ?>>
                    Found Item
                </option>

            </select>


            <label>Item Name</label>

            <input
                type="text"
                name="title"
                placeholder="Example: Black Samsung Phone"
                required
            >


            <label>Category</label>

            <select name="category" required>

                <option value="">Select Category</option>

                <option>Mobile</option>
                <option>Laptop</option>
                <option>Bag</option>
                <option>Wallet</option>
                <option>Keys</option>
                <option>Documents</option>
                <option>Jewelry</option>
                <option>Clothing</option>
                <option>Other</option>

            </select>


            <label>Description</label>

            <textarea
                name="description"
                placeholder="Describe the item..."
                required
            ></textarea>


            <label>Location</label>

            <input
                type="text"
                name="location"
                placeholder="Where was it lost/found?"
                required
            >


            <label>Date</label>

            <input
                type="date"
                name="date"
                required
            >


            <label>Contact Information</label>

            <input
                type="text"
                name="contact"
                placeholder="Phone or Email"
                required
            >


            <label>Item Image</label>

            <input
                type="file"
                name="image"
                accept="image/*"
            >


            <button type="submit" class="btn primary full">
                Submit Report
            </button>

        </form>

    </div>

</div>


<footer>

    <p>© 2026 Lost & Found</p>

</footer>


</body>
</html>