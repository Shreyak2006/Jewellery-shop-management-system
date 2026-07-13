<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE customer_id = '$customer_id' ORDER BY order_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .status { padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .Pending { background: #ff9800; color: white; }
        .Processing { background: #2196f3; color: white; }
        .Completed { background: #4caf50; color: white; }
        .logout { background: red; color: white; padding: 10px; text-decoration: none; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📦 My Order Status</h2>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
        
        <?php if(mysqli_num_rows($orders) > 0): ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Ornament</th>
                    <th>Weight</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['ornament_name']; ?></td>
                    <td><?php echo $row['weight']; ?> g</td>
                    <td><?php echo date('d-m-Y', strtotime($row['order_date'])); ?></td>
                    <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
        
        <br>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</body>
</html>