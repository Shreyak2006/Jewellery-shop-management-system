<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle Add Customer
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if customer_id already exists
    $check_sql = "SELECT customer_id FROM customers WHERE customer_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $customer_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Customer ID '$customer_id' already exists! Please use a different ID.";
    } else {
        $sql = "INSERT INTO customers (customer_id, customer_name, phone, address) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $customer_id, $customer_name, $phone, $address);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Customer added successfully!";
        } else {
            $error = "Error adding customer: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Customer
if(isset($_GET['delete'])) {
    $customer_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Check if customer has orders before deleting
    $check_orders = mysqli_query($conn, "SELECT order_id FROM orders WHERE customer_id = '$customer_id' LIMIT 1");
    if(mysqli_num_rows($check_orders) > 0) {
        $error = "Cannot delete customer! This customer has existing orders.";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM customers WHERE customer_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $customer_id);
        mysqli_stmt_execute($stmt) ? $success = "Customer deleted successfully!" : $error = "Error deleting customer!";
    }
}

// Handle Edit Customer
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_customer'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $stmt = mysqli_prepare($conn, "UPDATE customers SET customer_name=?, phone=?, address=? WHERE customer_id=?");
    mysqli_stmt_bind_param($stmt, "ssss", $customer_name, $phone, $address, $customer_id);
    mysqli_stmt_execute($stmt) ? $success = "Customer updated successfully!" : $error = "Error updating customer!";
}

// Get all customers
$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY customer_id ASC");

// Get next customer ID
$next_id_result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(customer_id, 5) AS UNSIGNED)) as max_id FROM customers WHERE customer_id LIKE 'CUST%'");
$next_num = 1;
if($next_id_result && $row = mysqli_fetch_assoc($next_id_result)) {
    $next_num = ($row['max_id'] ?? 0) + 1;
}
$next_customer_id = "CUST" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

// Get statistics
$total_customers = mysqli_num_rows($customers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | Goldsmith Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { font-size: 1.5rem; margin-bottom: 5px; }
        .sidebar-header p { font-size: 0.85rem; opacity: 0.8; }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            gap: 12px;
            transition: 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); padding-left: 30px; }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        /* Header */
        .top-header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .top-header h2 { color: #333; font-size: 1.5rem; }
        
        .user-info { display: flex; align-items: center; gap: 15px; }
        
        .logout-btn {
            background: #ff4757;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .logout-btn:hover { background: #ff3838; transform: translateY(-2px); }
        
        /* Stats Bar */
        .stats-bar {
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .total-count {
            font-size: 1rem;
            color: #666;
        }
        
        .total-count span {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        
        /* Card and Table */
        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e5e9;
        }
        
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Buttons */
        .edit-btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
            transition: 0.3s;
        }
        .edit-btn:hover { background: #45a049; transform: scale(1.05); }
        
        .delete-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .delete-btn:hover { background: #ff3838; transform: scale(1.05); }
        
        /* Alerts */
        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #4caf50; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #f44336; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: white;
            width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .modal-header h3 { color: #333; }
        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: 0.3s;
        }
        .close:hover { color: #333; }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #999;
        }
        .empty-state p { margin-bottom: 10px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 20px; }
        
        .suggested-id {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        
        /* Search box */
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-box input {
            padding: 8px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 20px;
            width: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .main-content { margin-left: 0; }
            .modal-content { width: 90%; margin: 20px auto; }
            .stats-bar { flex-direction: column; align-items: flex-start; }
            .search-box { width: 100%; }
            .search-box input { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>✨ Goldsmith MS</h3>
            <p>Management System</p>
        </div>
        <div class="sidebar-menu">
            <a href="../dashboard.php">📊 Dashboard</a>
            <a href="customers.php" class="active">👥 Customers</a>
            <a href="orders.php">📦 Orders</a>
            <a href="deliveries.php">🚚 Deliveries</a>
            <a href="payments.php">💰 Payments</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h2>👥 Customer Management</h2>
            <div class="user-info">
                <?php
                $role_display = '';
                if($_SESSION['role'] == 'goldsmith') $role_display = '👑 Master Goldsmith';
                elseif($_SESSION['role'] == 'manager') $role_display = '📋 Store Manager';
                elseif($_SESSION['role'] == 'employee') $role_display = '👔 Store Employee';
                else $role_display = ucfirst($_SESSION['role']);
                ?>
                <span><?php echo $role_display; ?></span>
                <span>👋 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <button class="logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
        
        <div class="stats-bar">
            <div class="total-count">
                📊 Total Customers: <span><?php echo $total_customers; ?></span>
            </div>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search customers..." onkeyup="searchCustomers()">
            </div>
            <button class="add-btn" onclick="openAddModal()">+ Add New Customer</button>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <?php if(mysqli_num_rows($customers) > 0): ?>
                <table id="customerTable">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($customers)): ?>
                            <tr class="customer-row">
                                <td><strong><?php echo htmlspecialchars($row['customer_id']); ?></strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</strong>?</