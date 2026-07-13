<?php
require_once 'config.php';

echo "<h2>Resetting All Passwords</h2>";

// Create a brand new hash that definitely works
$test_password = 'admin123';
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);

echo "<p>Testing hash with '$test_password': </p>";

if (password_verify($test_password, $new_hash)) {
    echo "<p style='color:green'>✅ Hash verification passed!</p>";
} else {
    echo "<p style='color:red'>❌ Hash verification failed!</p>";
}

echo "<p>New hash: <code>$new_hash</code></p>";

// Update all users
$sql = "UPDATE users SET password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $new_hash);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color:green; font-size:18px;'>✅ All passwords reset successfully!</p>";
    echo "<p>All users can now login with password: <strong>admin123</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
}

// Show users
$result = mysqli_query($conn, "SELECT username FROM users");
echo "<h3>Users available:</h3><ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>" . $row['username'] . " → password: admin123</li>";
}
echo "</ul>";

echo "<br><a href='login.php' style='display:inline-block; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>Go to Real Login Page</a>";
?>