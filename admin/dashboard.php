<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$vendors = $conn->query("SELECT COUNT(*) AS total FROM vendors")->fetch_assoc()["total"];
$products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()["total"];
$warehouses = $conn->query("SELECT COUNT(*) AS total FROM warehouses")->fetch_assoc()["total"];
$syncs = $conn->query("SELECT COUNT(*) AS total FROM sync_logs")->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard | InventorySync</title>

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

.stat-card {
    background: white;
    border: none;
    border-radius: 15px;
    padding: 22px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.stat-icon {
    font-size: 30px;
}

.stat-number {
    font-size: 30px;
    font-weight: bold;
}

.content {
    padding: 30px;
}

.welcome {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    border-radius: 15px;
    padding: 30px;
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

            <a href="dashboard.php" class="active">
                🏠 Dashboard
            </a>

            <a href="vendors.php">
    🏪 Vendors
</a>

            <a href="products.php">
    📦 Products
</a>

           <a href="warehouses.php">
    🏭 Warehouses
</a>

            <a href="inventory.php">📊 Inventory</a>

            <a href="orders.php">
                🛒 Orders
            </a>
            <a href="channels.php">
<i class="bi bi-diagram-3"></i>
Sync Channels
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

        <div class="topbar d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-0">
                    Admin Dashboard
                </h5>

                <small class="text-muted">
                    Inventory Management Overview
                </small>
            </div>

            <div>
                👤 <?= htmlspecialchars($_SESSION["name"]) ?>
            </div>

        </div>


        <div class="content">

            <div class="welcome mb-4">

                <h3>
                    Welcome back, <?= htmlspecialchars($_SESSION["name"]) ?> 👋
                </h3>

                <p class="mb-0">
                    Manage vendors, products, inventory and
                    synchronization from one place.
                </p>

            </div>


            <!-- STAT CARDS -->

            <div class="row g-4">

                <div class="col-md-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-icon">
                            🏪
                        </div>

                        <div class="text-muted mt-2">
                            Total Vendors
                        </div>

                        <div class="stat-number">
                            <?= $vendors ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-icon">
                            📦
                        </div>

                        <div class="text-muted mt-2">
                            Total Products
                        </div>

                        <div class="stat-number">
                            <?= $products ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-icon">
                            🏭
                        </div>

                        <div class="text-muted mt-2">
                            Warehouses
                        </div>

                        <div class="stat-number">
                            <?= $warehouses ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-icon">
                            🔄
                        </div>

                        <div class="text-muted mt-2">
                            Sync Records
                        </div>

                        <div class="stat-number">
                            <?= $syncs ?>
                        </div>

                    </div>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="card border-0 shadow-sm mt-4">

                <div class="card-body p-4">

                    <h5>
                        Quick Actions
                    </h5>

                    <p class="text-muted">
                        Manage your inventory system
                    </p>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <button class="btn btn-primary w-100">
                                + Add Vendor
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100">
                                + Add Product
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100">
                                📊 Inventory
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100">
                                🔄 Sync Now
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

</body>
</html>