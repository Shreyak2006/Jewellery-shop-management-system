<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$customer_id = $_SESSION['customer_id'] ?? null;

// Get statistics based on role
if($role == 'customer') {
    $total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE customer_id = '$customer_id'"))['count'] ?? 0;
    $total_spent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(p.amount) as total FROM payments p JOIN orders o ON p.order_id = o.order_id WHERE o.customer_id = '$customer_id'"))['total'] ?? 0;
    $recent_orders = mysqli_query($conn, "SELECT * FROM orders WHERE customer_id = '$customer_id' ORDER BY order_date DESC LIMIT 5");
} else {
    $total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM customers"))['count'] ?? 0;
    $total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'] ?? 0;
    $total_deliveries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM deliveries"))['count'] ?? 0;
    $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments"))['total'] ?? 0;
    $pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'"))['count'] ?? 0;
    $processing_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'"))['count'] ?? 0;
    $completed_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Completed'"))['count'] ?? 0;
    $recent_orders = mysqli_query($conn, "SELECT o.*, c.customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.customer_id ORDER BY o.order_date DESC LIMIT 5");
    $recent_customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY customer_id DESC LIMIT 5");
}

$current_date = date('F j, Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Goldsmith Management System</title>
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
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .welcome-text h2 { color: #333; font-size: 1.5rem; margin-bottom: 5px; }
        .welcome-text p { color: #666; }
        
        .date-time { text-align: right; }
        .date { color: #667eea; font-weight: 600; }
        .time { color: #666; margin-top: 5px; }
        
        .user-info { display: flex; align-items: center; gap: 15px; background: #f5f7fa; padding: 8px 15px; border-radius: 10px; }
        .user-role { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 12px; border-radius: 20px; }
        .logout-btn { background: #ff4757; color: white; padding: 8px 20px; border: none; border-radius: 8px; cursor: pointer; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; margin-top: 5px; }
        
        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .section-title { font-size: 1.2rem; color: #333; }
        .view-all { color: #667eea; text-decoration: none; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .badge-pending { background: #fff3e0; color: #ff9800; }
        .badge-processing { background: #e3f2fd; color: #2196f3; }
        .badge-completed { background: #e8f5e9; color: #4caf50; }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .empty-state { text-align: center; padding: 40px; color: #999; }
        
        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h3>✨ Goldsmith MS</h3></div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <?php if($role == 'customer'): ?>
                <a href="pages/my_orders.php">📦 My Orders</a>
            <?php else: ?>
                <a href="pages/customers.php">👥 Customers</a>
                <a href="pages/orders.php">📦 Orders</a>
                <a href="pages/deliveries.php">🚚 Deliveries</a>
                <a href="pages/payments.php">💰 Payments</a>
                <a href="reports.php">📈 Reports</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h2>
                <p><?php echo ($role == 'customer') ? 'Track your jewelry orders here' : 'Manage your business here'; ?></p>
            </div>
            <div class="date-time">
                <div class="date">📅 <?php echo $current_date; ?></div>
                <div class="time">🕐 <?php echo $current_time; ?></div>
            </div>
            <div class="user-info">
                <span class="user-role"><?php echo ($role == 'customer') ? '👤 Customer' : (($role == 'admin') ? '👑 Admin' : '👔 Staff'); ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </div>
        
        <?php if($role == 'customer'): ?>
            <div class="welcome-banner">
                <h3>Welcome, Valued Customer! 💎</h3>
                <p>Track your jewelry orders and status here</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">My Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">₹<?php echo number_format($total_spent, 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            
            <div class="recent-section">
                <div class="section-header">
                    <div class="section-title">📦 My Recent Orders</div>
                    <a href="pages/my_orders.php" class="view-all">View All →</a>
                </div>
                <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                    <table>
                        <thead><tr><th>Order ID</th><th>Ornament</th><th>Weight</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['ornament_name']); ?></td>
                                <td><?php echo $row['weight']; ?> g</td>
                                <td><?php echo date('d-m-Y', strtotime($row['order_date'])); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">No orders found</div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card" onclick="location.href='pages/customers.php'">
                    <div class="stat-number"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card" onclick="location.href='pages/orders.php'">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card" onclick="location.href='pages/deliveries.php'">
                    <div class="stat-number"><?php echo $total_deliveries; ?></div>
                    <div class="stat-label">Total Deliveries</div>
                </div>
                <div class="stat-card" onclick="location.href='pages/payments.php'">
                    <div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $processing_orders; ?></div>
                    <div class="stat-label">Processing Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $completed_orders; ?></div>
                    <div class="stat-label">Completed Orders</div>
                </div>
            </div>
            
            <div class="recent-section">
                <div class="section-header">
                    <div class="section-title">📋 Recent Orders</div>
                    <a href="pages/orders.php" class="view-all">View All →</a>
                </div>
                <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                    <table>
                        <thead><tr><th>Order ID</th><th>Customer</th><th>Ornament</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? $row['customer_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['ornament_name']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['order_date'])); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">No orders found</div>
                <?php endif; ?>
            </div>
            
            <div class="recent-section">
                <div class="section-header">
                    <div class="section-title">👥 Recent Customers</div>
                    <a href="pages/customers.php" class="view-all">View All →</a>
                </div>
                <?php if(mysqli_num_rows($recent_customers) > 0): ?>
                    <table>
                        <thead><tr><th>Customer ID</th><th>Name</th><th>Phone</th></tr></thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_customers)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">No customers found</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>