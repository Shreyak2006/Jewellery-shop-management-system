<?php
session_start();
require_once '../config.php';

// BLOCK CUSTOMERS - Redirect to dashboard
if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    header("Location: ../dashboard.php");
    exit();
}

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
    
    $check_sql = "SELECT customer_id FROM customers WHERE customer_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $customer_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Customer ID '$customer_id' already exists!";
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
    $stmt = mysqli_prepare($conn, "DELETE FROM customers WHERE customer_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $customer_id);
    mysqli_stmt_execute($stmt) ? $success = "Customer deleted!" : $error = "Error deleting!";
}

// Handle Edit Customer
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_customer'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $stmt = mysqli_prepare($conn, "UPDATE customers SET customer_name=?, phone=?, address=? WHERE customer_id=?");
    mysqli_stmt_bind_param($stmt, "ssss", $customer_name, $phone, $address, $customer_id);
    mysqli_stmt_execute($stmt) ? $success = "Customer updated!" : $error = "Error updating!";
}

$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY customer_id ASC");

$next_id_result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(customer_id, 5) AS UNSIGNED)) as max_id FROM customers WHERE customer_id LIKE 'CUST%'");
$next_num = 1;
if($next_id_result && $row = mysqli_fetch_assoc($next_id_result)) {
    $next_num = ($row['max_id'] ?? 0) + 1;
}
$next_customer_id = "CUST" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
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
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        .logout-btn { background: #ff4757; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .card { background: white; border-radius: 15px; padding: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .edit-btn { background: #4caf50; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; }
        .delete-btn { background: #ff4757; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; }
        .alert { padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; width: 500px; margin: 50px auto; padding: 30px; border-radius: 15px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; }
        .close { font-size: 28px; cursor: pointer; color: #999; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 2px solid #e1e5e9; border-radius: 8px; }
        .submit-btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        @media (max-width: 768px) { .sidebar { left: -260px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h3>✨ Goldsmith MS</h3></div>
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
                <span>👋 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <button class="logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
        
        <button class="add-btn" onclick="openAddModal()">+ Add New Customer</button>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <?php if(mysqli_num_rows($customers) > 0): ?>
                <table>
                    <thead><tr><th>Customer ID</th><th>Customer Name</th><th>Phone</th><th>Address</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($customers)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td>
                                <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                                <button class="delete-btn" onclick="deleteCustomer('<?php echo $row['customer_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">No customers found</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Add Customer</h3><span class="close" onclick="closeAddModal()">&times;</span></div>
            <form method="POST">
                <div class="form-group"><label>Customer ID</label><input type="text" name="customer_id" value="<?php echo $next_customer_id; ?>" required></div>
                <div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" required></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" required></div>
                <div class="form-group"><label>Address</label><textarea name="address" rows="3"></textarea></div>
                <button type="submit" name="add_customer" class="submit-btn">Add Customer</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Edit Customer</h3><span class="close" onclick="closeEditModal()">&times;</span></div>
            <form method="POST">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" id="edit_customer_name" required></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" id="edit_phone" required></div>
                <div class="form-group"><label>Address</label><textarea name="address" id="edit_address" rows="3"></textarea></div>
                <button type="submit" name="edit_customer" class="submit-btn">Update Customer</button>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() { document.getElementById('addModal').style.display = 'block'; }
        function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
        function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
        function deleteCustomer(id) { if(confirm('Delete customer ' + id + '?')) window.location.href = '?delete=' + id; }
        function openEditModal(c) {
            document.getElementById('edit_customer_id').value = c.customer_id;
            document.getElementById('edit_customer_name').value = c.customer_name;
            document.getElementById('edit_phone').value = c.phone;
            document.getElementById('edit_address').value = c.address;
            document.getElementById('editModal').style.display = 'block';
        }
        window.onclick = function(e) {
            if(e.target == document.getElementById('addModal')) closeAddModal();
            if(e.target == document.getElementById('editModal')) closeEditModal();
        }
    </script>
</body>
</html>