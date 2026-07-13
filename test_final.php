<?php
echo "<h2>Database Connection Test</h2>";

// YOUR CORRECT SETTINGS - Port 3307
$host = '127.0.0.1';
$port = 3307;  // IMPORTANT: Your MySQL is on port 3307
$user = 'root';
$pass = '';    // Empty password
$database = 'goldsmith';

echo "<strong>Testing connection to:</strong> {$host}:{$port}<br>";
echo "<strong>Username:</strong> {$user}<br>";
echo "<strong>Password:</strong> " . ($pass === '' ? '(empty)' : 'set') . "<br><br>";

// Connect using your correct port
$conn = @mysqli_connect($host, $user, $pass, $database, $port);

if ($conn) {
    echo "<span style='color: green; font-size: 18px;'>✅ CONNECTION SUCCESSFUL!</span><br>";
    echo "MySQL Server Version: " . mysqli_get_server_info($conn) . "<br>";
    echo "Connected to database: {$database}<br><br>";
    
    // Check tables
    $tables = mysqli_query($conn, "SHOW TABLES");
    echo "<h3>Tables in 'goldsmith' database:</h3>";
    if (mysqli_num_rows($tables) > 0) {
        echo "<ul>";
        while ($table = mysqli_fetch_array($tables)) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ No tables found! You need to create tables.";
    }
    
    mysqli_close($conn);
} else {
    echo "<span style='color: red; font-size: 18px;'>❌ CONNECTION FAILED!</span><br>";
    echo "Error: " . mysqli_connect_error() . "<br><br>";
    echo "<strong>Troubleshooting:</strong><br>";
    echo "1. Make sure MySQL is running in XAMPP (green light)<br>";
    echo "2. Check that MySQL is using port 3307<br>";
    echo "3. Verify database 'goldsmith' exists<br>";
}
?>