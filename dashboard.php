<?php
require_once "config/functions.php";

requireLogin();

$user = getCurrentUser();
$userReports = getAllItems(["user_id" => $user["id"]]);

$myTotal = count($userReports);
$myActive = 0;
$myResolved = 0;

foreach ($userReports as $item) {
    if (($item["status"] ?? "Active") === "Resolved") {
        $myResolved++;
    } else {
        $myActive++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost & Found</title>
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="my-reports.php">My Reports (<?= $myTotal ?>)</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<section class="dashboard">
    <div class="welcome">
        <div>
            <p>Welcome back 👋</p>
            <h1><?= e($user["name"]) ?></h1>
            <p><?= e($user["email"]) ?></p>
        </div>
        <div>
            <a href="report.php" class="btn secondary sm">+ Create New Report</a>
        </div>
    </div>

    <!-- USER STATS -->
    <div class="dashboard-stats">
        <div class="stat-card total">
            <div class="stat-icon">📋</div>
            <h2><?= $myTotal ?></h2>
            <p>Total Reports Submitted</p>
        </div>
        <div class="stat-card lost">
            <div class="stat-icon">⏳</div>
            <h2><?= $myActive ?></h2>
            <p>Active Open Cases</p>
        </div>
        <div class="stat-card found">
            <div class="stat-icon">🎉</div>
            <h2><?= $myResolved ?></h2>
            <p>Resolved / Recovered</p>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 18px;">Quick Actions</h2>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-icon">📢</div>
            <h2>Report Lost Item</h2>
            <p>Tell the community about an item you've misplaced to get help locating it.</p>
            <a href="report.php?type=Lost" class="btn primary sm">Report Lost</a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">🎁</div>
            <h2>Report Found Item</h2>
            <p>List something you found to help return it safely to its rightful owner.</p>
            <a href="report.php?type=Found" class="btn primary sm">Report Found</a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">📋</div>
            <h2>My Reports</h2>
            <p>Manage, edit, mark resolved, or remove your existing reported items.</p>
            <a href="my-reports.php" class="btn secondary sm">Manage Reports</a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">🔍</div>
            <h2>Browse All Items</h2>
            <p>Search through the full database of reported lost and found items.</p>
            <a href="items.php" class="btn secondary sm">Browse Database</a>
        </div>
    </div>

    <!-- RECENT USER ACTIVITY -->
    <div class="browse-header" style="margin-top: 40px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 800;">My Recent Reports</h2>
            <p class="sub">Items you have recently posted to the platform</p>
        </div>
        <?php if (!empty($userReports)): ?>
            <a href="my-reports.php" class="btn secondary sm">View All (<?= $myTotal ?>) →</a>
        <?php endif; ?>
    </div>

    <?php if (empty($userReports)): ?>
        <div class="empty">
            <div class="empty-icon">📝</div>
            <h2>No reports yet</h2>
            <p>You haven't posted any lost or found reports yet.</p>
            <a href="report.php" class="btn primary sm">Post a Report</a>
        </div>
    <?php else: ?>
        <div class="items-grid">
            <?php foreach (array_slice($userReports, 0, 3) as $item): ?>
                <div class="item-card">
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
                            <span class="status <?= strtolower($item["status"] ?? "Active") ?>">
                                <?= e($item["status"] ?? "Active") ?>
                            </span>
                        </div>
                        <h3><?= e($item["title"]) ?></h3>
                        <div class="item-meta">
                            <span>📁 <?= e($item["category"] ?? "Other") ?></span>
                            <span>📍 <?= e($item["location"]) ?></span>
                            <span>📅 <?= e($item["date"]) ?></span>
                        </div>
                        <div class="item-card-footer">
                            <a href="edit-item.php?id=<?= urlencode($item["id"]) ?>" class="edit-btn">✏️ Edit</a>
                            <a href="details.php?id=<?= urlencode($item["id"]) ?>" class="details-btn">View →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform</p>
</footer>

</body>
</html>