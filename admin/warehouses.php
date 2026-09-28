<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";


/* ADD WAREHOUSE */

if (isset($_POST["add_warehouse"])) {

    $warehouse_name = trim($_POST["warehouse_name"]);
    $location = trim($_POST["location"]);

    if ($warehouse_name == "") {

        $error = "Warehouse name is required.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO warehouses (warehouse_name, location)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ss",
            $warehouse_name,
            $location
        );

        if ($stmt->execute()) {
            $message = "Warehouse added successfully.";
        } else {
            $error = "Unable to add warehouse.";
        }

        $stmt->close();
    }
}


/* DELETE WAREHOUSE */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare(
        "DELETE FROM warehouses WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: warehouses.php");
        exit();
    }

    $stmt->close();
}


/* GET WAREHOUSES */

$warehouses = $conn->query(
    "SELECT *
     FROM warehouses
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Warehouses | InventorySync</title>

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

.warehouse-icon {
    width: 40px;
    height: 40px;
    background: #eff6ff;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
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

        <a href="vendors.php">
            🏪 Vendors
        </a>

        <a href="products.php">
            📦 Products
        </a>

        <a href="warehouses.php" class="active">
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


<!-- MAIN CONTENT -->

<div class="col-md-9 col-lg-10 p-0">


    <!-- TOPBAR -->

    <div class="topbar d-flex justify-content-between align-items-center">

        <div>

            <h5 class="mb-0">
                Warehouse Management
            </h5>

            <small class="text-muted">
                Manage inventory storage locations
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


        <!-- ALERTS -->

        <?php if ($message != ""): ?>

            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error != ""): ?>

            <div class="alert alert-danger">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- ADD WAREHOUSE -->

        <div class="card shadow-sm mb-4">

            <div class="card-body p-4">

                <h5>
                    Add New Warehouse
                </h5>

                <p class="text-muted">
                    Add a location where inventory will be stored.
                </p>


                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-5">

                            <label class="form-label">
                                Warehouse Name
                            </label>

                            <input
                                type="text"
                                name="warehouse_name"
                                class="form-control"
                                placeholder="e.g. Bangalore Warehouse"
                                required
                            >

                        </div>


                        <div class="col-md-5">

                            <label class="form-label">
                                Location
                            </label>

                            <input
                                type="text"
                                name="location"
                                class="form-control"
                                placeholder="e.g. Bangalore"
                            >

                        </div>


                        <div class="col-md-2 d-flex align-items-end">

                            <button
                                type="submit"
                                name="add_warehouse"
                                class="btn btn-primary w-100"
                            >
                                + Add Warehouse
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <!-- WAREHOUSE LIST -->

        <div class="card shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5 class="mb-1">
                            Warehouses
                        </h5>

                        <small class="text-muted">
                            Registered storage locations
                        </small>

                    </div>

                    <span class="badge bg-primary">
                        <?= $warehouses->num_rows ?> Warehouses
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Warehouse</th>

                                <th>Location</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($warehouses->num_rows > 0): ?>

                            <?php while ($warehouse = $warehouses->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= $warehouse["id"] ?>
                                </td>


                                <td>

                                    <span class="warehouse-icon">
                                        🏭
                                    </span>

                                    <strong>
                                        <?= htmlspecialchars($warehouse["warehouse_name"]) ?>
                                    </strong>

                                </td>


                                <td>

                                    📍
                                    <?= htmlspecialchars($warehouse["location"]) ?>

                                </td>


                                <td>

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                </td>


                                <td>

                                    <?= date(
                                        "d M Y",
                                        strtotime($warehouse["created_at"])
                                    ) ?>

                                </td>


                                <td>

                                    <a
                                        href="warehouses.php?delete=<?= $warehouse["id"] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this warehouse?')"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-5">

                                    No warehouses found.

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