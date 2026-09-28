<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* GET VENDOR */
$vendor_stmt = $conn->prepare(
    "SELECT id, vendor_name
     FROM vendors
     WHERE user_id = ?
     LIMIT 1"
);

$vendor_stmt->bind_param("i", $user_id);
$vendor_stmt->execute();

$vendor_result = $vendor_stmt->get_result();

if ($vendor_result->num_rows == 0) {
    die("Vendor profile not found.");
}

$vendor = $vendor_result->fetch_assoc();
$vendor_id = $vendor['id'];


/* GET ORDERS */
$order_stmt = $conn->prepare(
    "SELECT
        o.id,
        o.order_number,
        o.customer_name,
        o.total_amount,
        o.quantity,
        o.status,
        o.created_at,
        p.product_name,
        p.sku
     FROM orders o
     LEFT JOIN products p
        ON o.product_id = p.id
     WHERE o.vendor_id = ?
     ORDER BY o.created_at DESC"
);

$order_stmt->bind_param("i", $vendor_id);
$order_stmt->execute();

$order_result = $order_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Orders | InventorySync</title>

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

.pending {
    background: #fef3c7;
    color: #92400e;
}

.confirmed {
    background: #dbeafe;
    color: #1d4ed8;
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

<a href="products.php">

<i class="bi bi-box-seam"></i>
My Products

</a>

<a href="inventory.php">

<i class="bi bi-bar-chart"></i>
My Inventory

</a>

<a href="orders.php" class="active">

<i class="bi bi-cart"></i>
My Orders

</a>

<a href="#">

<i class="bi bi-arrow-repeat"></i>
Sync Status

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
My Orders
</h4>

<small class="text-muted">
View and monitor your vendor orders
</small>

</div>
<a href="dashboard.php" class="btn btn-outline-primary mb-3">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>

<span class="badge bg-success">

<i class="bi bi-shop"></i>
Vendor

</span>

</div>


<div class="card p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h5 class="mb-1">
Order Management
</h5>

<small class="text-muted">
<?php echo htmlspecialchars($vendor['vendor_name']); ?>
</small>

</div>

<span class="badge bg-primary">

<?php echo $order_result->num_rows; ?>

Orders

</span>

</div>


<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>#</th>

<th>Order Number</th>

<th>Product</th>

<th>Customer</th>

<th>Qty</th>

<th>Total</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>


<tbody>

<?php if ($order_result->num_rows > 0): ?>

<?php $number = 1; ?>

<?php while ($order = $order_result->fetch_assoc()): ?>

<tr>

<td>
<?php echo $number++; ?>
</td>


<td>

<strong>

<?php echo htmlspecialchars($order['order_number']); ?>

</strong>

</td>


<td>

<strong>

<?php echo htmlspecialchars(
    $order['product_name'] ?? 'Product Removed'
); ?>

</strong>

<br>

<small class="text-muted">

<?php echo htmlspecialchars(
    $order['sku'] ?? '-'
); ?>

</small>

</td>


<td>

<?php echo htmlspecialchars($order['customer_name']); ?>

</td>


<td>

<strong>

<?php echo $order['quantity']; ?>

</strong>

</td>


<td>

<strong>

₹<?php echo number_format(
    $order['total_amount'],
    2
); ?>

</strong>

</td>


<td>

<span class="status <?php echo htmlspecialchars($order['status']); ?>">

<?php echo ucfirst(
    htmlspecialchars($order['status'])
); ?>

</span>

</td>


<td>

<small class="text-muted">

<?php echo date(
    "d M Y, h:i A",
    strtotime($order['created_at'])
); ?>

</small>

</td>

</tr>

<?php endwhile; ?>


<?php else: ?>

<tr>

<td colspan="8" class="text-center text-muted py-5">

<i class="bi bi-cart-x fs-1"></i>

<br><br>

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

</body>

</html>