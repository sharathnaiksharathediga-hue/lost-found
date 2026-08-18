<?php
require_once "config/functions.php";

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$info = "";
$redirect = $_GET["redirect"] ?? "dashboard.php";

if (isset($_GET["logged_out"])) {
    $info = "You have been logged out successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        $result = authenticateUser($email, $password);
        if ($result["success"]) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $result["user"]["id"];
            $_SESSION["user_name"] = $result["user"]["name"];
            $_SESSION["user_email"] = $result["user"]["email"];

            $target = !empty($_POST["redirect"]) ? $_POST["redirect"] : "dashboard.php";
            header("Location: " . $target);
            exit;
        } else {
            $error = $result["message"];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <div class="auth-logo">
        <a href="index.php" style="color:inherit; text-decoration:none;">
            🔎 Lost<span>&</span>Found
        </a>
    </div>

    <h1>Welcome Back</h1>
    <p class="auth-subtitle">Login to manage your reports and updates</p>

    <?php if ($info): ?>
        <div class="success-message">
            <?= e($info) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-message">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="john@example.com" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="auth-button">Sign In</button>
    </form>

    <p class="auth-bottom">
        Don't have an account? <a href="register.php">Register here</a>
    </p>

    <a href="index.php" class="back-home">← Back to Home</a>
</div>

</body>
</html>