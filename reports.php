<?php
session_start();
require_once 'config.php';

// BLOCK CUSTOMERS - Redirect to dashboard
if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    header("Location: dashboard.php");
    exit();
}

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get report data
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM customers"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_deliveries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM deliveries"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments"))['total'] ?? 0;

// Orders by status
$status_data = [];
$status_result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while($row = mysqli_fetch_assoc($status_result)) {
    $status_data[$row['status']] = $row['count'];
}

// Deliveries by status
$delivery_status_data = [];
$delivery_result = mysqli_query($conn, "SELECT delivery_status, COUNT(*) as count FROM deliveries GROUP BY delivery_status");
while($row = mysqli_fetch_assoc($delivery_result)) {
    $delivery_status_data[$row['delivery_status']] = $row['count'];
}

// Top customers
$top_customers = mysqli_query($conn, "SELECT c.customer_name, COUNT(o.order_id) as order_count, SUM(p.amount) as total_spent FROM customers c LEFT JOIN orders o ON c.customer_id = o.customer_id LEFT JOIN payments p ON o.order_id = p.order_id GROUP BY c.customer_id ORDER BY total_spent DESC LIMIT 5");

// Recent orders
$recent_orders = mysqli_query($conn, "SELECT o.*, c.customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.customer_id ORDER BY o.order_date DESC LIMIT 10");

// Top products
$top_products = mysqli_query($conn, "SELECT ornament_name, COUNT(*) as order_count FROM orders WHERE ornament_name IS NOT NULL GROUP BY ornament_name ORDER BY order_count DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Goldsmith Management System</title>
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
        .print-btn { background: #667eea; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; margin-top: 5px; }
        .report-section { background: white; border-radius: 15px; padding: 20px; margin-bottom: 25px; }
        .section-title { font-size: 1.2rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; }
        .badge-pending { background: #fff3e0; color: #ff9800; }
        .badge-processing { background: #e3f2fd; color: #2196f3; }
        .badge-completed { background: #e8f5e9; color: #4caf50; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .main-content { margin-left: 0; }
            .two-columns { grid-template-columns: 1fr; }
        }
        @media print {
            .sidebar, .top-header, .print-btn, .logout-btn { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h3>✨ Goldsmith MS</h3></div>
        <div class="sidebar-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="pages/customers.php">👥 Customers</a>
            <a href="pages/orders.php">📦 Orders</a>
            <a href="pages/deliveries.php">🚚 Deliveries</a>
            <a href="pages/payments.php">💰 Payments</a>
            <a href="reports.php" class="active">📈 Reports</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h2>📈 Business Reports</h2>
            <div>
                <button class="print-btn" onclick="window.print()">🖨️ Print</button>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?php echo $total_customers; ?></div><div class="stat-label">Customers</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $total_orders; ?></div><div class="stat-label">Orders</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $total_deliveries; ?></div><div class="stat-label">Deliveries</div></div>
            <div class="stat-card"><div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div><div class="stat-label">Revenue</div></div>
        </div>
        
        <div class="two-columns">
            <div class="report-section">
                <div class="section-title">Orders by Status</div>
                <?php if(count($status_data) > 0): ?>
                    <table><thead><tr><th>Status</th><th>Count</th></tr></thead>
                    <tbody><?php foreach($status_data as $status => $count): ?>
                        <tr><td><span class="badge badge-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td><td><?php echo $count; ?></td></tr>
                    <?php endforeach; ?></tbody></table>
                <?php else: ?><div class="empty-state">No data</div><?php endif; ?>
            </div>
            
            <div class="report-section">
                <div class="section-title">Deliveries by Status</div>
                <?php if(count($delivery_status_data) > 0): ?>
                    <table><thead><tr><th>Status</th><th>Count</th></tr></thead>
                    <tbody><?php foreach($delivery_status_data as $status => $count): ?>
                        <tr><td><span class="badge badge-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td><td><?php echo $count; ?></td></tr>
                    <?php endforeach; ?></tbody></table>
                <?php else: ?><div class="empty-state">No data</div><?php endif; ?>
            </div>
        </div>
        
        <div class="two-columns">
            <div class="report-section">
                <div class="section-title">Top Customers</div>
                <?php if(mysqli_num_rows($top_customers) > 0): ?>
                    <table><thead><tr><th>Customer</th><th>Orders</th><th>Spent</th></tr></thead>
                    <tbody><?php while($row = mysqli_fetch_assoc($top_customers)): ?>
                        <tr><td><?php echo htmlspecialchars($row['customer_name']); ?></td><td><?php echo $row['order_count'] ?? 0; ?></td><td>₹<?php echo number_format($row['total_spent'] ?? 0, 2); ?></td></tr>
                    <?php endwhile; ?></tbody></table>
                <?php else: ?><div class="empty-state">No data</div><?php endif; ?>
            </div>
            
            <div class="report-section">
                <div class="section-title">Popular Ornaments</div>
                <?php if(mysqli_num_rows($top_products) > 0): ?>
                    <table><thead><tr><th>Ornament</th><th>Orders</th></tr></thead>
                    <tbody><?php while($row = mysqli_fetch_assoc($top_products)): ?>
                        <tr><td><?php echo htmlspecialchars($row['ornament_name']); ?></td><td><?php echo $row['order_count']; ?></td></tr>
                    <?php endwhile; ?></tbody></table>
                <?php else: ?><div class="empty-state">No data</div><?php endif; ?>
            </div>
        </div>
        
        <div class="report-section">
            <div class="section-title">Recent Orders</div>
            <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                <table><thead><tr><th>Order ID</th><th>Customer</th><th>Ornament</th><th>Date</th><th>Status</th></tr></thead>
                <tbody><?php while($row = mysqli_fetch_assoc($recent_orders)): ?>
                    <tr><td><?php echo htmlspecialchars($row['order_id']); ?></td><td><?php echo htmlspecialchars($row['customer_name'] ?? $row['customer_id']); ?></td><td><?php echo htmlspecialchars($row['ornament_name']); ?></td><td><?php echo date('d-m-Y', strtotime($row['order_date'])); ?></td><td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td></tr>
                <?php endwhile; ?></tbody></table>
            <?php else: ?><div class="empty-state">No orders found</div><?php endif; ?>
        </div>
    </div>
</body>
</html>
