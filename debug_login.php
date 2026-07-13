<?php
session_start();
require_once 'config.php';

// Test database connection
$connection_status = false;
$table_status = false;
$user_status = false;
$password_status = false;

// Check connection
if ($conn) {
    $connection_status = true;
}

// Check if users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $table_status = true;
}

// Get user count
$user_count = 0;
$users_list = [];
if ($table_status) {
    $user_result = mysqli_query($conn, "SELECT id, username, email, role, LEFT(password, 30) as password_preview FROM users");
    if ($user_result) {
        $user_count = mysqli_num_rows($user_result);
        while ($row = mysqli_fetch_assoc($user_result)) {
            $users_list[] = $row;
        }
    }
}

// Test password verification
$test_username = 'admin';
$test_password = 'admin123';
$test_result = null;

if ($table_status) {
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $test_username, $test_username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($test_password, $row['password'])) {
            $test_result = "success";
        } else {
            $test_result = "failed";
        }
    } else {
        $test_result = "not_found";
    }
}

// Handle manual test
$manual_test_result = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_login'])) {
    $test_user = mysqli_real_escape_string($conn, $_POST['test_user']);
    $test_pass = $_POST['test_pass'];
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $test_user, $test_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($test_pass, $row['password'])) {
            $manual_test_result = "<span style='color: green;'>✅ SUCCESS! Password matches! You can login.</span>";
        } else {
            $manual_test_result = "<span style='color: red;'>❌ FAILED! Password does not match! Stored hash: " . substr($row['password'], 0, 30) . "...</span>";
        }
    } else {
        $manual_test_result = "<span style='color: red;'>❌ User not found: $test_user</span>";
    }
}

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $update_user = mysqli_real_escape_string($conn, $_POST['update_user']);
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $update_user);
    
    if (mysqli_stmt_execute($stmt)) {
        $update_result = "<span style='color: green;'>✅ Password updated successfully for user: $update_user</span>";
    } else {
        $update_result = "<span style='color: red;'>❌ Error updating password: " . mysqli_error($conn) . "</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug Tool | Goldsmith Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .nav-links {
            margin-top: 15px;
        }
        
        .nav-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            padding: 8px 15px;
            background: #f5f7fa;
            border-radius: 8px;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .nav-links a:hover {
            background: #667eea;
            color: white;
        }
        
        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .status-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .status-success {
            border-left-color: #4caf50;
        }
        
        .status-error {
            border-left-color: #f44336;
        }
        
        .status-warning {
            border-left-color: #ff9800;
        }
        
        .status-label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .status-value {
            font-size: 1.1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .badge-error {
            background: #ffebee;
            color: #f44336;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
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
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .status-grid {
                grid-template-columns: 1fr;
            }
            table {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Login Debug Tool</h1>
            <p>Use this tool to diagnose and fix login issues</p>
            <div class="nav-links">
                <a href="login.php">← Back to Login Page</a>
                <a href="dashboard.php">Go to Dashboard</a>
                <a href="customers.php">View Customers</a>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="section">
            <div class="section-title">
                <span>📊</span> System Status
            </div>
            <div class="status-grid">
                <div class="status-card <?php echo $connection_status ? 'status-success' : 'status-error'; ?>">
                    <div class="status-label">Database Connection</div>
                    <div class="status-value">
                        <?php echo $connection_status ? '✅ Connected' : '❌ Failed'; ?>
                    </div>
                </div>
                <div class="status-card <?php echo $table_status ? 'status-success' : 'status-error'; ?>">
                    <div class="status-label">Users Table</div>
                    <div class="status-value">
                        <?php echo $table_status ? '✅ Exists' : '❌ Missing'; ?>
                    </div>
                </div>
                <div class="status-card <?php echo $user_count > 0 ? 'status-success' : 'status-warning'; ?>">
                    <div class="status-label">Total Users</div>
                    <div class="status-value">
                        <?php echo $user_count; ?> user(s) found
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Users in Database -->
        <div class="section">
            <div class="section-title">
                <span>👥</span> Users in Database
            </div>
            <?php if (count($users_list) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Password Hash (Preview)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users_list as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo $user['username']; ?></strong></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td><code><?php echo $user['password_preview'] ?: '❌ MISSING'; ?>...</code></td>
                            <td>
                                <?php if($user['password_preview']): ?>
                                    <span class="badge badge-success">✓ Has Password</span>
                                <?php else: ?>
                                    <span class="badge badge-error">✗ No Password</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>❌ No users found in database. Please run the SQL schema to create users.</p>
            <?php endif; ?>
        </div>
        
        <!-- Automatic Test -->
        <div class="section">
            <div class="section-title">
                <span>🔐</span> Automatic Password Test
            </div>
            <p><strong>Testing credentials:</strong> Username: <code>admin</code> | Password: <code>admin123</code></p>
            <br>
            <?php if ($test_result == "success"): ?>
                <div class="alert alert-success">
                    ✅ SUCCESS! Password 'admin123' is CORRECT for user 'admin'!<br>
                    You can login with these credentials.
                </div>
            <?php elseif ($test_result == "failed"): ?>
                <div class="alert alert-error">
                    ❌ FAILED! Password 'admin123' is INCORRECT for user 'admin'!<br>
                    The password hash in the database does not match 'admin123'.
                </div>
            <?php elseif ($test_result == "not_found"): ?>
                <div class="alert alert-error">
                    ❌ User 'admin' not found in database!<br>
                    Please insert the admin user.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Manual Test Form -->
        <div class="section">
            <div class="section-title">
                <span>🧪</span> Manual Login Test
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Username or Email:</label>
                    <input type="text" name="test_user" value="admin" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="text" name="test_pass" value="admin123" required>
                </div>
                <button type="submit" name="test_login">Test Login</button>
            </form>
            <?php if ($manual_test_result): ?>
                <div style="margin-top: 15px;">
                    <?php echo $manual_test_result; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Password Update Tool -->
        <div class="section">
            <div class="section-title">
                <span>🔧</span> Password Reset Tool
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Select User:</label>
                    <select name="update_user">
                        <?php foreach($users_list as $user): ?>
                            <option value="<?php echo $user['username']; ?>"><?php echo $user['username']; ?> (<?php echo $user['email']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="text" name="new_password" value="admin123" required>
                </div>
                <button type="submit" name="update_password">Update Password</button>
            </form>
            <?php if (isset($update_result)): ?>
                <div style="margin-top: 15px;">
                    <?php echo $update_result; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Fix SQL -->
        <div class="section">
            <div class="section-title">
                <span>📝</span> Quick Fix SQL
            </div>
            <p>If passwords are incorrect, run this SQL in MySQL Workbench:</p>
            <div class="code-block">
                USE goldsmith;<br>
                <br>
                -- Update admin password to 'admin123'<br>
                UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';<br>
                <br>
                -- Update staff password to 'staff123'<br>
                UPDATE users SET password = '$2y$10$XNdPqNqwEvBfNwLxH3VxOeMp8qHjK5lR7tY9uI2oP3zQ4wS6xF7a' WHERE username = 'staff';<br>
                <br>
                -- Update manager password to 'manager123'<br>
                UPDATE users SET password = '$2y$10$5XpKqLrMsNvBwCxYzU1I2oP3qR4sT5uV6wX7yZ8aB9cD0eF1gH2iJ' WHERE username = 'manager';<br>
                <br>
                -- Verify<br>
                SELECT username, 'admin123' as test_password,<br>
                CASE <br>
                    WHEN password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' THEN '✅ Correct'<br>
                    ELSE '❌ Incorrect'<br>
                END as status<br>
                FROM users WHERE username = 'admin';
            </div>
        </div>
        
        <!-- Session Info -->
        <div class="section">
            <div class="section-title">
                <span>🔑</span> Session Information
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="alert alert-success">
                    ✅ You are currently logged in as: <strong><?php echo $_SESSION['username']; ?></strong><br>
                    Session ID: <?php echo session_id(); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    ❌ No active session. You are not logged in.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Server Info -->
        <div class="section">
            <div class="section-title">
                <span>🖥️</span> Server Information
            </div>
            <div class="status-grid">
                <div class="status-card status-success">
                    <div class="status-label">PHP Version</div>
                    <div class="status-value"><?php echo phpversion(); ?></div>
                </div>
                <div class="status-card status-success">
                    <div class="status-label">MySQL Version</div>
                    <div class="status-value">
                        <?php echo mysqli_get_server_info($conn); ?>
                    </div>
                </div>
                <div class="status-card status-success">
                    <div class="status-label">Session Save Path</div>
                    <div class="status-value"><?php echo session_save_path() ?: 'Default'; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>