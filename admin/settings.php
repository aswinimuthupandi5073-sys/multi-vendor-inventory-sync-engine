```php
<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if (isset($_POST['save_settings'])) {

    $message = "Settings saved successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings - InventorySync</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>

        body {
            background: #f5f7fb;
        }

        .sidebar {
            min-height: 100vh;
            background: #111827;
        }

        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }

        .sidebar a:hover,
        .sidebar .active {
            background: #1f2937;
            color: #ffffff;
        }

        .brand {
            color: white;
            font-size: 21px;
            font-weight: 600;
            padding: 22px 20px;
        }

        .topbar {
            background: white;
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
        }

        .content {
            padding: 25px;
        }

        .settings-card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 sidebar p-0">

            <div class="brand">
                📦 InventorySync
            </div>

            <a href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>

            <a href="vendors.php">
                <i class="bi bi-people me-2"></i>
                Vendors
            </a>

            <a href="products.php">
                <i class="bi bi-box-seam me-2"></i>
                Products
            </a>

            <a href="warehouses.php">
                <i class="bi bi-building me-2"></i>
                Warehouses
            </a>

            <a href="inventory.php">
                <i class="bi bi-boxes me-2"></i>
                Inventory
            </a>

            <a href="orders.php">
                <i class="bi bi-cart-check me-2"></i>
                Orders
            </a>

            <a href="channels.php">
                <i class="bi bi-diagram-3 me-2"></i>
                Sync Channels
            </a>

            <a href="sync_engine.php">
                <i class="bi bi-arrow-repeat me-2"></i>
                Sync Engine
            </a>

            <a href="sync_logs.php">
                <i class="bi bi-clock-history me-2"></i>
                Sync Logs
            </a>

            <a href="settings.php" class="active">
                <i class="bi bi-gear me-2"></i>
                Settings
            </a>

            <a href="../logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </a>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9 col-lg-10 p-0">

            <div class="topbar d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-0">
                        Settings
                    </h5>

                    <small class="text-muted">
                        Manage system settings
                    </small>

                </div>

                <div>

                    <a href="dashboard.php"
                       class="btn btn-outline-primary">

                        <i class="bi bi-arrow-left"></i>
                        Back to Dashboard

                    </a>

                </div>

            </div>


            <div class="content">

                <?php if ($message): ?>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $message; ?>
                    </div>

                <?php endif; ?>


                <div class="card settings-card">

                    <div class="card-body p-4">

                        <h5 class="mb-4">
                            <i class="bi bi-sliders me-2"></i>
                            System Settings
                        </h5>


                        <form method="POST">

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Application Name
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        value="InventorySync"
                                        readonly>

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Default Stock Threshold
                                    </label>

                                    <input
                                        type="number"
                                        name="stock_threshold"
                                        class="form-control"
                                        value="10"
                                        min="0">

                                    <small class="text-muted">
                                        Products with stock at or below this value are shown as low stock.
                                    </small>

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Inventory Synchronization
                                    </label>

                                    <select
                                        name="sync_status"
                                        class="form-select">

                                        <option value="enabled">
                                            Enabled
                                        </option>

                                        <option value="disabled">
                                            Disabled
                                        </option>

                                    </select>

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Default Channel Status
                                    </label>

                                    <select
                                        name="channel_status"
                                        class="form-select">

                                        <option value="connected">
                                            Connected
                                        </option>

                                        <option value="disconnected">
                                            Disconnected
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <hr class="my-4">


                            <button
                                type="submit"
                                name="save_settings"
                                class="btn btn-primary">

                                <i class="bi bi-check-lg me-1"></i>
                                Save Settings

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
```
