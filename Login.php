<?php
ob_start();
session_start();
require_once "db.php";  // your database connection file
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['loginEmail'] ?? '';
    $password = $_POST['loginPassword'] ?? '';

    if ($email && $password) {
        $sql = "SELECT id, password_hash, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'student') {
                    header("Location: dashboard_intern.php");
                } elseif ($user['role'] === 'employer') {
                    header("Location: dashboard_employee.php");
                } else {
                    header("Location: dashboard_generic.php"); // fallback
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "Please enter email and password.";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | nextNtern</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      scroll-behavior: smooth;
    }
    
    /* Header Styles */
    .top-header {
      background: linear-gradient(to right, #edf4ff, #e3f1f6);
      padding: 1rem 0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .logo-text {
      font-size: 1.8rem;
      font-weight: bold;
      color: #333;
      transition: transform 0.3s ease;
    }
    
    .logo-text:hover {
      transform: scale(1.05);
    }
    
    .logo-highlight {
      color: #1c1a85;
      font-weight: bold;
    }
    
    .subtext {
      font-size: 0.75rem;
      color: #555;
    }
    
    .main-nav {
      background-color: #1c1a85;
      padding: 0.3rem 0;
    }
    
    .main-nav .nav-links a {
      color: white !important;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      padding: 8px 12px !important;
      text-decoration: none;
    }
    
    .main-nav .nav-links a:hover {
      background-color: #2c2c6d;
      border-radius: 4px;
    }
    
  .employers-link {
      color: #ffc107 !important;
      font-weight: bold;
      position: relative;
      text-decoration: none;
    }
    .employers-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: #ffc107;
      transition: width 0.3s ease;
    }
    .employers-link:hover::after {
      width: 100%;
    }
    /* Login Form Styles */
    .login-container {
      max-width: 500px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }
  
    
    .login-title {
      text-align: center;
      color: #1c1a85;
      margin-bottom: 30px;
      font-weight: bold;
    }
    
    .form-label {
      font-weight: 500;
      color: #333;
    }
    
    .form-control {
      border-radius: 5px;
      padding: 10px;
      border: 1px solid #ddd;
    }
    
    .form-control:focus {
      border-color: #1c1a85;
      box-shadow: 0 0 0 0.25rem rgba(28, 26, 133, 0.25);
    }
    
    .btn-login {
      background-color: #1c1a85;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: 500;
      transition: all 0.3s ease;
      width: 100%;
    }
    
    .btn-login:hover {
      background-color: #0f0e52;
      transform: translateY(-2px);
    }
    
    .register-link {
      text-align: center;
      margin-top: 20px;
    }
    
    .register-link a {
      color: #1c1a85;
      font-weight: 500;
      text-decoration: none;
    }
    
    .register-link a:hover {
      text-decoration: underline;
    }
    
    .input-group-text {
      background-color: #eef1f6;
      border-right: none;
    }
    
    .password-toggle {
      cursor: pointer;
      background-color: #eef1f6;
      border-left: none;
    }
    
    .forgot-password {
      text-align: right;
      margin-top: 5px;
    }
    
    .forgot-password a {
      color: #6c757d;
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .forgot-password a:hover {
      color: #1c1a85;
      text-decoration: underline;
    }
    
    .social-login {
      margin-top: 20px;
      text-align: center;
    }
    
    .social-login p {
      color: #6c757d;
      position: relative;
      margin-bottom: 20px;
    }
    
    .social-login p::before,
    .social-login p::after {
      content: "";
      position: absolute;
      height: 1px;
      width: 30%;
      background-color: #ddd;
      top: 50%;
    }
    
    .social-login p::before {
      left: 0;
    }
    
    .social-login p::after {
      right: 0;
    }
    
    .social-icons {
      display: flex;
      justify-content: center;
      gap: 15px;
    }
    
    .social-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      transition: all 0.3s ease;
    }
    
    .social-icon:hover {
      transform: translateY(-3px);
    }
    
    .google {
      background-color: #DB4437;
    }
    
    .facebook {
      background-color: #4267B2;
    }
    
    .linkedin {
      background-color: #0077B5;
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="animate__animated animate__fadeInDown">
  
  <div class="top-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex flex-column">
          <div class="brand">
            <span class="logo-text">next<span class="logo-highlight">N</span>tern</span>
          </div>
          <small class="subtext">Since 2025</small>
        </div>
      </div>
    </div>
  </div>

  <div class="main-nav">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <div class="nav-links d-flex">
          <a href="#">Home</a>
          <a href="#" onclick="alert('Please log in first'); return false;">Find Jobs</a>
          <a href="#" onclick="alert('Please log in first'); return false;">Post Applications</a>
          <a href="#" onclick="alert('Please log in first'); return false;">Profile Update</a>
        </div>
        <div>
          <a href="#" class="employers-link">Employers →</a>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Login Form -->
<div class="login-container animate__animated animate__fadeIn">
  <h2 class="login-title">Welcome Back</h2>
  
  <form id="loginForm" method="POST" action="Login.php">
    <div class="mb-3">
      <label for="loginEmail" class="form-label">Email Address</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
      </div>
    </div>
    
    <div class="mb-3">
      <label for="loginPassword" class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
       <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
        <span class="input-group-text password-toggle" id="toggleLoginPassword">
          <i class="fas fa-eye"></i>
        </span>
      </div>
      <div class="forgot-password">
        <a href="forgot-password.php">Forgot password?</a>
      </div>
    </div>
    
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="rememberMe">
      <label class="form-check-label" for="rememberMe">Remember me</label>
    </div>
    
    <button type="submit" class="btn btn-login">Log In</button>
    
    <div class="register-link">
      Don't have an account? <a href="Signup.html">Sign up</a>
    </div>
    
    <div class="social-login">
      <p>Or log in with</p>
      <div class="social-icons">
        <a href="#" class="social-icon google">
          <i class="fab fa-google"></i>
        </a>
        <a href="#" class="social-icon facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" class="social-icon linkedin">
          <i class="fab fa-linkedin-in"></i>
        </a>
      </div>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle password visibility
  document.getElementById('toggleLoginPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('loginPassword');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
  
  // Form submission
 /* document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Basic validation
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    if (!email || !password) {
      alert('Please fill in all fields');
      return;
    }
    
    // If validation passes, you would typically send data to server here
    alert('Login successful! Redirecting to dashboard...');
    // window.location.href = 'dashboard.html'; // Uncomment to redirect
  });*/
</script>
</body>
</html>
