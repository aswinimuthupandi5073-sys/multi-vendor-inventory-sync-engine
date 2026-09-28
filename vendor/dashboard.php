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


/* PRODUCT COUNT */
$product_count = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products
     WHERE vendor_id = $vendor_id"
)->fetch_assoc()['total'];


/* TOTAL STOCK */
$stock_result = $conn->query(
    "SELECT COALESCE(SUM(i.quantity),0) AS total
     FROM inventory i
     INNER JOIN products p
        ON i.product_id = p.id
     WHERE p.vendor_id = $vendor_id"
);

$total_stock = $stock_result->fetch_assoc()['total'];


/* LOW STOCK */
$low_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM inventory i
     INNER JOIN products p
        ON i.product_id = p.id
     WHERE p.vendor_id = $vendor_id
     AND i.quantity <= 10"
);

$low_stock = $low_result->fetch_assoc()['total'];


/* ORDERS */
$order_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders o
     WHERE o.vendor_id = $vendor_id"
);

$order_count = $order_result->fetch_assoc()['total'];


/* OWN PRODUCTS */
$products = $conn->query(
    "SELECT
        p.product_name,
        p.sku,
        p.price,
        p.status,
        COALESCE(SUM(i.quantity),0) AS stock
     FROM products p
     LEFT JOIN inventory i
        ON p.id = i.product_id
     WHERE p.vendor_id = $vendor_id
     GROUP BY p.id
     ORDER BY p.created_at DESC
     LIMIT 10"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Vendor Dashboard | InventorySync</title>

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

.stat-card {
    background: white;
    border: none;
    border-radius: 15px;
    padding: 22px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
}

.stat-icon {
    font-size: 30px;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
}

.stat-label {
    color: #6b7280;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
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

.table {
    vertical-align: middle;
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

<a href="dashboard.php" class="active">
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

<a href="orders.php">
<i class="bi bi-cart"></i>
My Orders
</a>

<a href="sync_status.php">
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
Welcome, <?php echo htmlspecialchars($vendor['vendor_name']); ?>
</h4>

<small class="text-muted">
Vendor Inventory Dashboard
</small>

</div>

<span class="badge bg-success">
<i class="bi bi-shop"></i>
Vendor
</span>

</div>


<!-- STATS -->

<div class="row g-4 mb-4">

<div class="col-md-3">

<div class="stat-card">

<div class="stat-icon text-primary">
<i class="bi bi-box-seam"></i>
</div>

<div class="stat-number">
<?php echo $product_count; ?>
</div>

<div class="stat-label">
My Products
</div>

</div>

</div>


<div class="col-md-3">

<div class="stat-card">

<div class="stat-icon text-success">
<i class="bi bi-boxes"></i>
</div>

<div class="stat-number">
<?php echo $total_stock; ?>
</div>

<div class="stat-label">
Total Stock
</div>

</div>

</div>


<div class="col-md-3">

<div class="stat-card">

<div class="stat-icon text-warning">
<i class="bi bi-exclamation-triangle"></i>
</div>

<div class="stat-number">
<?php echo $low_stock; ?>
</div>

<div class="stat-label">
Low Stock Items
</div>

</div>

</div>


<div class="col-md-3">

<div class="stat-card">

<div class="stat-icon text-info">
<i class="bi bi-cart-check"></i>
</div>

<div class="stat-number">
<?php echo $order_count; ?>
</div>

<div class="stat-label">
My Orders
</div>

</div>

</div>

</div>


<!-- PRODUCTS -->

<div class="card p-4">

<div class="d-flex justify-content-between align-items-center mb-3">

<div>

<h5 class="mb-1">
My Products
</h5>

<small class="text-muted">
Products belonging to your vendor account
</small>

</div>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>Product</th>
<th>SKU</th>
<th>Price</th>
<th>Stock</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php if ($products->num_rows > 0): ?>

<?php while ($product = $products->fetch_assoc()): ?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($product['product_name']); ?>
</strong>

</td>

<td>

<span class="badge bg-light text-dark">
<?php echo htmlspecialchars($product['sku']); ?>
</span>

</td>

<td>

₹<?php echo number_format($product['price'], 2); ?>

</td>

<td>

<strong>
<?php echo $product['stock']; ?>
</strong>

</td>

<td>

<?php if ($product['stock'] == 0): ?>

<span class="stock-out">
Out of Stock
</span>

<?php elseif ($product['stock'] <= 10): ?>

<span class="stock-low">
Low Stock
</span>

<?php else: ?>

<span class="stock-good">
In Stock
</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="5"
class="text-center text-muted py-4">

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

</body>

</html>