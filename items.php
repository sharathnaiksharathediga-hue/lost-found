<?php
require_once "config/functions.php";

$user = getCurrentUser();
$items = getAllItems();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Lost & Found Items</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
        <span>🔎</span> Lost<span>&</span>Found
    </a>
    <nav>
        <a href="index.php">Home</a>
        <a href="items.php" class="active">Browse Items</a>
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

<section class="browse">
    <div class="browse-header">
        <div>
            <h1>Browse Community Reports</h1>
            <p>Search and filter all lost and found listings across categories and locations.</p>
        </div>
        <a href="report.php" class="btn primary sm">+ Report an Item</a>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search by keyword, location, or name...">

        <select id="typeFilter">
            <option value="All">All Types (Lost & Found)</option>
            <option value="Lost">Lost Only</option>
            <option value="Found">Found Only</option>
        </select>

        <select id="categoryFilter">
            <option value="All">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="statusFilter">
            <option value="All">All Statuses</option>
            <option value="Active">Active Only</option>
            <option value="Resolved">Resolved Only</option>
        </select>
    </div>

    <!-- RESULTS COUNT -->
    <div style="margin-bottom: 20px; font-size: 14px; color: var(--text-muted); font-weight: 600;">
        Showing <span id="resultsCount"><?= count($items) ?></span> report(s)
    </div>

    <!-- ITEMS GRID -->
    <div class="items-grid" id="itemsGrid">
        <?php foreach ($items as $item): ?>
            <div class="item-card"
                 data-title="<?= strtolower(e($item["title"])) ?>"
                 data-type="<?= e($item["type"] ?? "Lost") ?>"
                 data-category="<?= e($item["category"] ?? "Other") ?>"
                 data-location="<?= strtolower(e($item["location"])) ?>"
                 data-description="<?= strtolower(e($item["description"])) ?>"
                 data-status="<?= e($item["status"] ?? "Active") ?>">

                <div class="item-image">
                    <?php if (!empty($item["image"]) && file_exists($item["image"])): ?>
                        <img src="<?= e($item["image"]) ?>" alt="<?= e($item["title"]) ?>">
                    <?php else: ?>
                        <div class="no-image">📦</div>
                    <?php endif; ?>
                </div>

                <div class="item-content">
                    <div class="card-top">
                        <span class="badge <?= ($item["type"] ?? "Lost") === "Lost" ? "lost" : "found" ?>">
                            <?= e($item["type"] ?? "Lost") ?>
                        </span>
                        <span class="badge-category"><?= e($item["category"] ?? "Other") ?></span>
                    </div>

                    <h3><?= e($item["title"]) ?></h3>

                    <div class="item-meta">
                        <span>📍 <?= e($item["location"]) ?></span>
                        <span>📅 <?= e($item["date"]) ?></span>
                    </div>

                    <div class="item-card-footer">
                        <span class="status <?= strtolower($item["status"] ?? "Active") ?>">
                            <?= e($item["status"] ?? "Active") ?>
                        </span>
                        <a href="details.php?id=<?= urlencode($item["id"]) ?>" class="details-btn">
                            View Details →
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- EMPTY FILTER STATE -->
    <div id="noResultsState" class="empty" style="<?= empty($items) ? 'display:block;' : 'display:none;' ?>">
        <div class="empty-icon">🔍</div>
        <h2>No matching reports found</h2>
        <p>Try adjusting your search terms or category filters.</p>
        <a href="report.php" class="btn primary sm">Report a New Item</a>
    </div>

</section>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>