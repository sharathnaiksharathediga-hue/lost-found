<?php
require_once "config/functions.php";

requireLogin();

$user = getCurrentUser();
$id = $_GET["id"] ?? "";

if (empty($id)) {
    header("Location: my-reports.php");
    exit;
}

$item = getItemById($id);

if (!$item) {
    die("Item not found.");
}

// Ownership verification
if (empty($item["user_id"]) || (string)$item["user_id"] !== (string)$user["id"]) {
    die("You do not have permission to delete this report.");
}

deleteItem($id);

header("Location: my-reports.php?deleted=1");
exit;