<?php
require_once "config/functions.php";

$stats = getSystemStats();
$recentItems = getAllItems(["limit" => 6]);
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found - Community Recovery Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
        <span>🔎</span> Lost<span>&</span>Found
    </a>
    <nav>
        <a href="index.php" class="active">Home</a>
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

<section class="hero">
    <div class="hero-content">
        <span class="small-title">Community Lost & Found Hub</span>
        <h1>Lost something? <span>Let's find it together.</span></h1>
        <p>Report lost belongings, list items you found, and help connect people back to what matters most.</p>
        <div class="hero-buttons">
            <a href="report.php?type=Lost" class="btn primary">📢 Report Lost Item</a>
            <a href="report.php?type=Found" class="btn secondary">🎁 Report Found Item</a>
            <a href="items.php" class="btn secondary">🔍 Browse All Items</a>
        </div>
    </div>
</section>

<section class="stats">
    <div class="stat-card lost">
        <div class="stat-icon">❗</div>
        <h2><?= e($stats["lost"]) ?></h2>
        <p>Lost Items Reported</p>
    </div>
    <div class="stat-card found">
        <div class="stat-icon">🎁</div>
        <h2><?= e($stats["found"]) ?></h2>
        <p>Found Items Reported</p>
    </div>
    <div class="stat-card total">
        <div class="stat-icon">📊</div>
        <h2><?= e($stats["total"]) ?></h2>
        <p>Total Community Reports</p>
    </div>
    <div class="stat-card resolved">
        <div class="stat-icon">✅</div>
        <h2><?= e($stats["resolved"]) ?></h2>
        <p>Items Reunited</p>
    </div>
</section>

<!-- RECENT ACTIVITY SECTION -->
<section class="recent-section">
    <div class="browse-header">
        <div>
            <h2>Recent Reports</h2>
            <p class="sub">Latest lost and found listings in your community</p>
        </div>
        <a href="items.php" class="btn secondary sm">View All Items →</a>
    </div>

    <?php if (empty($recentItems)): ?>
        <div class="empty">
            <div class="empty-icon">📦</div>
            <h2>No reports yet</h2>
            <p>Be the first member to report an item.</p>
            <a href="report.php" class="btn primary">Create Report</a>
        </div>
    <?php else: ?>
        <div class="items-grid">
            <?php foreach ($recentItems as $item): ?>
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
    <?php endif; ?>
</section>

<section class="how">
    <h2>How It Works</h2>
    <p class="section-description">Helping people recover their belongings in three simple steps.</p>
    <div class="steps">
        <div class="step">
            <div class="step-icon">📝</div>
            <h3>1. Report Item</h3>
            <p>Post a quick description and photo of the item you lost or found with contact info.</p>
        </div>
        <div class="step">
            <div class="step-icon">🔎</div>
            <h3>2. Search & Match</h3>
            <p>Community members search through categories, locations, and real-time listings.</p>
        </div>
        <div class="step">
            <div class="step-icon">🤝</div>
            <h3>3. Reconnect</h3>
            <p>Connect with the finder or owner directly to return the item safely.</p>
        </div>
    </div>
</section>

<footer>
    <p>© <?= date("Y") ?> Lost & Found Community Platform. Built with PHP, HTML, CSS & JavaScript.</p>
</footer>

</body>
</html>