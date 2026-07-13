<?php
require_once 'config.php';

echo "<h2>Direct Login Test</h2>";

$tests = [
    ['admin', 'admin123'],
    ['staff', 'staff123'],
    ['manager', 'manager123']
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'><th>Username</th><th>Password</th><th>Result</th></tr>";

foreach ($tests as $test) {
    $username = $test[0];
    $password = $test[1];
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            echo "<tr style='background:#d4edda;'>
                    <td><strong>$username</strong></td>
                    <td>$password</td>
                    <td style='color:green;'>✅ SUCCESS - Login works!</td>
                  </tr>";
        } else {
            echo "<tr style='background:#f8d7da;'>
                    <td><strong>$username</strong></td>
                    <td>$password</td>
                    <td style='color:red;'>❌ FAILED - Password mismatch<br>
                    Hash in DB: " . substr($row['password'], 0, 30) . "...</td>
                  </tr>";
        }
    } else {
        echo "<tr style='background:#f8d7da;'>
                <td><strong>$username</strong></td>
                <td>$password</td>
                <td style='color:red;'>❌ User not found!</td>
              </tr>";
    }
}
echo "</table>";

echo "<br><a href='login.php' style='display:inline-block; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page →</a>";
?>