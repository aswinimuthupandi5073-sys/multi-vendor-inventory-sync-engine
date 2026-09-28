<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* CREATE ORDER */
if (isset($_POST['create_order'])) {

    $order_number = "ORD-" . date("YmdHis");
    $vendor_id = intval($_POST['vendor_id']);
    $product_id = intval($_POST['product_id']);
    $customer_name = trim($_POST['customer_name']);
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {
        $message = "Quantity must be greater than 0.";
        $message_type = "danger";
    } else {

        /* GET PRODUCT PRICE */
        $product_stmt = $conn->prepare(
            "SELECT price FROM products
             WHERE id = ? AND status = 'active'"
        );

        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();

        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows == 0) {

            $message = "Product not found.";
            $message_type = "danger";

        } else {

            $product = $product_result->fetch_assoc();
            $price = $product['price'];
            $total_amount = $price * $quantity;

            /* CHECK STOCK */
            $stock_stmt = $conn->prepare(
                "SELECT id, quantity
                 FROM inventory
                 WHERE product_id = ?
                 LIMIT 1"
            );

            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();

            $stock_result = $stock_stmt->get_result();

            if ($stock_result->num_rows == 0) {

                $message = "No inventory found for this product.";
                $message_type = "danger";

            } else {

                $stock = $stock_result->fetch_assoc();
                $stock_id = $stock['id'];
                $current_stock = $stock['quantity'];

                if ($current_stock < $quantity) {

                    $message = "Insufficient stock. Available stock: " . $current_stock;
                    $message_type = "danger";

                } else {

                    /* CREATE ORDER */

                    $order_stmt = $conn->prepare(
                        "INSERT INTO orders
                        (order_number, vendor_id, product_id,
                         customer_name, total_amount, quantity, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')"
                    );

                    $order_stmt->bind_param(
                        "siisdi",
                        $order_number,
                        $vendor_id,
                        $product_id,
                        $customer_name,
                        $total_amount,
                        $quantity
                    );

                    if ($order_stmt->execute()) {

                        /* REDUCE STOCK */

                        $new_stock = $current_stock - $quantity;

                        $update_stock = $conn->prepare(
                            "UPDATE inventory
                             SET quantity = ?
                             WHERE id = ?"
                        );

                        $update_stock->bind_param(
                            "ii",
                            $new_stock,
                            $stock_id
                        );

                        $update_stock->execute();

                        /* TRANSACTION LOG */

                        $transaction_type = "stock_out";
                        $reference = $order_number;

                        $transaction = $conn->prepare(
                            "INSERT INTO inventory_transactions
                            (product_id, warehouse_id, type,
                             quantity, reference)
                            SELECT product_id, warehouse_id, ?,
                                   ?, ?
                            FROM inventory
                            WHERE id = ?"
                        );

                        $transaction->bind_param(
                            "sisi",
                            $transaction_type,
                            $quantity,
                            $reference,
                            $stock_id
                        );

                        $transaction->execute();

                        $message = "Order created successfully. Stock updated.";
                        $message_type = "success";

                    } else {

                        $message = "Order could not be created.";
                        $message_type = "danger";
                    }
                }
            }
        }
    }
}

/* CANCEL ORDER */
if (isset($_GET['cancel'])) {

    $id = intval($_GET['cancel']);

    $order_stmt = $conn->prepare(
        "SELECT product_id, quantity, status
         FROM orders
         WHERE id = ?"
    );

    $order_stmt->bind_param("i", $id);
    $order_stmt->execute();

    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows > 0) {

        $order = $order_result->fetch_assoc();

        if ($order['status'] != 'cancelled') {

            $product_id = $order['product_id'];
            $quantity = $order['quantity'];

            /* RESTORE STOCK */

            $stock_stmt = $conn->prepare(
                "SELECT id
                 FROM inventory
                 WHERE product_id = ?
                 LIMIT 1"
            );

            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();

            $stock_result = $stock_stmt->get_result();

            if ($stock_result->num_rows > 0) {

                $stock = $stock_result->fetch_assoc();
                $stock_id = $stock['id'];

                $update = $conn->prepare(
                    "UPDATE inventory
                     SET quantity = quantity + ?
                     WHERE id = ?"
                );

                $update->bind_param(
                    "ii",
                    $quantity,
                    $stock_id
                );

                $update->execute();
            }

            /* UPDATE ORDER */

            $cancel_stmt = $conn->prepare(
                "UPDATE orders
                 SET status = 'cancelled'
                 WHERE id = ?"
            );

            $cancel_stmt->bind_param("i", $id);
            $cancel_stmt->execute();
        }
    }

    header("Location: orders.php");
    exit();
}


/* VENDORS */
$vendors = $conn->query(
    "SELECT id, vendor_name
     FROM vendors
     WHERE status = 'active'
     ORDER BY vendor_name"
);


/* PRODUCTS */
$products = $conn->query(
    "SELECT id, product_name, sku, price
     FROM products
     WHERE status = 'active'
     ORDER BY product_name"
);


/* ORDERS */
$orders = $conn->query(
    "SELECT
        orders.*,
        vendors.vendor_name,
        products.product_name,
        products.sku
     FROM orders
     LEFT JOIN vendors
        ON orders.vendor_id = vendors.id
     LEFT JOIN products
        ON orders.product_id = products.id
     ORDER BY orders.created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Orders | InventorySync</title>

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

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.confirmed {
    background: #dbeafe;
    color: #1d4ed8;
}

.pending {
    background: #fef3c7;
    color: #92400e;
}

.shipped {
    background: #e0e7ff;
    color: #4338ca;
}

.delivered {
    background: #dcfce7;
    color: #166534;
}

.cancelled {
    background: #fee2e2;
    color: #991b1b;
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

<a href="inventory.php">
<i class="bi bi-bar-chart"></i>
Inventory
</a>

<a href="orders.php" class="active">
<i class="bi bi-cart"></i>
Orders
</a>

<a href="#">
<i class="bi bi-arrow-repeat"></i>
Sync Engine
</a>

<a href="#">
<i class="bi bi-clock-history"></i>
Sync Logs
</a>

<hr>

<a href="../logout.php">
<i class="bi bi-box-arrow-right"></i>
Logout
</a>

</div>


<!-- MAIN -->

<div class="col-md-10 main">

<div class="topbar d-flex justify-content-between align-items-center">

<div>

<h4 class="mb-1">
Order Management
</h4>

<small class="text-muted">
Create orders and automatically update inventory
</small>

</div>
<a href="dashboard.php" class="btn btn-outline-primary mb-3">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>

<span class="badge bg-primary">
<i class="bi bi-person-circle"></i>
Admin
</span>

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


<!-- CREATE ORDER -->

<div class="card p-4 mb-4">

<h5 class="mb-3">

<i class="bi bi-cart-plus"></i>
Create New Order

</h5>

<form method="POST">

<div class="row">

<div class="col-md-3 mb-3">

<label class="form-label">
Vendor
</label>

<select
name="vendor_id"
class="form-select"
required>

<option value="">
Select Vendor
</option>

<?php while ($vendor = $vendors->fetch_assoc()): ?>

<option value="<?php echo $vendor['id']; ?>">

<?php echo htmlspecialchars($vendor['vendor_name']); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="col-md-3 mb-3">

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
₹<?php echo number_format($product['price'], 2); ?>

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
min="1"
value="1"
required>

</div>


<div class="col-md-2 mb-3">

<label class="form-label">
Customer
</label>

<input
type="text"
name="customer_name"
class="form-control"
placeholder="Customer name"
required>

</div>


<div class="col-md-2 mb-3 d-flex align-items-end">

<button
type="submit"
name="create_order"
class="btn btn-primary w-100">

<i class="bi bi-plus-lg"></i>
Create

</button>

</div>

</div>

</form>

</div>


<!-- ORDERS TABLE -->

<div class="card p-4">

<div class="d-flex justify-content-between mb-3">

<h5 class="mb-0">
Recent Orders
</h5>

<span class="text-muted">
Order History
</span>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>Order</th>
<th>Vendor</th>
<th>Product</th>
<th>Customer</th>
<th>Qty</th>
<th>Total</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php if ($orders->num_rows > 0): ?>

<?php while ($order = $orders->fetch_assoc()): ?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($order['order_number']); ?>
</strong>

</td>

<td>
<?php echo htmlspecialchars($order['vendor_name'] ?? 'N/A'); ?>
</td>

<td>

<?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?>

<br>

<small class="text-muted">
<?php echo htmlspecialchars($order['sku'] ?? ''); ?>
</small>

</td>

<td>
<?php echo htmlspecialchars($order['customer_name']); ?>
</td>

<td>
<?php echo $order['quantity']; ?>
</td>

<td>

<strong>
₹<?php echo number_format($order['total_amount'], 2); ?>
</strong>

</td>

<td>

<span class="status <?php echo $order['status']; ?>">

<?php echo ucfirst($order['status']); ?>

</span>

</td>

<td>

<small>
<?php echo $order['created_at']; ?>
</small>

</td>

<td>

<?php if ($order['status'] != 'cancelled'): ?>

<a
href="orders.php?cancel=<?php echo $order['id']; ?>"
class="btn btn-sm btn-outline-danger"
onclick="return confirm('Cancel this order and restore stock?');">

<i class="bi bi-x-circle"></i>

</a>

<?php else: ?>

<span class="text-muted">
No Action
</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="9"
class="text-center text-muted py-4">

No orders found.

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