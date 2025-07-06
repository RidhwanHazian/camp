<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TasikBiruCamps</title>
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

        .register-container {
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
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

        .register-btn {
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

        .register-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .login-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
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
    <div class="register-container">
        <h1>Create an Account</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p style='color: #ff6b6b; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success_message'])) {
            echo "<p style='color: #4caf50; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;'>" . $_SESSION['success_message'] . "</p>";
            unset($_SESSION['success_message']);
        }
        ?>
        <form action="process_register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="register-btn">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
        
        <a href="homepage.php" class="back-link">Back</a>
    </div>
    
    <div class="copyright">
        © 2025 TasikBiruCamps. All rights reserved.
    </div>
</body>
</html>