<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";

/* ADD VENDOR */

if (isset($_POST["add_vendor"])) {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    if ($name != "" && $email != "") {

        $password = "vendor123";

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, 'vendor')"
        );

        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {

            $user_id = $conn->insert_id;

            $stmt2 = $conn->prepare(
                "INSERT INTO vendors
                 (user_id, vendor_name, email, phone)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt2->bind_param(
                "isss",
                $user_id,
                $name,
                $email,
                $phone
            );

            $stmt2->execute();

            $message = "Vendor added successfully.";

        } else {

            $message = "Unable to add vendor.";

        }
    }
}

/* DELETE VENDOR */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare(
        "SELECT user_id FROM vendors WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $vendor = $result->fetch_assoc();

        $user_id = $vendor["user_id"];

        $delete = $conn->prepare(
            "DELETE FROM vendors WHERE id = ?"
        );

        $delete->bind_param("i", $id);
        $delete->execute();

        if ($user_id) {

            $deleteUser = $conn->prepare(
                "DELETE FROM users WHERE id = ? AND role = 'vendor'"
            );

            $deleteUser->bind_param("i", $user_id);
            $deleteUser->execute();
        }

        header("Location: vendors.php");
        exit();
    }
}

$vendors = $conn->query(
    "SELECT vendors.*, users.status AS user_status
     FROM vendors
     LEFT JOIN users ON vendors.user_id = users.id
     ORDER BY vendors.id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Vendors | InventorySync</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<style>

body {
    background: #f5f7fb;
    font-family: Arial, sans-serif;
}

.sidebar {
    min-height: 100vh;
    background: #111827;
    color: white;
    padding: 25px 15px;
}

.logo {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 35px;
}

.sidebar a {
    display: block;
    color: #d1d5db;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 5px;
}

.sidebar a:hover,
.sidebar .active {
    background: #2563eb;
    color: white;
}

.topbar {
    background: white;
    padding: 18px 25px;
    border-bottom: 1px solid #e5e7eb;
}

.content {
    padding: 30px;
}

.card {
    border: none;
    border-radius: 15px;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="row">

<!-- SIDEBAR -->

<div class="col-md-3 col-lg-2 sidebar">

    <div class="logo">
        📦 InventorySync
    </div>

    <small class="text-secondary">
        ADMIN PANEL
    </small>

    <div class="mt-3">

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="vendors.php" class="active">
            🏪 Vendors
        </a>

        <a href="products.php">
            📦 Products
        </a>

        <a href="#warehouses.php">
            🏭 Warehouses
        </a>

        <a href="inventory.php">
            📊 Inventory
        </a>

        <a href="orders.php">
            🛒 Orders
        </a>

        <a href="sync_engine.php">
            🔄 Sync Engine
        </a>

        <a href="sync_logs.php">
            📋 Sync Logs
        </a>

        <a href="settings.php">
            ⚙️ Settings
        </a>

        <hr>

        <a href="../logout.php">
            🚪 Logout
        </a>

    </div>

</div>


<!-- MAIN -->

<div class="col-md-9 col-lg-10 p-0">

    <div class="topbar d-flex justify-content-between">

        <div>
            
            <h5 class="mb-0">
                Vendor Management
            </h5>

            <small class="text-muted">
                Manage registered vendors
            </small>
        </div>
<a href="dashboard.php" class="btn btn-outline-primary mb-3">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>
        <div>
            👤 <?= htmlspecialchars($_SESSION["name"]) ?>
        </div>

    </div>


    <div class="content">

        <?php if ($message != ""): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <!-- ADD VENDOR -->

        <div class="card shadow-sm mb-4">

            <div class="card-body p-4">

                <h5 class="mb-3">
                    Add New Vendor
                </h5>

                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Vendor Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Enter vendor name"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="vendor@example.com"
                                required
                            >

                        </div>


                        <div class="col-md-3">

                            <label class="form-label">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                placeholder="9876543210"
                            >

                        </div>


                        <div class="col-md-1 d-flex align-items-end">

                            <button
                                type="submit"
                                name="add_vendor"
                                class="btn btn-primary w-100"
                            >
                                +
                            </button>

                        </div>

                    </div>

                </form>

                <small class="text-muted d-block mt-3">
                    Default vendor password: <b>vendor123</b>
                </small>

            </div>

        </div>


        <!-- VENDOR LIST -->

        <div class="card shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between mb-3">

                    <h5>
                        Registered Vendors
                    </h5>

                    <span class="badge bg-primary">
                        <?= $vendors->num_rows ?> Vendors
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Vendor</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($vendors->num_rows > 0): ?>

                            <?php while ($vendor = $vendors->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= $vendor["id"] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($vendor["vendor_name"]) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($vendor["email"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($vendor["phone"]) ?>
                                </td>

                                <td>

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <a
                                        href="vendors.php?delete=<?= $vendor["id"] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this vendor?')"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-4">

                                    No vendors found.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

</div>

</body>
</html>