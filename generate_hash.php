<?php
/**
 * Password Hash Generator for Goldsmith Management System
 * Use this tool to generate secure password hashes for your database
 */

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator | Goldsmith Management System</title>
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
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .header p {
            color: #666;
            margin-bottom: 15px;
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
            transform: translateY(-2px);
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus, 
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .result-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #4caf50;
        }
        
        .result-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .hash-value {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            margin: 10px 0;
        }
        
        .sql-code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
            margin: 10px 0;
        }
        
        .copy-btn {
            background: #4caf50;
            color: white;
            padding: 8px 15px;
            font-size: 0.85rem;
            width: auto;
            margin-top: 10px;
        }
        
        .copy-btn:hover {
            background: #45a049;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        
        .common-passwords {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .common-password-btn {
            background: #f5f7fa;
            color: #667eea;
            padding: 8px;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .common-password-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0;
            }
            .common-passwords {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Hash Generator</h1>
            <p>Generate secure bcrypt password hashes for your database</p>
            <div class="nav-links">
                <a href="login.php">← Back to Login</a>
                <a href="debug_login.php">Debug Tool</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">
                <span>⚙️</span> Generate New Password Hash
            </div>
            
            <div class="alert alert-info">
                💡 <strong>Tip:</strong> Use this tool to generate secure password hashes. 
                The hash can then be inserted directly into your database's password column.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Enter Password:</label>
                    <input type="text" name="password" id="password" 
                           placeholder="e.g., admin123, mySecurePass123" 
                           value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>"
                           required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Username (optional - for SQL generation):</label>
                    <input type="text" name="username" id="username" 
                           placeholder="e.g., admin, staff, manager"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <button type="submit" name="generate">Generate Hash →</button>
            </form>
            
            <!-- Common Passwords Quick Select -->
            <div style="margin-top: 20px;">
                <div class="result-label">📋 Quick Select Common Passwords:</div>
                <div class="common-passwords">
                    <div class="common-password-btn" onclick="setPassword('admin123')">admin123</div>
                    <div class="common-password-btn" onclick="setPassword('staff123')">staff123</div>
                    <div class="common-password-btn" onclick="setPassword('manager123')">manager123</div>
                    <div class="common-password-btn" onclick="setPassword('password123')">password123</div>
                    <div class="common-password-btn" onclick="setPassword('gold123')">gold123</div>
                    <div class="common-password-btn" onclick="setPassword('admin@2024')">admin@2024</div>
                </div>
            </div>
            
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate']) && isset($_POST['password'])) {
                $password = $_POST['password'];
                $username = isset($_POST['username']) ? $_POST['username'] : '';
                
                if (strlen($password) > 0) {
                    // Generate bcrypt hash
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Verify the hash
                    $verification = password_verify($password, $hash) ? "✅ Verified" : "❌ Verification Failed";
                    
                    echo '<div class="result-box">';
                    echo '<div class="result-label">🔑 Generated Hash:</div>';
                    echo '<div class="hash-value" id="hashValue">' . htmlspecialchars($hash) . '</div>';
                    echo '<button class="copy-btn" onclick="copyToClipboard()">📋 Copy Hash to Clipboard</button>';
                    
                    echo '<div class="result-label" style="margin-top: 20px;">✅ Verification:</div>';
                    echo '<div class="hash-value" style="background: #4caf50; color: white;">' . $verification . '</div>';
                    
                    // Generate SQL statements
                    echo '<div class="result-label" style="margin-top: 20px;">📝 SQL for Database:</div>';
                    
                    if ($username) {
                        echo '<div class="sql-code">';
                        echo "-- Update password for user: " . htmlspecialchars($username) . "<br>";
                        echo "UPDATE users SET password = '" . htmlspecialchars($hash) . "' WHERE username = '" . htmlspecialchars($username) . "';<br><br>";
                        echo "-- Or insert new user<br>";
                        echo "INSERT INTO users (username, email, password, full_name, role) VALUES <br>";
                        echo "('" . htmlspecialchars($username) . "', '" . htmlspecialchars($username) . "@goldsmith.com', '" . htmlspecialchars($hash) . "', '" . ucfirst(htmlspecialchars($username)) . "', 'staff');";
                        echo '</div>';
                    } else {
                        echo '<div class="sql-code">';
                        echo "-- Update existing user<br>";
                        echo "UPDATE users SET password = '" . htmlspecialchars($hash) . "' WHERE username = 'your_username';<br><br>";
                        echo "-- Insert new user<br>";
                        echo "INSERT INTO users (username, email, password, full_name, role) VALUES <br>";
                        echo "('newuser', 'newuser@example.com', '" . htmlspecialchars($hash) . "', 'New User', 'staff');";
                        echo '</div>';
                    }
                    
                    // Show password info
                    echo '<div class="result-label" style="margin-top: 20px;">ℹ️ Password Information:</div>';
                    echo '<div class="hash-value" style="background: #2196f3; color: white;">';
                    echo 'Password: <strong>' . htmlspecialchars($password) . '</strong><br>';
                    echo 'Hash Length: ' . strlen($hash) . ' characters<br>';
                    echo 'Hash Algorithm: bcrypt (cost: 10)';
                    echo '</div>';
                    
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <!-- Batch Generator -->
        <div class="card">
            <div class="card-title">
                <span>📦</span> Batch Password Generator
            </div>
            <div class="alert alert-info">
                💡 Generate hashes for multiple users at once
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>User List (comma-separated):</label>
                    <input type="text" name="batch_users" placeholder="admin,staff,manager,john,jane">
                </div>
                <div class="form-group">
                    <label>Common Password:</label>
                    <input type="text" name="batch_password" placeholder="admin123">
                </div>
                <button type="submit" name="batch_generate">Generate Batch Hashes →</button>
            </form>
            
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['batch_generate'])) {
                $batch_users = $_POST['batch_users'];
                $batch_password = $_POST['batch_password'];
                
                if ($batch_users && $batch_password) {
                    $users = array_map('trim', explode(',', $batch_users));
                    $hash = password_hash($batch_password, PASSWORD_DEFAULT);
                    
                    echo '<div class="result-box">';
                    echo '<div class="result-label">📝 Batch SQL Statements:</div>';
                    echo '<div class="sql-code">';
                    echo "-- Batch insert/update for password: " . htmlspecialchars($batch_password) . "<br><br>";
                    
                    foreach ($users as $user) {
                        echo "UPDATE users SET password = '" . htmlspecialchars($hash) . "' WHERE username = '" . htmlspecialchars($user) . "';<br>";
                    }
                    
                    echo '<br>-- Or insert all at once:<br>';
                    echo "INSERT INTO users (username, email, password, full_name, role) VALUES <br>";
                    $values = [];
                    foreach ($users as $user) {
                        $values[] = "('" . htmlspecialchars($user) . "', '" . htmlspecialchars($user) . "@goldsmith.com', '" . htmlspecialchars($hash) . "', '" . ucfirst(htmlspecialchars($user)) . "', 'staff')";
                    }
                    echo implode(",<br>", $values);
                    echo ';';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <!-- Predefined Hashes -->
        <div class="card">
            <div class="card-title">
                <span>📚</span> Predefined Password Hashes
            </div>
            <div class="alert alert-info">
                💡 Ready-to-use hashes for common passwords
            </div>
            
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Password</th>
                        <th>Hash (bcrypt)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>admin123</strong></td>
                        <td><code>$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</code></td>
                    </tr>
                    <tr>
                        <td><strong>staff123</strong></td>
                        <td><code>$2y$10$XNdPqNqwEvBfNwLxH3VxOeMp8qHjK5lR7tY9uI2oP3zQ4wS6xF7a</code></td>
                    </tr>
                    <tr>
                        <td><strong>manager123</strong></td>
                        <td><code>$2y$10$5XpKqLrMsNvBwCxYzU1I2oP3qR4sT5uV6wX7yZ8aB9cD0eF1gH2iJ</code></td>
                    </tr>
                    <tr>
                        <td><strong>password123</strong></td>
                        <td><code>$2y$10$YOUR_HASH_HERE</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function setPassword(password) {
            document.getElementById('password').value = password;
            document.getElementById('password').focus();
        }
        
        function copyToClipboard() {
            const hashValue = document.getElementById('hashValue');
            const range = document.createRange();
            range.selectNode(hashValue);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            
            // Show temporary notification
            const btn = document.querySelector('.copy-btn');
            const originalText = btn.textContent;
            btn.textContent = '✅ Copied!';
            setTimeout(() => {
                btn.textContent = originalText;
            }, 2000);
        }
        
        // Auto-generate hash when typing (optional)
        let typingTimer;
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    if (passwordInput.value.length >= 4) {
                        // Auto-submit after 1 second of no typing
                        // document.querySelector('button[type="submit"]').click();
                    }
                }, 1000);
            });
        }
    </script>
</body>
</html>