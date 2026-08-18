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
    die("You do not have permission to edit this report.");
}

$error = "";
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
    "Electronics",
    "Pets",
    "Other"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $type = in_array($_POST["type"] ?? "", ["Lost", "Found"]) ? $_POST["type"] : "Lost";
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $date = trim($_POST["date"] ?? "");
    $contact = trim($_POST["contact"] ?? "");
    $status = in_array($_POST["status"] ?? "", ["Active", "Resolved"]) ? $_POST["status"] : "Active";

    if (empty($title) || empty($category) || empty($description) || empty($location) || empty($date) || empty($contact)) {
        $error = "Please fill in all required fields.";
    } else {
        $imagePath = $item["image"] ?? "";

        // Check if new image uploaded
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload("image", "uploads/items/");
            if ($uploadResult["success"]) {
                // Delete old image if exists
                if (!empty($item["image"])) {
                    $oldPath = dirname(__DIR__) . "/" . ltrim($item["image"], "/\\");
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $imagePath = $uploadResult["path"];
            } else {
                $error = $uploadResult["error"] ?? "Failed to upload new image.";
            }
        }

        if (empty($error)) {
            $updateData = [
                "title" => $title,
                "type" => $type,
                "category" => $category,
                "description" => $description,
                "location" => $location,
                "date" => $date,
                "contact" => $contact,
                "status" => $status,
                "image" => $imagePath
            ];

            updateItem($id, $updateData);
            header("Location: details.php?id=" . urlencode($id) . "&updated=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
        <span>🔎</span> Lost<span>&</span>Found
    </a>
    <nav>
        <a href="index.php">Home</a>
        <a href="items.php">Browse Items</a>
        <a href="report.php">Report Item</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="my-reports.php">My Reports</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="form-container">
    <div class="form-card">
        <h1>Edit Report</h1>
        <p>Update the information for this lost or found listing.</p>

        <?php if ($error): ?>
            <div class="error-message">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div>
                    <label for="type">Report Type *</label>
                    <select name="type" id="type" required>
                        <option value="Lost" <?= ($item["type"] ?? "Lost") === "Lost" ? "selected" : "" ?>>Lost Item</option>
                        <option value="Found" <?= ($item["type"] ?? "Lost") === "Found" ? "selected" : "" ?>>Found Item</option>
                    </select>
                </div>
                <div>
                    <label for="status">Case Status *</label>
                    <select name="status" id="status" required>
                        <option value="Active" <?= ($item["status"] ?? "Active") === "Active" ? "selected" : "" ?>>Active (Open)</option>
                        <option value="Resolved" <?= ($item["status"] ?? "Active") === "Resolved" ? "selected" : "" ?>>Resolved (Recovered)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="title">Item Title *</label>
                    <input type="text" id="title" name="title" value="<?= e($item["title"]) ?>" required>
                </div>
                <div>
                    <label for="category">Category *</label>
                    <select name="category" id="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($item["category"] ?? "") === $cat ? "selected" : "" ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label for="description">Description *</label>
            <textarea id="description" name="description" required><?= e($item["description"]) ?></textarea>

            <div class="form-row">
                <div>
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" value="<?= e($item["location"]) ?>" required>
                </div>
                <div>
                    <label for="date">Date *</label>
                    <input type="date" id="date" name="date" value="<?= e($item["date"]) ?>" required>
                </div>
            </div>

            <label for="contact">Contact Information *</label>
            <input type="text" id="contact" name="contact" value="<?= e($item["contact"]) ?>" required>

            <?php if (!empty($item["image"]) && file_exists($item["image"])): ?>
                <label>Current Photo</label>
                <div style="margin-bottom: 12px;">
                    <img src="<?= e($item["image"]) ?>" alt="Item Image" style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                </div>
            <?php endif; ?>

            <label for="image">Replace Photo (Optional)</label>
            <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp, image/gif">
            <div class="image-preview-wrapper" style="display:none;"></div>

            <button type="submit" class="auth-button">Save Changes</button>
        </form>

        <a href="my-reports.php" class="back-home">← Cancel and return to My Reports</a>
    </div>
</div>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>