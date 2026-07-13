<?php
echo "<h2>Database Connection Test</h2>";

// Try connecting with different configurations
$configs = [
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3307, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => ''],
];

foreach ($configs as $config) {
    echo "<strong>Testing: {$config['host']}:{$config['port']} with user '{$config['user']}'</strong><br>";
    $conn = @mysqli_connect($config['host'], $config['user'], $config['pass'], '', $config['port']);
    
    if ($conn) {
        echo "✅ SUCCESS! Connected to MySQL!<br>";
        echo "   Server info: " . mysqli_get_server_info($conn) . "<br>";
        
        // Check if goldsmith database exists
        $db_check = mysqli_query($conn, "SHOW DATABASES LIKE 'goldsmith'");
        if(mysqli_num_rows($db_check) > 0) {
            echo "   ✅ Database 'goldsmith' exists!<br>";
        } else {
            echo "   ❌ Database 'goldsmith' not found!<br>";
        }
        
        mysqli_close($conn);
        echo "<br>";
        break;
    } else {
        echo "❌ Failed: " . mysqli_connect_error() . "<br><br>";
    }
}
?>