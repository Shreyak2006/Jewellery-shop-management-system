<?php
// Test different connection attempts
echo "<h2>Testing Database Connections</h2>";

$tests = [
    'No password' => ['root', ''],
    'Password: root' => ['root', 'root'],
    'Password: mysql' => ['root', 'mysql'],
    'Password: 123456' => ['root', '123456'],
];

foreach ($tests as $name => $creds) {
    echo "<strong>Testing: $name</strong><br>";
    $conn = @mysqli_connect('localhost', $creds[0], $creds[1], 'goldsmith');
    
    if ($conn) {
        echo "✅ SUCCESS! Username: {$creds[0]}, Password: '{$creds[1]}'<br><br>";
        mysqli_close($conn);
        break;
    } else {
        echo "❌ Failed: " . mysqli_connect_error() . "<br><br>";
    }
}

// Also check if MySQL is running
echo "<hr>";
echo "<strong>MySQL Service Status:</strong><br>";
exec('sc query MySQL', $output, $status);
if ($status === 0) {
    echo "MySQL service exists<br>";
} else {
    echo "Using XAMPP MySQL<br>";
}
?>