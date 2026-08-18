<?php
require_once "config/functions.php";

$user = getCurrentUser();
$id = $_GET["id"] ?? "";

$item = getItemById($id);
$successMessage = "";

if (isset($_GET["created"])) {
    $successMessage = "Your report has been successfully published to the community!";
} elseif (isset($_GET["updated"])) {
    $successMessage = "Item details were successfully updated.";
}

// Handle Quick Status Toggle by Owner
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_status"]) && $item && $user) {
    if (isset($item["user_id"]) && $item["user_id"] === $user["id"]) {
        $newStatus = ($item["status"] ?? "Active") === "Active" ? "Resolved" : "Active";
        updateItem($item["id"], ["status" => $newStatus]);
        $item = getItemById($id); // Reload item
        $successMessage = "Item status updated to: " . $newStatus;
    }
}

$isOwner = $user && $item && isset($item["user_id"]) && $item["user_id"] === $user["id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $item ? e($item["title"]) . " - Lost & Found" : "Item Not Found" ?></title>
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

<div class="details-container">

    <?php if ($successMessage): ?>
        <div class="success-message">
            <?= e($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!$item): ?>
        <div class="empty">
            <div class="empty-icon">⚠️</div>
            <h2>Item Not Found</h2>
            <p>The report you are looking for does not exist or has been removed.</p>
            <a href="items.php" class="btn primary sm">← Browse All Items</a>
        </div>
    <?php else: ?>

        <div style="margin-bottom: 20px;">
            <a href="items.php" class="btn secondary sm">← Back to All Items</a>
        </div>

        <div class="details-card">
            <!-- IMAGE COLUMN -->
            <div class="details-image">
                <?php if (!empty($item["image"]) && file_exists($item["image"])): ?>
                    <img src="<?= e($item["image"]) ?>" alt="<?= e($item["title"]) ?>">
                <?php else: ?>
                    <div class="large-placeholder">📦</div>
                <?php endif; ?>
            </div>

            <!-- CONTENT COLUMN -->
            <div class="details-info">
                <div class="details-header-badges">
                    <span class="badge <?= ($item["type"] ?? "Lost") === "Lost" ? "lost" : "found" ?>">
                        <?= e($item["type"] ?? "Lost") ?>
                    </span>
                    <span class="status <?= strtolower($item["status"] ?? "Active") ?>">
                        <?= e($item["status"] ?? "Active") ?>
                    </span>
                    <span class="badge-category">
                        📁 <?= e($item["category"] ?? "Other") ?>
                    </span>
                </div>

                <h1><?= e($item["title"]) ?></h1>

                <div class="details-meta-grid">
                    <div class="details-meta-item">
                        <span>📍 Location</span>
                        <strong><?= e($item["location"]) ?></strong>
                    </div>
                    <div class="details-meta-item">
                        <span>📅 Date Reported</span>
                        <strong><?= e($item["date"]) ?></strong>
                    </div>
                </div>

                <h3>Description</h3>
                <div class="description-box">
                    <?= nl2br(e($item["description"])) ?>
                </div>

                <div class="contact-box">
                    <h3>📞 Contact Person</h3>
                    <p><?= e($item["contact"]) ?></p>
                </div>

                <!-- OWNER MANAGEMENT OR BACK ACTION -->
                <div class="details-actions">
                    <?php if ($isOwner): ?>
                        <a href="edit-item.php?id=<?= urlencode($item["id"]) ?>" class="btn secondary sm">
                            ✏️ Edit Report
                        </a>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="toggle_status" value="1">
                            <button type="submit" class="btn secondary sm">
                                <?= ($item["status"] ?? "Active") === "Active" ? "✅ Mark as Resolved" : "🔄 Reopen Case" ?>
                            </button>
                        </form>

                        <a href="delete-item.php?id=<?= urlencode($item["id"]) ?>"
                           class="btn danger sm"
                           onclick="return confirm('Are you sure you want to permanently delete this report?');">
                            🗑 Delete
                        </a>
                    <?php else: ?>
                        <a href="items.php" class="btn secondary sm">
                            ← Return to Browse
                        </a>
                        <a href="report.php?type=<?= ($item['type'] ?? 'Lost') === 'Lost' ? 'Found' : 'Lost' ?>" class="btn primary sm">
                            Found Similar Item?
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    <?php endif; ?>

</div>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>