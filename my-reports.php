<?php
require_once "config/functions.php";

requireLogin();

$user = getCurrentUser();
$myReports = getAllItems(["user_id" => $user["id"]]);

$info = "";
if (isset($_GET["deleted"])) {
    $info = "The report was successfully deleted.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - Lost & Found</title>
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
        <a href="my-reports.php" class="active">My Reports (<?= count($myReports) ?>)</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="reports-container">

    <div class="reports-header">
        <div>
            <span class="small-title">Account Activity</span>
            <h1>My Reports</h1>
            <p>Manage all the lost and found reports you've posted to the community.</p>
        </div>
        <a href="report.php" class="btn primary sm">+ Create New Report</a>
    </div>

    <?php if ($info): ?>
        <div class="success-message">
            <?= e($info) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($myReports)): ?>
        <div class="empty-reports">
            <div class="empty-icon">📋</div>
            <h2>No reports found</h2>
            <p>You haven't reported any lost or found items yet.</p>
            <a href="report.php" class="btn primary sm">+ Post Your First Report</a>
        </div>
    <?php else: ?>

        <div style="margin-bottom: 20px; font-weight: 600; color: var(--text-muted); font-size: 14px;">
            Showing <?= count($myReports) ?> report(s)
        </div>

        <div class="reports-grid">
            <?php foreach ($myReports as $item): ?>
                <div class="report-card">
                    <div class="report-image">
                        <?php if (!empty($item["image"]) && file_exists($item["image"])): ?>
                            <img src="<?= e($item["image"]) ?>" alt="<?= e($item["title"]) ?>">
                        <?php else: ?>
                            <div class="no-image">📦</div>
                        <?php endif; ?>
                    </div>

                    <div class="report-content">
                        <div class="card-top">
                            <span class="badge <?= ($item["type"] ?? "Lost") === "Lost" ? "lost" : "found" ?>">
                                <?= e($item["type"] ?? "Lost") ?>
                            </span>
                            <span class="status <?= strtolower($item["status"] ?? "Active") ?>">
                                <?= e($item["status"] ?? "Active") ?>
                            </span>
                        </div>

                        <h2><?= e($item["title"]) ?></h2>

                        <div class="item-meta">
                            <span>📁 <?= e($item["category"] ?? "Other") ?></span>
                            <span>📍 <?= e($item["location"]) ?></span>
                            <span>📅 <?= e($item["date"]) ?></span>
                        </div>

                        <div class="report-actions">
                            <a href="details.php?id=<?= urlencode($item["id"]) ?>" class="view-btn">
                                👁 View
                            </a>
                            <a href="edit-item.php?id=<?= urlencode($item["id"]) ?>" class="edit-btn">
                                ✏️ Edit
                            </a>
                            <a href="delete-item.php?id=<?= urlencode($item["id"]) ?>"
                               class="delete-btn"
                               onclick="return confirm('Are you sure you want to delete this report?');">
                                🗑 Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>