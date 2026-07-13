<?php
/**
 * Database Configuration for Goldsmith Management System
 * This file handles database connection and session management
 */

// Database configuration - UPDATE THESE VALUES IF NEEDED
define('DB_HOST', '127.0.0.1');     // MySQL server host
define('DB_PORT', 3307);             // MySQL port (from your phpMyAdmin config)
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password (empty for XAMPP)
define('DB_NAME', 'goldsmith');      // Database name

// Create connection with custom port
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    // Detailed error message for debugging
    $error = mysqli_connect_error();
    die("<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #f5c6cb; background: #f8d7da; border-radius: 10px;'>
            <h2 style='color: #721c24;'>❌ Database Connection Failed</h2>
            <p><strong>Error:</strong> $error</p>
            <hr>
            <h3>Troubleshooting Steps:</h3>
            <ul>
                <li>Make sure MySQL is running in XAMPP Control Panel</li>
                <li>Check if MySQL is using port <strong>3307</strong></li>
                <li>Verify database '<strong>goldsmith</strong>' exists</li>
                <li>Confirm username/password is correct</li>
            </ul>
            <p><small>Current settings: Host: " . DB_HOST . ", Port: " . DB_PORT . ", User: " . DB_USER . "</small></p>
         </div>");
}

// Set character set to UTF-8 for proper encoding
if (!mysqli_set_charset($conn, "utf8mb4")) {
    // Fallback to utf8 if utf8mb4 fails
    mysqli_set_charset($conn, "utf8");
}

// Set timezone (adjust according to your location)
date_default_timezone_set('Asia/Kolkata'); // India Time
// date_default_timezone_set('America/New_York'); // For US
// date_default_timezone_set('Europe/London'); // For UK

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Define site-wide constants
define('SITE_NAME', 'Goldsmith Management System');
define('SITE_URL', 'http://localhost/goldsmithmanagementsystem/');

// Function to check if user is logged in (useful for protected pages)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "login.php");
        exit();
    }
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($data)));
}

// Function to display success/error messages
function showMessage($message, $type = 'success') {
    $class = ($type == 'success') ? 'alert-success' : 'alert-error';
    return "<div class='alert $class'>$message</div>";
}

// Uncomment for debugging (remove in production)
// echo "✅ Connected successfully to database 'goldsmith' on port 3307<br>";
?>