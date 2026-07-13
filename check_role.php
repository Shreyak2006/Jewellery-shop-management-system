<?php
session_start();
require_once 'config.php';

echo "<h2>Session Role Check</h2>";

if(isset($_SESSION['user_id'])) {
    echo "User is logged in: " . $_SESSION['username'] . "<br>";
    echo "Role in session: <strong>" . $_SESSION['role'] . "</strong><br>";
    echo "Customer ID: " . ($_SESSION['customer_id'] ?? 'Not set') . "<br>";
} else {
    echo "No user logged in<br>";
}

echo "<h3>All users in database:</h3>";
$result = mysqli_query($conn, "SELECT id, username, role, customer_id FROM users");
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Customer ID</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . ($row['customer_id'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='login.php'>Go to Login</a>";
?>