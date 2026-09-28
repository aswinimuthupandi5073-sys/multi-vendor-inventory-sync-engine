<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* ADD / UPDATE INVENTORY */
if (isset($_POST['save_inventory'])) {

    $product_id = intval($_POST['product_id']);
    $warehouse_id = intval($_POST['warehouse_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 0) {
        $message = "Quantity cannot be negative.";
        $message_type = "danger";
    } else {

        $check = $conn->prepare(
            "SELECT id, quantity FROM inventory
             WHERE product_id = ? AND warehouse_id = ?"
        );

        $check->bind_param("ii", $product_id, $warehouse_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $inventory_id = $row['id'];
            $old_quantity = $row['quantity'];

            $update = $conn->prepare(
                "UPDATE inventory SET quantity = ?
                 WHERE id = ?"
            );

            $update->bind_param("ii", $quantity, $inventory_id);
            $update->execute();

            $transaction_qty = $quantity - $old_quantity;

            $type = "adjustment";

            $transaction = $conn->prepare(
                "INSERT INTO inventory_transactions
                (product_id, warehouse_id, type, quantity, reference)
                VALUES (?, ?, ?, ?, ?)"
            );

            $reference = "Manual inventory update";

            $transaction->bind_param(
                "iisis",
                $product_id,
                $warehouse_id,
                $type,
                $transaction_qty,
                $reference
            );

            $transaction->execute();

            $message = "Inventory updated successfully.";
            $message_type = "success";

        } else {

            $insert = $conn->prepare(
                "INSERT INTO inventory
                (product_id, warehouse_id, quantity)
                VALUES (?, ?, ?)"
            );

            $insert->bind_param(
                "iii",
                $product_id,
                $warehouse_id,
                $quantity
            );

            $insert->execute();

            $type = "stock_in";
            $reference = "Initial stock";

            $transaction = $conn->prepare(
                "INSERT INTO inventory_transactions
                (product_id, warehouse_id, type, quantity, reference)
                VALUES (?, ?, ?, ?, ?)"
            );

            $transaction->bind_param(
                "iisis",
                $product_id,
                $warehouse_id,
                $type,
                $quantity,
                $reference
            );

            $transaction->execute();

            $message = "Inventory added successfully.";
            $message_type = "success";
        }
    }
}

/* DELETE INVENTORY */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $delete = $conn->prepare(
        "DELETE FROM inventory WHERE id = ?"
    );

    $delete->bind_param("i", $id);
    $delete->execute();

    header("Location: inventory.php");
    exit();
}

/* PRODUCTS */
$products = $conn->query(
    "SELECT id, product_name, sku
     FROM products
     WHERE status = 'active'
     ORDER BY product_name"
);

/* WAREHOUSES */
$warehouses = $conn->query(
    "SELECT id, warehouse_name, location
     FROM warehouses
     WHERE status = 'active'
     ORDER BY warehouse_name"
);

/* INVENTORY LIST */
$inventory = $conn->query(
    "SELECT 
        inventory.id,
        products.product_name,
        products.sku,
        warehouses.warehouse_name,
        warehouses.location,
        inventory.quantity,
        inventory.updated_at
     FROM inventory
     INNER JOIN products
        ON inventory.product_id = products.id
     INNER JOIN warehouses
        ON inventory.warehouse_id = warehouses.id
     ORDER BY inventory.updated_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Inventory Management | InventorySync</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
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

        .brand {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 7px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        .main {
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 18px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }

        .table {
            vertical-align: middle;
        }

        .stock-good {
            background: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .stock-low {
            background: #fef3c7;
            color: #92400e;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .stock-out {
            background: #fee2e2;
            color: #991b1b;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->

        <div class="col-md-2 sidebar">

            <div class="brand">
                📦 InventorySync
            </div>

            <a href="dashboard.php">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="vendors.php">
                <i class="bi bi-people"></i>
                Vendors
            </a>

            <a href="products.php">
                <i class="bi bi-box-seam"></i>
                Products
            </a>

            <a href="warehouses.php">
                <i class="bi bi-building"></i>
                Warehouses
            </a>

            <a href="inventory.php" class="active">
                <i class="bi bi-bar-chart"></i>
                Inventory
            </a>

            <a href="orders.php">
                <i class="bi bi-cart"></i>
                Orders
            </a>

            <a href="sync_engine.php">
                <i class="bi bi-arrow-repeat"></i>
                Sync Engine
            </a>

            <a href="sync_logs.php">
                <i class="bi bi-clock-history"></i>
                Sync Logs
            </a>

            <hr>

            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-10 main">

            <div class="topbar d-flex justify-content-between align-items-center">

                <div>
                    <h4 class="mb-1">Inventory Management</h4>

                    <small class="text-muted">
                        Manage stock across multiple warehouses
                    </small>
                </div>
<a href="dashboard.php" class="btn btn-outline-primary mb-3">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>
                <div>
                    <span class="badge bg-primary">
                        <i class="bi bi-person-circle"></i>
                        Admin
                    </span>
                </div>

            </div>


            <?php if ($message != ""): ?>

                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">

                    <?php echo htmlspecialchars($message); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>


            <!-- ADD INVENTORY -->

            <div class="card p-4 mb-4">

                <h5 class="mb-3">
                    <i class="bi bi-plus-circle"></i>
                    Add / Update Stock
                </h5>

                <form method="POST">

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Product
                            </label>

                            <select
                                name="product_id"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Product
                                </option>

                                <?php while ($product = $products->fetch_assoc()): ?>

                                    <option value="<?php echo $product['id']; ?>">

                                        <?php echo htmlspecialchars($product['product_name']); ?>

                                        -
                                        <?php echo htmlspecialchars($product['sku']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Warehouse
                            </label>

                            <select
                                name="warehouse_id"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Warehouse
                                </option>

                                <?php while ($warehouse = $warehouses->fetch_assoc()): ?>

                                    <option value="<?php echo $warehouse['id']; ?>">

                                        <?php echo htmlspecialchars($warehouse['warehouse_name']); ?>

                                        -
                                        <?php echo htmlspecialchars($warehouse['location']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <div class="col-md-2 mb-3">

                            <label class="form-label">
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity"
                                class="form-control"
                                min="0"
                                required>

                        </div>


                        <div class="col-md-2 mb-3 d-flex align-items-end">

                            <button
                                type="submit"
                                name="save_inventory"
                                class="btn btn-primary w-100">

                                <i class="bi bi-save"></i>
                                Save Stock

                            </button>

                        </div>

                    </div>

                </form>

            </div>


            <!-- INVENTORY TABLE -->

            <div class="card p-4">

                <div class="d-flex justify-content-between mb-3">

                    <h5 class="mb-0">
                        Current Inventory
                    </h5>

                    <span class="text-muted">
                        Live Stock
                    </span>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>

                            <tr>

                                <th>Product</th>
                                <th>SKU</th>
                                <th>Warehouse</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($inventory->num_rows > 0): ?>

                            <?php while ($row = $inventory->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($row['sku']); ?>
                                        </span>
                                    </td>

                                    <td>

                                        <?php echo htmlspecialchars($row['warehouse_name']); ?>

                                        <br>

                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($row['location']); ?>
                                        </small>

                                    </td>

                                    <td>
                                        <strong>
                                            <?php echo $row['quantity']; ?>
                                        </strong>
                                    </td>

                                    <td>

                                        <?php if ($row['quantity'] == 0): ?>

                                            <span class="stock-out">
                                                Out of Stock
                                            </span>

                                        <?php elseif ($row['quantity'] <= 10): ?>

                                            <span class="stock-low">
                                                Low Stock
                                            </span>

                                        <?php else: ?>

                                            <span class="stock-good">
                                                In Stock
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <small>
                                            <?php echo $row['updated_at']; ?>
                                        </small>
                                    </td>

                                    <td>

                                        <a
                                            href="inventory.php?delete=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this inventory record?');">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="7"
                                    class="text-center text-muted py-4">

                                    No inventory records found.

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

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>