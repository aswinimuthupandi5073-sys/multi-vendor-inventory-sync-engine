<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* SYNC ALL */
if (isset($_POST['sync_all'])) {

    $channels = $conn->query(
        "SELECT id, channel_name
         FROM sync_channels
         WHERE status = 'connected'"
    );

    $products = $conn->query(
        "SELECT id
         FROM products
         WHERE status = 'active'"
    );

    $count = 0;

    while ($channel = $channels->fetch_assoc()) {

        $products->data_seek(0);

        while ($product = $products->fetch_assoc()) {

            $product_id = $product['id'];
            $channel_id = $channel['id'];

            /* CENTRAL STOCK */
            $stock_stmt = $conn->prepare(
                "SELECT COALESCE(SUM(quantity), 0) AS total_stock
                 FROM inventory
                 WHERE product_id = ?"
            );

            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();

            $stock_result = $stock_stmt->get_result();
            $stock = $stock_result->fetch_assoc();
            $new_quantity = intval($stock['total_stock']);

            /* OLD CHANNEL STOCK */
            $old_stmt = $conn->prepare(
                "SELECT quantity
                 FROM channel_inventory
                 WHERE channel_id = ? AND product_id = ?"
            );

            $old_stmt->bind_param("ii", $channel_id, $product_id);
            $old_stmt->execute();

            $old_result = $old_stmt->get_result();

            if ($old_result->num_rows > 0) {

                $old_row = $old_result->fetch_assoc();
                $old_quantity = intval($old_row['quantity']);

            } else {

                $old_quantity = 0;
            }

            /* UPDATE CHANNEL STOCK */

            $sync_stmt = $conn->prepare(
                "INSERT INTO channel_inventory
                (channel_id, product_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                quantity = VALUES(quantity),
                last_synced_at = CURRENT_TIMESTAMP"
            );

            $sync_stmt->bind_param(
                "iii",
                $channel_id,
                $product_id,
                $new_quantity
            );

            $sync_stmt->execute();

           /* LOG */

if ($old_quantity != $new_quantity) {

    $status = "success";
    $message_text = "Stock synchronized successfully";

} else {

    $status = "success";
    $message_text = "Stock already synchronized";
}
            $log_stmt = $conn->prepare(
                "INSERT INTO sync_logs
                (product_id, channel_id, old_quantity,
                 new_quantity, status, message)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $log_stmt->bind_param(
                "iiiiss",
                $product_id,
                $channel_id,
                $old_quantity,
                $new_quantity,
                $status,
                $message_text
            );

            $log_stmt->execute();

            $count++;
        }
    }

    $message = $count . " inventory records synchronized successfully.";
    $message_type = "success";
}


/* MANUAL MISMATCH SIMULATION */
if (isset($_POST['simulate_mismatch'])) {

    $channel_id = intval($_POST['channel_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare(
        "INSERT INTO channel_inventory
        (channel_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        quantity = VALUES(quantity),
        last_synced_at = CURRENT_TIMESTAMP"
    );

    $stmt->bind_param(
        "iii",
        $channel_id,
        $product_id,
        $quantity
    );

    $stmt->execute();

    $message = "Channel stock changed. Mismatch can now be detected.";
    $message_type = "warning";
}


/* CHANNELS */
$channels = $conn->query(
    "SELECT id, channel_name, channel_type, status
     FROM sync_channels
     ORDER BY channel_name"
);


/* PRODUCTS */
$products = $conn->query(
    "SELECT id, product_name, sku
     FROM products
     WHERE status = 'active'
     ORDER BY product_name"
);


/* SYNC STATUS */
$status_result = $conn->query(
    "SELECT
        ci.id,
        ci.channel_id,
        ci.product_id,
        ci.quantity AS channel_quantity,
        ci.last_synced_at,
        sc.channel_name,
        p.product_name,
        p.sku,
        COALESCE(
            (
                SELECT SUM(i.quantity)
                FROM inventory i
                WHERE i.product_id = ci.product_id
            ), 0
        ) AS central_quantity
     FROM channel_inventory ci
     INNER JOIN sync_channels sc
        ON ci.channel_id = sc.id
     INNER JOIN products p
        ON ci.product_id = p.id
     ORDER BY ci.last_synced_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Sync Engine | InventorySync</title>

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

.sync-box {
    background: #eff6ff;
    border-radius: 15px;
    padding: 25px;
}

.sync-icon {
    font-size: 42px;
    color: #2563eb;
}

.badge-match {
    background: #dcfce7;
    color: #166534;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-mismatch {
    background: #fee2e2;
    color: #991b1b;
    padding: 6px 12px;
    border-radius: 20px;
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

<a href="orders.php">
<i class="bi bi-cart"></i>
Orders
</a>

<a href="sync_engine.php" class="active">
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


<!-- MAIN -->

<div class="col-md-10 main">

<div class="topbar d-flex justify-content-between align-items-center">

<div>

<h4 class="mb-1">
Inventory Sync Engine
</h4>

<small class="text-muted">
Synchronize central stock with connected channels
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


<!-- SYNC ACTION -->

<div class="card p-4 mb-4">

<div class="sync-box">

<div class="row align-items-center">

<div class="col-md-8">

<div class="sync-icon mb-2">
<i class="bi bi-arrow-repeat"></i>
</div>

<h4>
Central Inventory Synchronization
</h4>

<p class="text-muted mb-0">

Update stock quantities across all connected
e-commerce channels using the central inventory.

</p>

</div>

<div class="col-md-4 text-md-end">

<form method="POST">

<button
type="submit"
name="sync_all"
class="btn btn-primary btn-lg">

<i class="bi bi-arrow-repeat"></i>
Sync All Inventory

</button>

</form>

</div>

</div>

</div>

</div>


<!-- MISMATCH TEST -->

<div class="card p-4 mb-4">

<h5 class="mb-3">

<i class="bi bi-exclamation-triangle"></i>
Simulate Channel Stock Change

</h5>

<p class="text-muted">

Use this only for testing mismatch detection.

</p>

<form method="POST">

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">
Channel
</label>

<select
name="channel_id"
class="form-select"
required>

<option value="">
Select Channel
</option>

<?php while ($channel = $channels->fetch_assoc()): ?>

<option value="<?php echo $channel['id']; ?>">

<?php echo htmlspecialchars($channel['channel_name']); ?>

</option>

<?php endwhile; ?>

</select>

</div>


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

</option>

<?php endwhile; ?>

</select>

</div>


<div class="col-md-2 mb-3">

<label class="form-label">
Channel Stock
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
name="simulate_mismatch"
class="btn btn-warning w-100">

Test Mismatch

</button>

</div>

</div>

</form>

</div>


<!-- SYNC STATUS -->

<div class="card p-4">

<div class="d-flex justify-content-between mb-3">

<h5 class="mb-0">
Channel Sync Status
</h5>

<span class="text-muted">
Central vs Channel
</span>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>Channel</th>
<th>Product</th>
<th>SKU</th>
<th>Central Stock</th>
<th>Channel Stock</th>
<th>Status</th>
<th>Last Sync</th>

</tr>

</thead>

<tbody>

<?php if ($status_result->num_rows > 0): ?>

<?php while ($row = $status_result->fetch_assoc()): ?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($row['channel_name']); ?>
</strong>

</td>

<td>
<?php echo htmlspecialchars($row['product_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['sku']); ?>
</td>

<td>

<strong>
<?php echo $row['central_quantity']; ?>
</strong>

</td>

<td>

<strong>
<?php echo $row['channel_quantity']; ?>
</strong>

</td>

<td>

<?php if ($row['central_quantity'] == $row['channel_quantity']): ?>

<span class="badge-match">
<i class="bi bi-check-circle"></i>
Synced
</span>

<?php else: ?>

<span class="badge-mismatch">
<i class="bi bi-exclamation-circle"></i>
Mismatch
</span>

<?php endif; ?>

</td>

<td>

<small>
<?php echo $row['last_synced_at']; ?>
</small>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="7"
class="text-center text-muted py-4">

No synchronization records yet.

Click <strong>Sync All Inventory</strong> to start.

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