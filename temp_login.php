<?php
session_start();
require_once 'config.php';

// Simple login - NO PASSWORD CHECK
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Get user by username only, no password check
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['email'] = $row['email'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Temporary Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔑 Temporary Login</h2>
        <p>Enter username (no password needed for now)</p>
        
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <button type="submit">Login →</button>
        </form>
        
        <hr>
        <p style="margin-top: 15px; font-size: 12px; color: #666;">Try: admin, staff, or manager</p>
    </div>
</body>
</html>
