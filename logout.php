<?php
/**
 * Logout Script for Goldsmith Management System
 * Destroys user session and redirects to login page
 */

// Start session to access session data
session_start();

// Clear all session variables
$_SESSION = array();

// If using session cookies, destroy the cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session completely
session_destroy();

// Optional: Clear any remember me cookies if implemented
// if (isset($_COOKIE['remember_token'])) {
//     setcookie('remember_token', '', time()-3600, '/');
// }

// Redirect to login page with success message
// Using JavaScript for smooth redirect with message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out | Goldsmith Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            left: -150px;
            animation: float 20s infinite;
        }
        
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
            animation: float 25s infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 30px) rotate(180deg); }
        }
        
        .logout-container {
            text-align: center;
            z-index: 1;
            position: relative;
        }
        
        .logout-card {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 90%;
            margin: 20px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .logout-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .logout-card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        
        .logout-card p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f5f7fa;
            color: #667eea;
            padding: 12px 30px;
            border: 2px solid #667eea;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .countdown {
            font-size: 0.9rem;
            color: #999;
            margin-top: 20px;
        }
        
        .countdown span {
            color: #667eea;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        @media (max-width: 480px) {
            .logout-card {
                padding: 30px;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">
                👋
            </div>
            <h2>Logged Out Successfully</h2>
            <p>You have been securely logged out of your account.<br>Thank you for using Goldsmith Management System.</p>
            
            <div class="spinner"></div>
            
            <div class="btn-group">
                <a href="login.php" class="btn-primary">Login Again →</a>
                <a href="index.php" class="btn-secondary">Back to Home</a>
            </div>
            
            <div class="countdown">
                Redirecting to login page in <span id="countdown">3</span> seconds...
            </div>
        </div>
    </div>
    
    <script>
        // Auto-redirect countdown
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            seconds--;
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'login.php';
            }
        }, 1000);
        
        // Optional: Add fade out effect before redirect
        setTimeout(function() {
            const card = document.querySelector('.logout-card');
            if (card) {
                card.style.transition = 'opacity 0.5s ease';
                card.style.opacity = '0';
            }
        }, 2500);
    </script>
</body>
</html>