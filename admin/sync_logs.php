<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

/* DELETE LOGS */
if (isset($_GET['clear'])) {

    $conn->query("DELETE FROM sync_logs");

    header("Location: sync_logs.php");
    exit();
}

/* STATISTICS */

$total_logs = $conn->query(
    "SELECT COUNT(*) AS total FROM sync_logs"
)->fetch_assoc()['total'];

$success_logs = $conn->query(
    "SELECT COUNT(*) AS total
     FROM sync_logs
     WHERE status = 'success'"
)->fetch_assoc()['total'];

$failed_logs = $conn->query(
    "SELECT COUNT(*) AS total
     FROM sync_logs
     WHERE status = 'failed'"
)->fetch_assoc()['total'];

$mismatch_logs = $conn->query(
    "SELECT COUNT(*) AS total
     FROM sync_logs
     WHERE status = 'mismatch'"
)->fetch_assoc()['total'];


/* LOG LIST */

$logs = $conn->query(
    "SELECT
        sync_logs.*,
        products.product_name,
        products.sku,
        sync_channels.channel_name
     FROM sync_logs
     LEFT JOIN products
        ON sync_logs.product_id = products.id
     LEFT JOIN sync_channels
        ON sync_logs.channel_id = sync_channels.id
     ORDER BY sync_logs.created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Sync Logs | InventorySync</title>

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

.stat-card {
    padding: 22px;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
}

.stat-label {
    color: #6b7280;
}

.log-success {
    background: #dcfce7;
    color: #166534;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.log-failed {
    background: #fee2e2;
    color: #991b1b;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.log-mismatch {
    background: #fef3c7;
    color: #92400e;
    padding: 6px 12px;
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

<a href="sync_engine.php">
<i class="bi bi-arrow-repeat"></i>
Sync Engine
</a>

<a href="sync_logs.php" class="active">
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
Synchronization Logs
</h4>

<small class="text-muted">
Monitor inventory synchronization activity
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


<!-- STATISTICS -->

<div class="row g-4 mb-4">

<div class="col-md-3">

<div class="card stat-card">

<div class="stat-number">
<?php echo $total_logs; ?>
</div>

<div class="stat-label">
Total Sync Records
</div>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card">

<div class="stat-number text-success">
<?php echo $success_logs; ?>
</div>

<div class="stat-label">
Successful Sync
</div>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card">

<div class="stat-number text-danger">
<?php echo $failed_logs; ?>
</div>

<div class="stat-label">
Failed Sync
</div>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card">

<div class="stat-number text-warning">
<?php echo $mismatch_logs; ?>
</div>

<div class="stat-label">
Mismatches
</div>

</div>

</div>

</div>


<!-- LOG TABLE -->

<div class="card p-4">

<div class="d-flex justify-content-between align-items-center mb-3">

<div>

<h5 class="mb-1">
Sync Activity
</h5>

<small class="text-muted">
Latest synchronization records
</small>

</div>

<?php if ($total_logs > 0): ?>

<a
href="sync_logs.php?clear=1"
class="btn btn-outline-danger btn-sm"
onclick="return confirm('Clear all synchronization logs?');">

<i class="bi bi-trash"></i>
Clear Logs

</a>

<?php endif; ?>

</div>


<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>#</th>
<th>Product</th>
<th>SKU</th>
<th>Channel</th>
<th>Old Stock</th>
<th>New Stock</th>
<th>Status</th>
<th>Message</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php if ($logs->num_rows > 0): ?>

<?php while ($log = $logs->fetch_assoc()): ?>

<tr>

<td>
<?php echo $log['id']; ?>
</td>

<td>

<strong>
<?php echo htmlspecialchars(
    $log['product_name'] ?? 'Deleted Product'
); ?>
</strong>

</td>

<td>

<?php echo htmlspecialchars(
    $log['sku'] ?? '-'
); ?>

</td>

<td>

<?php echo htmlspecialchars(
    $log['channel_name'] ?? 'Deleted Channel'
); ?>

</td>

<td>
<?php echo $log['old_quantity']; ?>
</td>

<td>
<?php echo $log['new_quantity']; ?>
</td>

<td>

<?php if ($log['status'] == 'success'): ?>

<span class="log-success">
<i class="bi bi-check-circle"></i>
Success
</span>

<?php elseif ($log['status'] == 'failed'): ?>

<span class="log-failed">
<i class="bi bi-x-circle"></i>
Failed
</span>

<?php else: ?>

<span class="log-mismatch">
<i class="bi bi-exclamation-triangle"></i>
Mismatch
</span>

<?php endif; ?>

</td>

<td>

<small>
<?php echo htmlspecialchars($log['message']); ?>
</small>

</td>

<td>

<small>
<?php echo $log['created_at']; ?>
</small>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="9"
class="text-center text-muted py-5">

<i class="bi bi-clock-history fs-2"></i>

<br><br>

No synchronization logs found.

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