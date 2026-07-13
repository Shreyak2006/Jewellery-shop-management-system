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

// Get orders for dropdown
$orders_list = mysqli_query($conn, "SELECT order_id FROM orders ORDER BY order_date DESC");

// Handle Add Payment
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_payment'])) {
    $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $amount = floatval($_POST['amount']);
    
    $check_sql = "SELECT payment_id FROM payments WHERE payment_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $payment_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Payment ID '$payment_id' already exists!";
    } else {
        $sql = "INSERT INTO payments (payment_id, order_id, amount) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssd", $payment_id, $order_id, $amount);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Payment recorded successfully!";
        } else {
            $error = "Error adding payment: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Payment
if(isset($_GET['delete'])) {
    $payment_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM payments WHERE payment_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $payment_id);
    mysqli_stmt_execute($stmt) ? $success = "Payment deleted!" : $error = "Error deleting!";
}

// Handle Edit Payment
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_payment'])) {
    $payment_id = $_POST['payment_id'];
    $order_id = $_POST['order_id'];
    $amount = floatval($_POST['amount']);
    
    $stmt = mysqli_prepare($conn, "UPDATE payments SET order_id=?, amount=? WHERE payment_id=?");
    mysqli_stmt_bind_param($stmt, "ssd", $order_id, $amount, $payment_id);
    mysqli_stmt_execute($stmt) ? $success = "Payment updated!" : $error = "Error updating!";
}

// Get all payments with order details
$payments = mysqli_query($conn, "SELECT p.*, o.order_id as order_ref FROM payments p LEFT JOIN orders o ON p.order_id = o.order_id ORDER BY p.payment_id DESC");

// Calculate total payments
$total_payments = 0;
$payments_count = mysqli_num_rows($payments);
if($payments_count > 0) {
    while($row = mysqli_fetch_assoc($payments)) {
        $total_payments += $row['amount'];
    }
    mysqli_data_seek($payments, 0);
}

// Get next payment ID
$next_id_result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(payment_id, 4) AS UNSIGNED)) as max_id FROM payments WHERE payment_id LIKE 'PAY%'");
$next_num = 1;
if($next_id_result && $row = mysqli_fetch_assoc($next_id_result)) {
    $next_num = ($row['max_id'] ?? 0) + 1;
}
$next_payment_id = "PAY" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments | Goldsmith Management System</title>
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
        
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
            border-top: 2px solid #ddd;
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
            <a href="orders.php">📦 Orders</a>
            <a href="deliveries.php">🚚 Deliveries</a>
            <a href="payments.php" class="active">💰 Payments</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h2>💰 Payment Management</h2>
            <div class="user-info">
                <span>👋 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <button class="logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
        
        <div class="stats-bar">
            <div class="stats-grid">
                <div class="stat-badge">Total Records: <span><?php echo $payments_count; ?></span></div>
                <div class="stat-badge">Total Amount: <span>₹<?php echo number_format($total_payments, 2); ?></span></div>
            </div>
            <button class="add-btn" onclick="openAddModal()">+ Record Payment</button>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <?php if($payments_count > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Order ID</th>
                            <th>Amount (₹)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $running_total = 0;
                        while($row = mysqli_fetch_assoc($payments)): 
                            $running_total += $row['amount'];
                            $order_display = isset($row['order_ref']) ? $row['order_ref'] : $row['order_id'];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['payment_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order_display); ?></td>
                            <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                                <button class="delete-btn" onclick="deletePayment('<?php echo $row['payment_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong>₹<?php echo number_format($running_total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">No payments found. Click "Record Payment" to get started!</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Record Payment</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Payment ID</label>
                    <input type="text" name="payment_id" value="<?php echo $next_payment_id; ?>" required>
                </div>
                <div class="form-group">
                    <label>Order ID</label>
                    <select name="order_id" required>
                        <option value="">Select Order</option>
                        <?php 
                        mysqli_data_seek($orders_list, 0);
                        while($o = mysqli_fetch_assoc($orders_list)): ?>
                            <option value="<?php echo $o['order_id']; ?>"><?php echo $o['order_id']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" name="amount" step="0.01" required placeholder="Enter amount">
                </div>
                <button type="submit" name="add_payment" class="submit-btn">Record Payment</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Payment</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="payment_id" id="edit_payment_id">
                <div class="form-group">
                    <label>Order ID</label>
                    <input type="text" name="order_id" id="edit_order_id" readonly style="background:#f5f7fa;">
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" name="amount" id="edit_amount" step="0.01" required>
                </div>
                <button type="submit" name="edit_payment" class="submit-btn">Update Payment</button>
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
        
        function openEditModal(payment) {
            document.getElementById('edit_payment_id').value = payment.payment_id;
            document.getElementById('edit_order_id').value = payment.order_ref || payment.order_id;
            document.getElementById('edit_amount').value = payment.amount;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deletePayment(id) {
            if(confirm('Delete payment ' + id + '?')) {
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