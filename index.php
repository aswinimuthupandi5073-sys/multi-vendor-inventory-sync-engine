<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>InventorySync | Multi-Vendor Inventory</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f7f9fc;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #111827;
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }

        .hero {
            padding: 90px 20px;
            background: linear-gradient(135deg, #111827, #2563eb);
            color: white;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
        }

        .hero p {
            font-size: 18px;
            color: #dbeafe;
        }

        .btn-main {
            background: white;
            color: #1d4ed8;
            border: none;
            padding: 12px 25px;
            font-weight: bold;
            border-radius: 8px;
        }

        .feature-card {
            border: none;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            background: white;
        }

        .feature-icon {
            font-size: 35px;
            margin-bottom: 15px;
        }

        footer {
            background: #111827;
            color: #9ca3af;
            padding: 25px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            📦 InventorySync
        </a>

        <a href="login.php" class="btn btn-primary">
            Login
        </a>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-7">
                <span class="badge bg-light text-primary mb-3">
                    Multi-Vendor Inventory Platform
                </span>

                <h1>
                    Centralized Inventory
                    Synchronization Engine
                </h1>

                <p class="mt-3">
                    Manage products, vendors, warehouses and stock
                    from one centralized platform with real-time
                    synchronization monitoring.
                </p>

                <a href="login.php" class="btn btn-main mt-3">
                    Get Started →
                </a>
            </div>

            <div class="col-lg-5 text-center">
                <div class="display-1">
                    📦
                </div>

                <h4 class="mt-3">
                    Smart Inventory Control
                </h4>

                <p>
                    Track • Sync • Monitor
                </p>
            </div>

        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">

        <div class="text-center mb-5">
            <h2>Powerful Inventory Management</h2>
            <p class="text-muted">
                Everything you need to manage multi-vendor inventory.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">🏪</div>
                    <h5>Vendor Management</h5>
                    <p class="text-muted">
                        Manage multiple vendors and their products
                        from a centralized dashboard.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h5>Inventory Tracking</h5>
                    <p class="text-muted">
                        Monitor stock quantities across warehouses
                        and identify low-stock products.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h5>Inventory Sync</h5>
                    <p class="text-muted">
                        Synchronize stock information across
                        connected vendor channels.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">

        <div class="row text-center">

            <div class="col-md-3">
                <h2 class="fw-bold">Multi</h2>
                <p class="text-muted">Vendor Support</p>
            </div>

            <div class="col-md-3">
                <h2 class="fw-bold">24/7</h2>
                <p class="text-muted">Inventory Monitoring</p>
            </div>

            <div class="col-md-3">
                <h2 class="fw-bold">Smart</h2>
                <p class="text-muted">Sync Engine</p>
            </div>

            <div class="col-md-3">
                <h2 class="fw-bold">Secure</h2>
                <p class="text-muted">Role-Based Access</p>
            </div>

        </div>

    </div>
</section>

<footer>
    <div class="container text-center">
        <p class="mb-0">
            © 2026 InventorySync. Multi-Vendor E-Commerce Inventory Platform.
        </p>
    </div>
</footer>

</body>
</html>