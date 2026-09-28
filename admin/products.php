<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

/* ADD PRODUCT */
if (isset($_POST["add_product"])) {

    $product_name = trim($_POST["product_name"]);
    $sku = trim($_POST["sku"]);
    $price = floatval($_POST["price"]);
    $vendor_id = intval($_POST["vendor_id"]);

    if ($product_name == "" || $sku == "" || $vendor_id <= 0) {

        $error = "Please fill all required fields.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (vendor_id, product_name, sku, price)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issd",
            $vendor_id,
            $product_name,
            $sku,
            $price
        );

        if ($stmt->execute()) {
            $message = "Product added successfully.";
        } else {
            $error = "SKU already exists or product could not be added.";
        }

        $stmt->close();
    }
}


/* DELETE PRODUCT */
if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare(
        "DELETE FROM products WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: products.php");
        exit();
    }

    $stmt->close();
}


/* GET VENDORS */
$vendor_list = $conn->query(
    "SELECT id, vendor_name
     FROM vendors
     WHERE status = 'active'
     ORDER BY vendor_name"
);


/* GET PRODUCTS */
$products = $conn->query(
    "SELECT products.*,
            vendors.vendor_name
     FROM products
     LEFT JOIN vendors
     ON products.vendor_id = vendors.id
     ORDER BY products.id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Products | InventorySync</title>

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

.form-control,
.form-select {
    border-radius: 8px;
    padding: 10px;
}

.table th {
    white-space: nowrap;
}

.product-icon {
    width: 38px;
    height: 38px;
    background: #eff6ff;
    border-radius: 8px;
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

        <a href="products.php" class="active">
            📦 Products
        </a>

        <a href="warehouses.php">
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

    <div class="topbar d-flex justify-content-between align-items-center">

        <div>

            <h5 class="mb-0">
                Product Management
            </h5>

            <small class="text-muted">
                Manage products and SKU information
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


        <!-- SUCCESS -->

        <?php if ($message != ""): ?>

            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <!-- ERROR -->

        <?php if ($error != ""): ?>

            <div class="alert alert-danger">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- ADD PRODUCT -->

        <div class="card shadow-sm mb-4">

            <div class="card-body p-4">

                <h5 class="mb-1">
                    Add New Product
                </h5>

                <p class="text-muted mb-4">
                    Create a product and assign it to a vendor.
                </p>


                <form method="POST">

                    <div class="row g-3">


                        <div class="col-md-3">

                            <label class="form-label">
                                Product Name
                            </label>

                            <input
                                type="text"
                                name="product_name"
                                class="form-control"
                                placeholder="e.g. Laptop Bag"
                                required
                            >

                        </div>


                        <div class="col-md-3">

                            <label class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                name="sku"
                                class="form-control"
                                placeholder="e.g. LB-1001"
                                required
                            >

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Price (₹)
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                required
                            >

                        </div>


                        <div class="col-md-3">

                            <label class="form-label">
                                Vendor
                            </label>

                            <select
                                name="vendor_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Vendor
                                </option>

                                <?php while ($vendor = $vendor_list->fetch_assoc()): ?>

                                    <option value="<?= $vendor["id"] ?>">
                                        <?= htmlspecialchars($vendor["vendor_name"]) ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <div class="col-md-1 d-flex align-items-end">

                            <button
                                type="submit"
                                name="add_product"
                                class="btn btn-primary w-100"
                            >
                                Add
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <!-- PRODUCT TABLE -->

        <div class="card shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5 class="mb-1">
                            Product Catalog
                        </h5>

                        <small class="text-muted">
                            All products registered in the system
                        </small>

                    </div>

                    <span class="badge bg-primary">
                        <?= $products->num_rows ?> Products
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Product</th>

                                <th>SKU</th>

                                <th>Vendor</th>

                                <th>Price</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($products->num_rows > 0): ?>

                            <?php while ($product = $products->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= $product["id"] ?>
                                </td>


                                <td>

                                    <span class="product-icon">
                                        📦
                                    </span>

                                    <strong>
                                        <?= htmlspecialchars($product["product_name"]) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($product["sku"]) ?>
                                    </span>

                                </td>


                                <td>
                                    <?= htmlspecialchars($product["vendor_name"] ?? "N/A") ?>
                                </td>


                                <td>

                                    <strong>
                                        ₹<?= number_format($product["price"], 2) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="products.php?delete=<?= $product["id"] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this product?')"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="7"
                                    class="text-center text-muted py-5">

                                    No products found.

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