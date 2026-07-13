<?php
echo "<h2>Generate Correct Password Hashes</h2>";

$passwords = [
    'admin123',
    'staff123', 
    'manager123'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'><th>Password</th><th>Hash (Copy this)</th></tr>";

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td><strong>$password</strong></td>";
    echo "<td><code style='background: #2d2d2d; color: #f8f8f2; padding: 5px; display: block; word-break: break-all;'>$hash</code></td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>Copy these SQL statements:</h3>";
echo "<pre style='background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto;'>";
echo "USE goldsmith;\n\n";
echo "-- Update admin password to 'admin123'\n";
echo "UPDATE users SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username = 'admin';\n\n";
echo "-- Update staff password to 'staff123'\n";
echo "UPDATE users SET password = '" . password_hash('staff123', PASSWORD_DEFAULT) . "' WHERE username = 'staff';\n\n";
echo "-- Update manager password to 'manager123'\n";
echo "UPDATE users SET password = '" . password_hash('manager123', PASSWORD_DEFAULT) . "' WHERE username = 'manager';\n";
echo "</pre>";
?>