# Multi Vendor Inventory Sync Engine

A PHP and MySQL based web application designed to centralize product, vendor, warehouse, inventory, order, and synchronization management for a multi-vendor e-commerce environment.

## 📌 Project Overview

The **Multi Vendor Inventory Sync Engine** helps manage inventory from a centralized system and synchronize stock quantities across connected e-commerce channels.

The system provides separate access for **Admin** and **Vendor** users.

## ✨ Features

### 👨‍💼 Admin

* Admin Dashboard
* Vendor Management
* Product Management
* Warehouse Management
* Inventory Management
* Order Management
* Sync Channel Management
* Inventory Synchronization Engine
* Sync Logs
* Stock Mismatch Detection
* Direct navigation between management pages

### 🏪 Vendor

* Vendor Dashboard
* View Own Products
* View Own Inventory
* View Own Orders
* Monitor Inventory Synchronization
* View Synced and Mismatched Stock

### 🔄 Inventory Synchronization

* Central inventory tracking
* Multi-channel stock synchronization
* Channel inventory records
* Stock mismatch detection
* Synchronization logs
* Manual channel stock change simulation

## 🛠️ Technologies Used

* PHP
* MySQL
* HTML5
* CSS3
* JavaScript
* Bootstrap 5
* Bootstrap Icons
* XAMPP
* phpMyAdmin

## 📂 Project Structure

```text
multi_vendor_inventory/
│
├── index.php
├── login.php
├── logout.php
├── database.sql
│
├── config/
│   └── db.php
│
├── admin/
│   ├── dashboard.php
│   ├── vendors.php
│   ├── products.php
│   ├── warehouses.php
│   ├── inventory.php
│   ├── orders.php
│   ├── channels.php
│   ├── sync_engine.php
│   └── sync_logs.php
│
├── vendor/
│   ├── dashboard.php
│   ├── products.php
│   ├── inventory.php
│   ├── orders.php
│   └── sync_status.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
└── includes/
    ├── header.php
    ├── sidebar.php
    └── footer.php
```

## ⚙️ Installation & Setup

### 1. Install XAMPP

Install XAMPP and start:

* Apache
* MySQL

### 2. Copy Project

Copy the project folder into:

```text
C:\xampp\htdocs\
```

The final path should be:

```text
C:\xampp\htdocs\multi_vendor_inventory
```

### 3. Create Database

Open phpMyAdmin and create a database named:

```text
multi_vendor_inventory
```

### 4. Import Database

Open the `database.sql` file from this project and import it into the `multi_vendor_inventory` database.

The SQL file contains the required tables and demo data.

### 5. Check Database Configuration

Open:

```text
config/db.php
```

Default XAMPP configuration:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "multi_vendor_inventory";
```

Update these values if your MySQL configuration is different.

### 6. Run the Project

Open your browser and visit:

```text
http://localhost/multi_vendor_inventory/
```

## 🔐 Demo Login Credentials

### Admin

```text
Email: admin@example.com
Password: admin123
```

### Vendor

```text
Email: vendor@example.com
Password: vendor123
```

## 🧪 Testing Flow

### Admin Testing

1. Login as Admin
2. Add or manage vendors
3. Add products
4. Manage warehouses
5. Update inventory
6. Create an order
7. Verify stock reduction
8. Manage sync channels
9. Run Sync Engine
10. Check Sync Logs
11. Check stock mismatch status

### Vendor Testing

1. Login as Vendor
2. View products
3. View inventory
4. View orders
5. Open Sync Status
6. Verify synchronized and mismatched quantities

## 📊 Database Modules

The application includes database tables for:

* Users
* Vendors
* Products
* Warehouses
* Inventory
* Orders
* Inventory Transactions
* Sync Channels
* Channel Inventory
* Sync Logs

## 🔄 Example Synchronization

The system compares:

```text
Central Inventory
       ↓
Sync Engine
       ↓
Connected Channels
       ↓
Channel Inventory
```

If the central stock and channel stock are different, the system displays a **Mismatch** status.

## 🎯 Project Objective

The main objective of this project is to reduce manual inventory updates, minimize stock inconsistencies, and provide centralized inventory control for multi-vendor e-commerce operations.

## 👩‍💻 Developed By

**Aswini**

B.Tech Artificial Intelligence & Data Science

## 📌 Note

This project is developed as an academic/demo application using XAMPP, PHP, and MySQL.
