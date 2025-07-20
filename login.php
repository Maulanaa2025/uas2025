<?php
session_start();
require 'config.php';

// Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Messages
$login_error = '';
$register_error = '';
$register_success = '';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $npm = $_POST['npm'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE npm = ?");
        $stmt->execute([$npm]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_npm'] = $user['npm'];
            header("Location: index.php");
            exit;
        } else {
            $login_error = "NPM or password incorrect!";
        }
    } catch (PDOException $e) {
        $login_error = "Error: " . $e->getMessage();
    }
}

// Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $npm = $_POST['npm'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE npm = ? OR email = ?");
        $check->execute([$npm, $email]);
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            $register_error = "NPM or Email already registered!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, npm, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $npm, $email, $password]);
            $register_success = "Registration successful! Please login.";
        }
    } catch (PDOException $e) {
        $register_error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .form-container {
            padding: 30px;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #004754;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #003440;
        }
        
        .toggle-btn {
            background: #bebd00;
            margin-top: 10px;
        }
        
        .toggle-btn:hover {
            background: #a8a700;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }
        
        .error {
            background: #fdecea;
            color: #e74c3c;
        }
        
        .success {
            background: #e8f8f0;
            color: #2ecc71;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Login Form (default visible) -->
        <div class="form-container" id="login-form">
            <h1>Login</h1>
            
            <?php if (!empty($login_error)): ?>
                <div class="message error"><?= $login_error ?></div>
            <?php endif; ?>
            
            <?php if (!empty($register_success)): ?>
                <div class="message success"><?= $register_success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="text" name="npm" placeholder="NPM" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            
            <button class="toggle-btn" onclick="showRegister()">Create Account</button>
        </div>
        
        <!-- Register Form (hidden by default) -->
        <div class="form-container hidden" id="register-form">
            <h1>Register</h1>
            
            <?php if (!empty($register_error)): ?>
                <div class="message error"><?= $register_error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="text" name="npm" placeholder="NPM" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
            </form>
            
            <button class="toggle-btn" onclick="showLogin()">Already have an account? Login</button>
        </div>
    </div>

    <script>
        function showRegister() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        }
        
        function showLogin() {
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('login-form').classList.remove('hidden');
        }
        
        // Auto show login if there's register success message
        <?php if(!empty($register_success)): ?>
            showLogin();
        <?php endif; ?>
    </script>
</body>
</html>