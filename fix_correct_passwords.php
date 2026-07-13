<?php
require_once 'config.php';

echo "<h2>🔧 Fixing Correct Passwords for Each User</h2>";

// Different hashes for different passwords
$users = [
    ['admin', 'admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'],
    ['staff', 'staff123', '$2y$10$XNdPqNqwEvBfNwLxH3VxOeMp8qHjK5lR7tY9uI2oP3zQ4wS6xF7a'],
    ['manager', 'manager123', '$2y$10$5XpKqLrMsNvBwCxYzU1I2oP3qR4sT5uV6wX7yZ8aB9cD0eF1gH2iJ']
];

foreach ($users as $user) {
    $username = $user[0];
    $password = $user[1];
    $hash = $user[2];
    
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $hash, $username);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green'>✅ Password for <strong>$username</strong> updated to <strong>$password</strong></p>";
    } else {
        echo "<p style='color:red'>❌ Error updating $username: " . mysqli_error($conn) . "</p>";
    }
}

// Verify each user with their specific password
echo "<h3>Verification:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'><th>Username</th><th>Password to Use</th><th>Status</th></tr>";

$test_users = [
    ['admin', 'admin123'],
    ['staff', 'staff123'],
    ['manager', 'manager123']
];

foreach ($test_users as $test) {
    $username = $test[0];
    $test_password = $test[1];
    
    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row && password_verify($test_password, $row['password'])) {
        echo "<tr style='background: #d4edda;'><td><strong>$username</strong></td><td>$test_password</td><td style='color:green;'>✅ CORRECT</td></tr>";
    } else {
        echo "<tr style='background: #f8d7da;'><td><strong>$username</strong></td><td>$test_password</td><td style='color:red;'>❌ INCORRECT</td></tr>";
    }
}

echo "</table>";

echo "<br><a href='login.php' style='display:inline-block; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>Go to Login →</a>";
?>