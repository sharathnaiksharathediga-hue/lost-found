<?php

session_start();

require_once "config/functions.php";

requireLogin();


// JSON file
$itemFile = "data/items.json";


// Get item ID from URL
if (!isset($_GET["id"]) || $_GET["id"] === "") {

    header("Location: my-reports.php");

    exit;
}

$itemID = $_GET["id"];


// Read items
$items = readJSON($itemFile);


// Find item
$item = null;
$itemIndex = -1;


foreach ($items as $index => $currentItem) {

    if (
        isset($currentItem["id"]) &&
        $currentItem["id"] === $itemID
    ) {

        $item = $currentItem;

        $itemIndex = $index;

        break;
    }
}


// Item not found
if ($item === null) {

    die("Item not found.");
}


// Check ownership
if (
    $item["user_id"] !== $_SESSION["user_id"]
) {

    die("You are not allowed to edit this item.");
}


$error = "";
$success = "";


// Form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);

    $type = $_POST["type"];

    $category = $_POST["category"];

    $description = trim(
        $_POST["description"]
    );

    $location = trim(
        $_POST["location"]
    );

    $date = $_POST["date"];

    $contact = trim(
        $_POST["contact"]
    );

    $status = $_POST["status"];


    // Validation

    if (
        $title === "" ||
        $category === "" ||
        $description === "" ||
        $location === "" ||
        $date === "" ||
        $contact === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } else {

        /*
        Update item
        */

        $items[$itemIndex]["title"] =
            $title;

        $items[$itemIndex]["type"] =
            $type;

        $items[$itemIndex]["category"] =
            $category;

        $items[$itemIndex]["description"] =
            $description;

        $items[$itemIndex]["location"] =
            $location;

        $items[$itemIndex]["date"] =
            $date;

        $items[$itemIndex]["contact"] =
            $contact;

        $items[$itemIndex]["status"] =
            $status;


        /*
        Handle new image
        */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === 0
        ) {

            $uploadDir =
                "uploads/items/";


            // Make sure folder exists

            if (!is_dir($uploadDir)) {

                mkdir(
                    $uploadDir,
                    0777,
                    true
                );
            }


            $extension = strtolower(
                pathinfo(
                    $_FILES["image"]["name"],
                    PATHINFO_EXTENSION
                )
            );


            $allowedExtensions = [
                "jpg",
                "jpeg",
                "png",
                "gif",
                "webp"
            ];


            if (
                in_array(
                    $extension,
                    $allowedExtensions
                )
            ) {

                $fileName =
                    generateID() .
                    "." .
                    $extension;


                $target =
                    $uploadDir .
                    $fileName;


                if (
                    move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $target
                    )
                ) {

                    /*
                    Delete old image
                    */

                    if (
                        !empty(
                            $items[$itemIndex]["image"]
                        ) &&
                        file_exists(
                            $items[$itemIndex]["image"]
                        )
                    ) {

                        unlink(
                            $items[$itemIndex]["image"]
                        );
                    }


                    /*
                    Save new image
                    */

                    $items[$itemIndex]["image"] =
                        $target;
                }
            }
        }


        /*
        Save JSON
        */

        writeJSON(
            $itemFile,
            $items
        );


        /*
        Redirect
        */

        header(
            "Location: details.php?id=" .
            urlencode($itemID)
        );

        exit;
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
        Edit Item - Lost & Found
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


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

        <a href="my-reports.php">
            My Reports
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<div class="form-container">

    <div class="form-card">

        <h1>
            Edit Item
        </h1>


        <p>
            Update your lost or found item.
        </p>


        <?php if ($error): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars(
                    $error
                );
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- ITEM TYPE -->

            <label>
                Item Type
            </label>


            <select name="type" required>

                <option
                    value="Lost"
                    <?php
                    echo
                    $item["type"] === "Lost"
                        ? "selected"
                        : "";
                    ?>
                >
                    Lost Item
                </option>


                <option
                    value="Found"
                    <?php
                    echo
                    $item["type"] === "Found"
                        ? "selected"
                        : "";
                    ?>
                >
                    Found Item
                </option>

            </select>


            <!-- TITLE -->

            <label>
                Item Name
            </label>


            <input
                type="text"
                name="title"
                value="<?php
                    echo htmlspecialchars(
                        $item["title"]
                    );
                ?>"
                required
            >


            <!-- CATEGORY -->

            <label>
                Category
            </label>


            <select
                name="category"
                required
            >

                <option value="">
                    Select Category
                </option>


                <?php

                $categories = [
                    "Mobile",
                    "Laptop",
                    "Bag",
                    "Wallet",
                    "Keys",
                    "Documents",
                    "Jewelry",
                    "Clothing",
                    "Books",
                    "Other"
                ];


                foreach (
                    $categories
                    as $category
                ):

                ?>

                    <option
                        value="<?php
                            echo htmlspecialchars(
                                $category
                            );
                        ?>"
                        <?php
                        echo
                        $item["category"]
                        === $category
                            ? "selected"
                            : "";
                        ?>
                    >

                        <?php
                        echo htmlspecialchars(
                            $category
                        );
                        ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <!-- DESCRIPTION -->

            <label>
                Description
            </label>


            <textarea
                name="description"
                required
            ><?php
                echo htmlspecialchars(
                    $item["description"]
                );
            ?></textarea>


            <!-- LOCATION -->

            <label>
                Location
            </label>


            <input
                type="text"
                name="location"
                value="<?php
                    echo htmlspecialchars(
                        $item["location"]
                    );
                ?>"
                required
            >


            <!-- DATE -->

            <label>
                Date
            </label>


            <input
                type="date"
                name="date"
                value="<?php
                    echo htmlspecialchars(
                        $item["date"]
                    );
                ?>"
                required
            >


            <!-- CONTACT -->

            <label>
                Contact
            </label>


            <input
                type="text"
                name="contact"
                value="<?php
                    echo htmlspecialchars(
                        $item["contact"]
                    );
                ?>"
                required
            >


            <!-- STATUS -->

            <label>
                Status
            </label>


            <select
                name="status"
                required
            >

                <option
                    value="Active"
                    <?php
                    echo
                    $item["status"] === "Active"
                        ? "selected"
                        : "";
                    ?>
                >
                    Active
                </option>


                <option
                    value="Resolved"
                    <?php
                    echo
                    $item["status"] === "Resolved"
                        ? "selected"
                        : "";
                    ?>
                >
                    Resolved
                </option>

            </select>


            <!-- CURRENT IMAGE -->

            <?php

            if (
                !empty($item["image"]) &&
                file_exists($item["image"])
            ):

            ?>

                <label>
                    Current Image
                </label>


                <img
                    src="<?php
                        echo htmlspecialchars(
                            $item["image"]
                        );
                    ?>"
                    alt="Item Image"
                    style="
                        width:180px;
                        border-radius:10px;
                        margin-bottom:15px;
                    "
                >

            <?php endif; ?>


            <!-- NEW IMAGE -->

            <label>
                Change Image
            </label>


            <input
                type="file"
                name="image"
                accept="image/*"
            >


            <!-- BUTTON -->

            <button
                type="submit"
                class="auth-button"
            >
                Save Changes
            </button>


        </form>


        <a
            href="my-reports.php"
            class="back-home"
        >
            ← Back to My Reports
        </a>

    </div>

</div>


</body>

</html>