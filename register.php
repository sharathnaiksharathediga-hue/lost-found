<?php

session_start();

require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];


    if ($name === "" || $email === "" || $password === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $error = "An account with this email already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashedPassword
            );


            if ($stmt->execute()) {

                $success =
                    "Registration successful! You can now login.";

            } else {

                $error =
                    "Something went wrong. Please try again.";
            }

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Account - Lost & Found</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="auth-page">


<div class="auth-card">

    <div class="auth-logo">
        🔎 Lost<span>&</span>Found
    </div>


    <h1>Create Account</h1>

    <p class="auth-subtitle">
        Join the Lost & Found community
    </p>


    <?php if ($error): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="success-message">

            <?php echo htmlspecialchars($success); ?>

            <br><br>

            <a href="login.php">
                Login now →
            </a>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>Full Name</label>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >


        <label>Email Address</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            id="password"
            placeholder="Minimum 6 characters"
            required
        >


        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm your password"
            required
        >


        <button
            type="submit"
            class="auth-button"
        >
            Create Account
        </button>

    </form>


    <p class="auth-bottom">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </p>


    <a href="index.php" class="back-home">
        ← Back to Home
    </a>

</div>


</body>

</html>