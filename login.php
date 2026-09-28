<?php
session_start();
include "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ? AND status = 'active'
         LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if ($password === $user["password"]) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] == "admin") {
                header("Location: admin/dashboard.php");
                exit();
            }

            if ($user["role"] == "vendor") {
                header("Location: vendor/dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid email or password.";
        }

    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | InventorySync</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                #111827,
                #2563eb
            );
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .logo {
            width: 65px;
            height: 65px;
            background: #2563eb;
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: auto;
        }

        .btn-login {
            background: #2563eb;
            border: none;
            padding: 12px;
            font-weight: bold;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .demo-box {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="login-card">

    <div class="text-center">

        <div class="logo">
            📦
        </div>

        <h3 class="mt-3 fw-bold">
            InventorySync
        </h3>

        <p class="text-muted">
            Sign in to your account
        </p>

    </div>

    <?php if ($error != ""): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">

            <label class="form-label">
                Email Address
            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter your email"
                required
            >

        </div>

        <div class="mb-3">

            <label class="form-label">
                Password
            </label>

            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required
            >

        </div>

        <button
            type="submit"
            class="btn btn-login btn-primary w-100"
        >
            Sign In
        </button>

    </form>
<p class="text-center text-muted mt-3 mb-0">
    Secure access for authorized users
</p>

    <div class="text-center mt-3">

        <a href="index.php"
           class="text-decoration-none">
            ← Back to Home
        </a>

    </div>

</div>

</body>

</html>