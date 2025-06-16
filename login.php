<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TasikBiruCamps</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            color: #fff;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-image: url('backgroundcamp.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }

        .login-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 2.5rem;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            margin: 2rem;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 600;
            color: #fff;
        }

        .error-message {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .role-selector {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .role-option input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            margin: 0;
            cursor: pointer;
            position: relative;
        }

        .role-option input[type="radio"]:checked {
            border-color: #28a745;
        }

        .role-option input[type="radio"]:checked::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background-color: #28a745;
            border-radius: 50%;
        }

        .role-option label {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.5);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            background-color: #28a745;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .login-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .register-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: #fff;
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login to TasikBiruCamps</h1>
        
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="process_login.php" method="POST">
            <div class="role-selector">
                <div class="role-option">
                    <input type="radio" id="admin" name="role" value="admin" checked>
                    <label for="admin">Admin</label>
                </div>
                <div class="role-option">
                    <input type="radio" id="staff" name="role" value="staff">
                    <label for="staff">Staff</label>
                </div>
                <div class="role-option">
                    <input type="radio" id="customer" name="role" value="customer">
                    <label for="customer">Customer</label>
                </div>
            </div>


            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
        
        <a href="homepage.php" class="back-link">Back</a>
    </div>
    
    <div class="copyright">
        © 2025 TasikBiruCamps. All rights reserved.
    </div>
</body>
</html>