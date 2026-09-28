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


/* GET INVENTORY */
$inventory_stmt = $conn->prepare(
    "SELECT
        p.product_name,
        p.sku,
        w.warehouse_name,
        w.location,
        i.quantity,
        i.updated_at
     FROM inventory i
     INNER JOIN products p
        ON i.product_id = p.id
     INNER JOIN warehouses w
        ON i.warehouse_id = w.id
     WHERE p.vendor_id = ?
     ORDER BY i.updated_at DESC"
);

$inventory_stmt->bind_param("i", $vendor_id);
$inventory_stmt->execute();

$inventory_result = $inventory_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Inventory | InventorySync</title>

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
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.stock-low {
    background: #fef3c7;
    color: #92400e;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.stock-out {
    background: #fee2e2;
    color: #991b1b;
    padding: 6px 12px;
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

<a href="products.php">
<i class="bi bi-box-seam"></i>
My Products
</a>

<a href="inventory.php" class="active">
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


<!-- MAIN CONTENT -->

<div class="col-md-10 main">

<div class="topbar d-flex justify-content-between align-items-center">

<div>

<h4 class="mb-1">
My Inventory
</h4>

<small class="text-muted">
Monitor stock across warehouses
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
Inventory Overview
</h5>

<small class="text-muted">
<?php echo htmlspecialchars($vendor['vendor_name']); ?>
</small>

</div>

<span class="badge bg-primary">

<?php echo $inventory_result->num_rows; ?>

Records

</span>

</div>


<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>#</th>

<th>Product</th>

<th>SKU</th>

<th>Warehouse</th>

<th>Location</th>

<th>Quantity</th>

<th>Status</th>

<th>Last Updated</th>

</tr>

</thead>


<tbody>

<?php if ($inventory_result->num_rows > 0): ?>

<?php $number = 1; ?>

<?php while ($item = $inventory_result->fetch_assoc()): ?>

<tr>

<td>
<?php echo $number++; ?>
</td>


<td>

<strong>
<?php echo htmlspecialchars($item['product_name']); ?>
</strong>

</td>


<td>

<span class="badge bg-light text-dark">

<?php echo htmlspecialchars($item['sku']); ?>

</span>

</td>


<td>

<i class="bi bi-building"></i>

<?php echo htmlspecialchars($item['warehouse_name']); ?>

</td>


<td>

<?php echo htmlspecialchars($item['location']); ?>

</td>


<td>

<strong>

<?php echo $item['quantity']; ?>

</strong>

</td>


<td>

<?php if ($item['quantity'] == 0): ?>

<span class="stock-out">
Out of Stock
</span>

<?php elseif ($item['quantity'] <= 10): ?>

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

<small class="text-muted">

<?php echo date(
    "d M Y, h:i A",
    strtotime($item['updated_at'])
); ?>

</small>

</td>

</tr>

<?php endwhile; ?>


<?php else: ?>

<tr>

<td colspan="8" class="text-center text-muted py-5">

<i class="bi bi-box-seam fs-1"></i>

<br><br>

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

</body>

</html>