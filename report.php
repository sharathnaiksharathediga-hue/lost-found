<?php
require_once "config/functions.php";

$user = getCurrentUser();
$error = "";
$defaultType = isset($_GET["type"]) && in_array($_GET["type"], ["Lost", "Found"]) ? $_GET["type"] : "Lost";

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

    if (empty($title) || empty($category) || empty($description) || empty($location) || empty($date) || empty($contact)) {
        $error = "Please fill in all required fields.";
    } else {
        $imagePath = "";

        // Handle Image Upload
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload("image", "uploads/items/");
            if ($uploadResult["success"]) {
                $imagePath = $uploadResult["path"];
            } else {
                $error = $uploadResult["error"] ?? "Failed to upload image.";
            }
        }

        if (empty($error)) {
            $newItem = [
                "user_id" => $user ? $user["id"] : null,
                "title" => $title,
                "type" => $type,
                "category" => $category,
                "description" => $description,
                "location" => $location,
                "date" => $date,
                "contact" => $contact,
                "status" => "Active",
                "image" => $imagePath
            ];

            $savedId = saveItem($newItem);
            header("Location: details.php?id=" . urlencode($savedId) . "&created=1");
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
    <title>Report Item - Lost & Found</title>
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
        <a href="report.php" class="active">Report Item</a>
        <?php if ($user): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="my-reports.php">My Reports</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav-register">Register</a>
        <?php endif; ?>
    </nav>
</header>

<div class="form-container">
    <div class="form-card">
        <h1>Report an Item</h1>
        <p>Fill in the details to publish a lost or found report to the community.</p>

        <?php if ($error): ?>
            <div class="error-message">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="report.php" enctype="multipart/form-data">
            <div class="form-row">
                <div>
                    <label for="type">Report Type *</label>
                    <select name="type" id="type" required>
                        <option value="Lost" <?= (isset($_POST['type']) ? $_POST['type'] : $defaultType) === "Lost" ? "selected" : "" ?>>Lost Item</option>
                        <option value="Found" <?= (isset($_POST['type']) ? $_POST['type'] : $defaultType) === "Found" ? "selected" : "" ?>>Found Item</option>
                    </select>
                </div>
                <div>
                    <label for="category">Category *</label>
                    <select name="category" id="category" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= (isset($_POST['category']) && $_POST['category'] === $cat) ? "selected" : "" ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label for="title">Item Name / Title *</label>
            <input type="text" id="title" name="title" placeholder="e.g. Black Leather Wallet with Student ID" value="<?= isset($_POST['title']) ? e($_POST['title']) : '' ?>" required>

            <label for="description">Detailed Description *</label>
            <textarea id="description" name="description" placeholder="Provide distinct characteristics, brand, color, unique marks, etc." required><?= isset($_POST['description']) ? e($_POST['description']) : '' ?></textarea>

            <div class="form-row">
                <div>
                    <label for="location">Location Lost/Found *</label>
                    <input type="text" id="location" name="location" placeholder="e.g. Central Library, 2nd Floor" value="<?= isset($_POST['location']) ? e($_POST['location']) : '' ?>" required>
                </div>
                <div>
                    <label for="date">Date *</label>
                    <input type="date" id="date" name="date" value="<?= isset($_POST['date']) ? e($_POST['date']) : date('Y-m-d') ?>" required>
                </div>
            </div>

            <label for="contact">Contact Information *</label>
            <input type="text" id="contact" name="contact" placeholder="Phone number, Email, or WhatsApp" value="<?= isset($_POST['contact']) ? e($_POST['contact']) : ($user ? e($user['email']) : '') ?>" required>

            <label for="image">Item Photo (Optional)</label>
            <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp, image/gif">
            <div class="image-preview-wrapper" style="display:none;"></div>

            <button type="submit" class="auth-button">Publish Report</button>
        </form>
    </div>
</div>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>