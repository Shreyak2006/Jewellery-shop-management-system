<?php
session_start();
require_once 'config.php';

// Redirect to dashboard if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login form submission directly on index page (optional)
$error = '';
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goldsmith Management System | Home</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
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
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-animation {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        .circle1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }

        .circle2 {
            width: 500px;
            height: 500px;
            bottom: -250px;
            right: -250px;
            animation-delay: 5s;
        }

        .circle3 {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 30px) rotate(180deg); }
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            margin-left: 30px;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 20px 50px;
            position: relative;
            z-index: 1;
        }

        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            margin-bottom: 80px;
            flex-wrap: wrap;
        }

        .hero-content {
            flex: 1;
            color: white;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            animation: fadeInUp 1.2s ease;
        }

        .btn-primary {
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-outline {
            background: transparent;
            color: white;
            padding: 12px 30px;
            border: 2px solid white;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }

        .hero-image {
            flex: 1;
            text-align: center;
            animation: fadeInRight 0.8s ease;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
        }

        /* Features Section */
        .features {
            margin-bottom: 80px;
        }

        .section-title {
            text-align: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 50px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            color: white;
            transition: all 0.3s;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
        }

        .feature-card p {
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 50px;
            margin-bottom: 80px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }

        /* Login Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            width: 450px;
            margin: 80px auto;
            border-radius: 20px;
            padding: 35px;
            position: relative;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeInRight {
            from {
                transform: translateX(30px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            color: #333;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.2rem;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 3px solid #c33;
        }

        .modal-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .modal-footer a {
            color: #667eea;
            text-decoration: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px;
            color: rgba(255, 255, 255, 0.7);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            .hero {
                flex-direction: column;
                text-align: center;
            }
            .hero-buttons {
                justify-content: center;
            }
            .modal-content {
                width: 90%;
                margin: 50px auto;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="bg-circle circle1"></div>
        <div class="bg-circle circle2"></div>
        <div class="bg-circle circle3"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">✨ Goldsmith MS</div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#" onclick="openLoginModal()">Login</a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <h1>Welcome to <br>Goldsmith Management System</h1>
                <p>The complete solution for managing your goldsmith business efficiently. Track customers, manage orders, process payments, and grow your business.</p>
                <div class="hero-buttons">
                    <button class="btn-primary" onclick="openLoginModal()">Get Started</button>
                    <a href="#features" class="btn-outline">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <div style="font-size: 200px;">💎</div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="features">
            <h2 class="section-title">Why Choose Us?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Customer Management</h3>
                    <p>Easily manage customer information, track purchase history, and maintain relationships.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Order Tracking</h3>
                    <p>Track orders from creation to delivery with real-time status updates.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Payment Processing</h3>
                    <p>Secure payment processing with multiple payment method support.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Analytics & Reports</h3>
                    <p>Generate detailed reports and gain insights into your business performance.</p>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats">
            <div class="stats-grid">
                <div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div>
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Orders Completed</div>
                </div>
                <div>
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Gold Products</div>
                </div>
                <div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div id="about" class="features">
            <h2 class="section-title">About Us</h2>
            <div class="feature-card" style="max-width: 800px; margin: 0 auto;">
                <div class="feature-icon">🏪</div>
                <h3>Your Trusted Gold Business Partner</h3>
                <p>Goldsmith Management System is a comprehensive solution designed specifically for goldsmith businesses. 
                We provide tools to manage customers, track inventory, process orders, and generate insights to help 
                your business grow. Our system is secure, reliable, and easy to use.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2024 Goldsmith Management System. All rights reserved.</p>
            <p style="margin-top: 10px;">💎 Premium Gold Business Management Solution</p>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔐 Sign In</h2>
                <span class="close" onclick="closeLoginModal()">&times;</span>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" name="username" required placeholder="Enter your username or email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="modal_password" required placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="toggleModalPassword()">👁️</button>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-login">Sign In →</button>
                <div class="modal-footer">
                    <a href="#">Forgot Password?</a> | <a href="#">Create Account</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }
        
        // Toggle password visibility for modal
        function toggleModalPassword() {
            const passwordInput = document.getElementById('modal_password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const btn = document.querySelector('.toggle-password');
            btn.textContent = type === 'password' ? '👁️' : '🙈';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target === modal) {
                closeLoginModal();
            }
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Auto-hide error message after 3 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 3000);
        
        // Add animation to feature cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>