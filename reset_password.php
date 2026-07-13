<?php
require_once 'config.php';

echo "<h2>Password Reset Tool</h2>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update the password
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $username);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green'>✅ Password updated successfully for user: $username</p>";
        echo "<p>New password hash: " . substr($hashed_password, 0, 50) . "...</p>";
        
        // Verify it works
        echo "<h3>Verification:</h3>";
        if (password_verify($new_password, $hashed_password)) {
            echo "<p style='color:green'>✅ Password verification successful! You can now login.</p>";
        } else {
            echo "<p style='color:red'>❌ Password verification failed!</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Display current users
$result = mysqli_query($conn, "SELECT id, username, email, role FROM users");
?>

<h3>Current Users:</h3>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['username']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['role']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>Reset Password:</h3>
<form method="POST">
    <label>Username:</label>
    <select name="username" required>
        <option value="admin">admin</option>
        <option value="staff">staff</option>
        <option value="manager">manager</option>
    </select>
    <br><br>
    <label>New Password:</label>
    <input type="text" name="new_password" value="admin123" required>
    <br><br>
    <input type="submit" value="Reset Password">
</form>

<hr>
<p><strong>After resetting, try logging in with:</strong></p>
<p>Username: admin<br>Password: admin123</p>