<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

/* ADD CHANNEL */
if (isset($_POST['add_channel'])) {

    $vendor_id = intval($_POST['vendor_id']);
    $channel_name = trim($_POST['channel_name']);
    $channel_type = trim($_POST['channel_type']);

    if ($vendor_id > 0 && $channel_name != "") {

        $stmt = $conn->prepare(
            "INSERT INTO sync_channels
            (vendor_id, channel_name, channel_type, status)
            VALUES (?, ?, ?, 'connected')"
        );

        $stmt->bind_param(
            "iss",
            $vendor_id,
            $channel_name,
            $channel_type
        );

        if ($stmt->execute()) {
            $message = "Sync channel added successfully.";
        } else {
            $error = "Unable to add sync channel.";
        }

    } else {
        $error = "Please select a vendor and enter channel name.";
    }
}


/* DELETE CHANNEL */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare(
        "DELETE FROM sync_channels WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: channels.php");
    exit();
}


/* TOGGLE STATUS */
if (isset($_GET['toggle'])) {

    $id = intval($_GET['toggle']);

    $stmt = $conn->prepare(
        "UPDATE sync_channels
         SET status =
         CASE
            WHEN status = 'connected'
            THEN 'disconnected'
            ELSE 'connected'
         END
         WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: channels.php");
    exit();
}


/* GET VENDORS */
$vendors = $conn->query(
    "SELECT id, vendor_name
     FROM vendors
     WHERE status = 'active'
     ORDER BY vendor_name"
);


/* GET CHANNELS */
$channels = $conn->query(
    "SELECT
        c.id,
        c.channel_name,
        c.channel_type,
        c.status,
        c.created_at,
        v.vendor_name
     FROM sync_channels c
     LEFT JOIN vendors v
        ON c.vendor_id = v.id
     ORDER BY c.created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Sync Channels | InventorySync</title>

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

.connected {
    background: #dcfce7;
    color: #166534;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.disconnected {
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

<a href="vendors.php">

<i class="bi bi-shop"></i>
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

<a href="channels.php" class="active">

<i class="bi bi-diagram-3"></i>
Sync Channels

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


<!-- MAIN -->

<div class="col-md-10 main">

<div class="topbar d-flex justify-content-between align-items-center">

<div>

<h4 class="mb-1">
Sync Channels
</h4>

<small class="text-muted">
Manage connected inventory channels
</small>

</div>
<a href="dashboard.php" class="btn btn-outline-primary mb-3">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>

<span class="badge bg-primary">
Admin
</span>

</div>


<?php if ($message): ?>

<div class="alert alert-success">

<i class="bi bi-check-circle"></i>

<?php echo htmlspecialchars($message); ?>

</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-circle"></i>

<?php echo htmlspecialchars($error); ?>

</div>

<?php endif; ?>


<!-- ADD CHANNEL -->

<div class="card p-4 mb-4">

<h5 class="mb-3">

<i class="bi bi-plus-circle"></i>

Add Sync Channel

</h5>

<form method="POST">

<div class="row g-3">

<div class="col-md-4">

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

<?php echo htmlspecialchars(
    $vendor['vendor_name']
); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="col-md-4">

<label class="form-label">
Channel Name
</label>

<input
type="text"
name="channel_name"
class="form-control"
placeholder="Example: Amazon Store"
required>

</div>


<div class="col-md-3">

<label class="form-label">
Channel Type
</label>

<select
name="channel_type"
class="form-select">

<option value="Online Store">
Online Store
</option>

<option value="Marketplace">
Marketplace
</option>

<option value="E-Commerce">
E-Commerce
</option>

</select>

</div>


<div class="col-md-1 d-flex align-items-end">

<button
type="submit"
name="add_channel"
class="btn btn-primary w-100">

<i class="bi bi-plus-lg"></i>

</button>

</div>

</div>

</form>

</div>


<!-- CHANNEL LIST -->

<div class="card p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h5 class="mb-1">
Connected Channels
</h5>

<small class="text-muted">
Manage vendor synchronization channels
</small>

</div>

<span class="badge bg-primary">

<?php echo $channels->num_rows; ?>

Channels

</span>

</div>


<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>#</th>
<th>Channel</th>
<th>Type</th>
<th>Vendor</th>
<th>Status</th>
<th>Created</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php if ($channels->num_rows > 0): ?>

<?php $number = 1; ?>

<?php while ($channel = $channels->fetch_assoc()): ?>

<tr>

<td>
<?php echo $number++; ?>
</td>


<td>

<strong>

<?php echo htmlspecialchars(
    $channel['channel_name']
); ?>

</strong>

</td>


<td>

<span class="badge bg-light text-dark">

<?php echo htmlspecialchars(
    $channel['channel_type']
); ?>

</span>

</td>


<td>

<?php echo htmlspecialchars(
    $channel['vendor_name'] ?? '-'
); ?>

</td>


<td>

<?php if ($channel['status'] == 'connected'): ?>

<span class="connected">

<i class="bi bi-check-circle"></i>
Connected

</span>

<?php else: ?>

<span class="disconnected">

<i class="bi bi-x-circle"></i>
Disconnected

</span>

<?php endif; ?>

</td>


<td>

<small class="text-muted">

<?php echo date(
    "d M Y, h:i A",
    strtotime($channel['created_at'])
); ?>

</small>

</td>


<td>

<a
href="channels.php?toggle=<?php echo $channel['id']; ?>"
class="btn btn-sm btn-outline-primary">

<i class="bi bi-arrow-repeat"></i>

</a>


<a
href="channels.php?delete=<?php echo $channel['id']; ?>"
class="btn btn-sm btn-outline-danger"
onclick="return confirm('Delete this channel?');">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="7"
class="text-center text-muted py-5">

<i class="bi bi-diagram-3 fs-1"></i>

<br><br>

No sync channels found.

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