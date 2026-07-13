<?php

session_start();
require_once '../config.php';
// BLOCK CUSTOMERS - Redirect to dashboard
if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    header("Location: ../dashboard.php");
    exit();
}

// BLOCK CUSTOMERS - Redirect to dashboard
if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    header("Location: ../dashboard.php");
    exit();
}

// Rest of your existing code below...

session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get customers for dropdown
$customers_list = mysqli_query($conn, "SELECT customer_id, customer_name FROM customers ORDER BY customer_name");

// Handle Add Order
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $goldsmith_id = mysqli_real_escape_string($conn, $_POST['goldsmith_id']);
    $ornament_name = mysqli_real_escape_string($conn, $_POST['ornament_name']);
    $order_date = mysqli_real_escape_string($conn, $_POST['order_date']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $check_sql = "SELECT order_id FROM orders WHERE order_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $order_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Order ID '$order_id' already exists!";
    } else {
        $sql = "INSERT INTO orders (order_id, customer_id, goldsmith_id, ornament_name, order_date, weight, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $order_id, $customer_id, $goldsmith_id, $ornament_name, $order_date, $weight, $status);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Order added successfully!";
        } else {
            $error = "Error adding order: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Order
if(isset($_GET['delete'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt) ? $success = "Order deleted!" : $error = "Error deleting!";
}

// Handle Edit Order
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_order'])) {
    $order_id = $_POST['order_id'];
    $customer_id = $_POST['customer_id'];
    $goldsmith_id = $_POST['goldsmith_id'];
    $ornament_name = $_POST['ornament_name'];
    $order_date = $_POST['order_date'];
    $weight = $_POST['weight'];
    $status = $_POST['status'];
    
    $stmt = mysqli_prepare($conn, "UPDATE orders SET customer_id=?, goldsmith_id=?, ornament_name=?, order_date=?, weight=?, status=? WHERE order_id=?");
    mysqli_stmt_bind_param($stmt, "sssssss", $customer_id, $goldsmith_id, $ornament_name, $order_date, $weight, $status, $order_id);
    mysqli_stmt_execute($stmt) ? $success = "Order updated!" : $error = "Error updating!";
}

// Get all orders with customer names
$orders = mysqli_query($conn, "SELECT o.*, c.customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.customer_id ORDER BY o.order_date DESC");

// Get next order ID
$next_id_result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(order_id, 4) AS UNSIGNED)) as max_id FROM orders WHERE order_id LIKE 'ORD%'");
$next_num = 1;
if($next_id_result && $row = mysqli_fetch_assoc($next_id_result)) {
    $next_num = ($row['max_id'] ?? 0) + 1;
}
$next_order_id = "ORD" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

// Get statistics
$total_orders = mysqli_num_rows($orders);
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'"))['count'] ?? 0;
$processing_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'"))['count'] ?? 0;
$completed_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Completed'"))['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Goldsmith Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 100;
        }
        
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { font-size: 1.5rem; }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            gap: 12px;
            transition: 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); }
        
        .main-content { margin-left: 260px; padding: 20px; }
        
        .top-header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info { display: flex; align-items: center; gap: 15px; }
        
        .logout-btn {
            background: #ff4757;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .stats-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stats-grid { display: flex; gap: 20px; flex-wrap: wrap; }
        
        .stat-badge {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .stat-badge span { font-weight: bold; color: #667eea; }
        
        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            overflow-x: auto;
        }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        
        .edit-btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .delete-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .badge-pending { background: #fff3e0; color: #ff9800; }
        .badge-processing { background: #e3f2fd; color: #2196f3; }
        .badge-completed { background: #e8f5e9; color: #4caf50; }
        
        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .main-content { margin-left: 0; }
            .modal-content { width: 90%; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>✨ Goldsmith MS</h3>
        </div>
        <div class="sidebar-menu">
            <a href="../dashboard.php">📊 Dashboard</a>
            <a href="customers.php">👥 Customers</a>
            <a href="orders.php" class="active">📦 Orders</a>
            <a href="deliveries.php">🚚 Deliveries</a>
            <a href="payments.php">💰 Payments</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h2>📦 Order Management</h2>
            <div class="user-info">
                <span>👋 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <button class="logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
        
        <div class="stats-bar">
            <div class="stats-grid">
                <div class="stat-badge">Total: <span><?php echo $total_orders; ?></span></div>
                <div class="stat-badge">Pending: <span><?php echo $pending_orders; ?></span></div>
                <div class="stat-badge">Processing: <span><?php echo $processing_orders; ?></span></div>
                <div class="stat-badge">Completed: <span><?php echo $completed_orders; ?></span></div>
            </div>
            <button class="add-btn" onclick="openAddModal()">+ Add New Order</button>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <?php if(mysqli_num_rows($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Goldsmith ID</th>
                            <th>Ornament</th>
                            <th>Weight (g)</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['order_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['customer_name'] ?? $row['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['goldsmith_id'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['ornament_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['weight']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['order_date'])); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if($row['status'] == 'Pending') $status_class = 'badge-pending';
                                elseif($row['status'] == 'Processing') $status_class = 'badge-processing';
                                else $status_class = 'badge-completed';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                            </td>
                            <td>
                                <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                                <button class="delete-btn" onclick="deleteOrder('<?php echo $row['order_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">No orders found. Click "Add New Order" to get started!</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Order</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Order ID</label>
                    <input type="text" name="order_id" value="<?php echo $next_order_id; ?>" required>
                </div>
                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php 
                        mysqli_data_seek($customers_list, 0);
                        while($c = mysqli_fetch_assoc($customers_list)): ?>
                            <option value="<?php echo $c['customer_id']; ?>"><?php echo $c['customer_id'] . ' - ' . $c['customer_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goldsmith ID</label>
                    <input type="text" name="goldsmith_id" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Ornament Name</label>
                    <input type="text" name="ornament_name" required>
                </div>
                <div class="form-group">
                    <label>Weight (grams)</label>
                    <input type="text" name="weight" required placeholder="e.g., 10.5">
                </div>
                <div class="form-group">
                    <label>Order Date</label>
                    <input type="date" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <button type="submit" name="add_order" class="submit-btn">Add Order</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Order</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="edit_order_id">
                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" id="edit_customer_id" required>
                        <?php 
                        $customers_list2 = mysqli_query($conn, "SELECT customer_id, customer_name FROM customers ORDER BY customer_name");
                        while($c = mysqli_fetch_assoc($customers_list2)): ?>
                            <option value="<?php echo $c['customer_id']; ?>"><?php echo $c['customer_id'] . ' - ' . $c['customer_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goldsmith ID</label>
                    <input type="text" name="goldsmith_id" id="edit_goldsmith_id">
                </div>
                <div class="form-group">
                    <label>Ornament Name</label>
                    <input type="text" name="ornament_name" id="edit_ornament_name" required>
                </div>
                <div class="form-group">
                    <label>Weight (grams)</label>
                    <input type="text" name="weight" id="edit_weight" required>
                </div>
                <div class="form-group">
                    <label>Order Date</label>
                    <input type="date" name="order_date" id="edit_order_date" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <button type="submit" name="edit_order" class="submit-btn">Update Order</button>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(order) {
            document.getElementById('edit_order_id').value = order.order_id;
            document.getElementById('edit_customer_id').value = order.customer_id;
            document.getElementById('edit_goldsmith_id').value = order.goldsmith_id || '';
            document.getElementById('edit_ornament_name').value = order.ornament_name;
            document.getElementById('edit_weight').value = order.weight;
            document.getElementById('edit_order_date').value = order.order_date;
            document.getElementById('edit_status').value = order.status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteOrder(id) {
            if(confirm('Delete order ' + id + '?')) {
                window.location.href = '?delete=' + id;
            }
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('addModal')) closeAddModal();
            if (event.target == document.getElementById('editModal')) closeEditModal();
        }
        
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(a) { a.style.display = 'none'; });
        }, 3000);
    </script>
</body>
</html>