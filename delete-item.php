<?php

session_start();

require_once "config/functions.php";


// User must be logged in
requireLogin();


// JSON file
$itemFile = "data/items.json";


// Check item ID
if (!isset($_GET["id"]) || $_GET["id"] === "") {

    header("Location: my-reports.php");

    exit;
}


$itemID = $_GET["id"];


// Read items
$items = readJSON($itemFile);


// Find item
$itemIndex = -1;
$item = null;


foreach ($items as $index => $currentItem) {

    if (
        isset($currentItem["id"]) &&
        $currentItem["id"] === $itemID
    ) {

        $itemIndex = $index;

        $item = $currentItem;

        break;
    }
}


// Item not found
if ($itemIndex === -1) {

    die("Item not found.");

}


// Security check
// User can delete only their own report

if (
    !isset($item["user_id"]) ||
    $item["user_id"] !== $_SESSION["user_id"]
) {

    die("You are not allowed to delete this item.");

}


// Delete image if it exists

if (
    isset($item["image"]) &&
    !empty($item["image"]) &&
    file_exists($item["image"])
) {

    unlink($item["image"]);

}


// Remove item from array

array_splice(
    $items,
    $itemIndex,
    1
);


// Save updated JSON

writeJSON(
    $itemFile,
    $items
);


// Redirect back to My Reports

header(
    "Location: my-reports.php"
);

exit;

?>