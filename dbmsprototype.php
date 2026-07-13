<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Get user from database
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            
            // For customers, get their customer_id
            if($row['role'] == 'customer') {
                $cust_query = mysqli_query($conn, "SELECT customer_id FROM customers WHERE customer_name = '{$row['full_name']}' LIMIT 1");
                if($cust = mysqli_fetch_assoc($cust_query)) {
                    $_SESSION['customer_id'] = $cust['customer_id'];
                }
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password! Please try again.";
        }
    } else {
        $error = "User not found! Please check your username or email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Goldsmith Management System</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background shapes */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            left: -150px;
            animation: float 20s infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
            animation: float 25s infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 30px) rotate(180deg); }
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .login-wrapper {
            display: flex;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .login-wrapper:hover {
            transform: translateY(-5px);
        }

        .info-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            margin-bottom: 40px;
        }

        .logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .logo p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .info-content h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .info-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .features {
            list-style: none;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features li::before {
            content: '✓';
            display: inline-block;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
        }

        .login-section {
            flex: 1;
            padding: 50px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            color: #667eea;
            font-style: normal;
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .additional-links {
            margin-top: 25px;
            text-align: center;
        }

        .additional-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .additional-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .demo-credentials {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }

        .demo-credentials p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 8px;
        }

        .demo-credentials .cred {
            font-family: monospace;
            font-size: 0.8rem;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            .info-section {
                padding: 30px;
                text-align: center;
            }
            .features li {
                justify-content: center;
            }
            .login-section {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-wrapper">
            <div class="info-section">
                <div class="logo">
                    <h1>✨ Goldsmith MS</h1>
                    <p>Premium Jewelry Management System</p>
                </div>
                <div class="info-content">
                    <h2>Welcome Back!</h2>
                    <p>Manage your jewelry business efficiently with our comprehensive management system.</p>
                    <ul class="features">
                        <li>Customer Management</li>
                        <li>Order Processing</li>
                        <li>Inventory Tracking</li>
                        <li>Sales Analytics</li>
                        <li>Real-time Reports</li>
                    </ul>
                </div>
            </div>
            <div class="login-section">
                <div class="login-header">
                    <h3>Sign In</h3>
                    <p>Enter your credentials to access dashboard</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <div class="input-group">
                            <i>👤</i>
                            <input type="text" name="username" class="form-control" required placeholder="Enter username or email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <i>🔒</i>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Enter password">
                            <button type="button" class="toggle-password" id="togglePassword">👁️</button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">Login →</button>
                    
                    <div class="additional-links">
                        <a href="#">Forgot Password?</a>
                        <span style="margin: 0 10px;">|</span>
                        <a href="register_customer.php">Register as Customer</a>
                    </div>
                </form>
                
                <div class="demo-credentials">
                    <p><strong>📝 Demo Credentials:</strong></p>
                    <p class="cred">👑 Admin: username: admin | password: admin123</p>
                    <p class="cred">👔 Employee: username: staff | password: admin123</p>
                    <p class="cred">👤 Customer: username: customer1 | password: admin123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
        
        // Auto-hide alert after 5 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if(alert) {
                alert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>