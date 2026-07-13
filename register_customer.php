<?php
require_once 'config.php';

$success = '';
$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if username exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Username or email already exists!";
    } else {
        $sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'customer')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $full_name);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Registration successful! You can now login.";
            
            // Also add to customers table
            $customer_id = "CUST" . str_pad(rand(100, 999), 3, "0", STR_PAD_LEFT);
            mysqli_query($conn, "INSERT INTO customers (customer_id, customer_name, phone, address) VALUES ('$customer_id', '$full_name', '$phone', '$address')");
        } else {
            $error = "Registration failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration | Goldsmith Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; color: #333; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #667eea; }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>✨ Customer Registration</h2>
        <p class="subtitle">Create your account to start shopping</p>
        
        <?php if($success): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Register as Customer →</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>