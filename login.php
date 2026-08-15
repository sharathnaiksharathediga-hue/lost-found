<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];


    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();


            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];

                $_SESSION["user_name"] = $user["name"];

                $_SESSION["user_email"] = $user["email"];


                header("Location: dashboard.php");

                exit;

            } else {

                $error = "Incorrect email or password.";

            }

        } else {

            $error = "Incorrect email or password.";

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

    <title>Login - Lost & Found</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="auth-page">


<div class="auth-card">

    <div class="auth-logo">
        🔎 Lost<span>&</span>Found
    </div>


    <h1>Welcome Back</h1>

    <p class="auth-subtitle">
        Login to manage your reports
    </p>


    <?php if ($error): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">


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
            placeholder="Enter your password"
            required
        >


        <button
            type="submit"
            class="auth-button"
        >
            Login
        </button>

    </form>


    <p class="auth-bottom">

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </p>


    <a href="index.php" class="back-home">
        ← Back to Home
    </a>

</div>


</body>

</html>