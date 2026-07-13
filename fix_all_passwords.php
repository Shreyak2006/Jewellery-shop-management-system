<?php
require_once 'config.php';

echo "<h2>🔧 Fixing All User Passwords</h2>";

$users = [
    ['admin', 'admin123'],
    ['staff', 'staff123'],
    ['manager', 'manager123']
];

$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

foreach ($users as $user) {
    $username = $user[0];
    $password = $user[1];
    
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $hash, $username);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green'>✅ Password for <strong>$username</strong> updated to <strong>$password</strong></p>";
    } else {
        echo "<p style='color:red'>❌ Error updating $username: " . mysqli_error($conn) . "</p>";
    }
}

// Verify
echo "<h3>Verification:</h3>";
$result = mysqli_query($conn, "SELECT username, 
    CASE 
        WHEN password = '$hash' THEN '✅ Correct'
        ELSE '❌ Incorrect'
    END as status 
    FROM users");

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Username</th><th>Status</th><th>Password to use</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $pass = ($row['username'] == 'admin') ? 'admin123' : (($row['username'] == 'staff') ? 'staff123' : 'manager123');
    echo "<tr>
            <td><strong>{$row['username']}</strong></td>
            <td>{$row['status']}</td>
            <td>{$pass}</td>
          </tr>";
}
echo "</table>";

echo "<br><a href='login.php' style='display:inline-block; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>Go to Login →</a>";
?>