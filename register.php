<?php
require_once "config/functions.php";

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $result = registerUser($name, $email, $password);
        if ($result["success"]) {
            $success = $result["message"];
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
    <title>Create Account - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <div class="auth-logo">
        <a href="index.php" style="color:inherit; text-decoration:none;">
            🔎 Lost<span>&</span>Found
        </a>
    </div>

    <h1>Create Account</h1>
    <p class="auth-subtitle">Join our community to manage your reports</p>

    <?php if ($error): ?>
        <div class="error-message">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message">
            <?= e($success) ?>
            <div style="margin-top: 10px;">
                <a href="login.php" class="btn primary sm" style="display:inline-block;">Proceed to Login →</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="POST" action="register.php">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>

            <button type="submit" class="auth-button">Create Account</button>
        </form>
    <?php endif; ?>

    <p class="auth-bottom">
        Already have an account? <a href="login.php">Login</a>
    </p>

    <a href="index.php" class="back-home">← Back to Home</a>
</div>

</body>
</html>