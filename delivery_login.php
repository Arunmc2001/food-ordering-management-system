<?php
session_start();
include("connection/connect.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM delivery_persons WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $delivery_person = $result->fetch_assoc();
        if (password_verify($password, $delivery_person['password'])) {
            $_SESSION['delivery_id'] = $delivery_person['id'];
            $_SESSION['delivery_name'] = $delivery_person['name'];
            header("Location: delivery_tracker.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FOODMANIA - Delivery Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b00;
            --primary-gradient: linear-gradient(45deg, #ff6b00, #ff9500);
            --secondary-color: #ff9500;
            --accent-color: #ffd700;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: #ecf0f1;
            --header-bg: #1a1a1a;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .live-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            background: linear-gradient(-45deg, #ff6b00, #ff9500, #ffd700, #ff6b00);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .top-header {
            background-color: var(--header-bg);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .brand-logo {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .dashboard-title {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 500;
            padding-left: 1.5rem;
            border-left: 2px solid var(--primary-color);
        }
        .login-container {
            background: var(--card-background);
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            width: 350px;
            margin: 5vh auto 0 auto;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-container h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 1.2rem;
            width: 100%;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            background: #f9f9f9;
            margin-bottom: 0.5rem;
        }
        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1.1rem;
            letter-spacing: 1px;
            margin-top: 0.5rem;
            transition: box-shadow 0.2s, background 0.2s;
        }
        button:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.2);
            background: var(--secondary-color);
        }
        .error {
            color: var(--danger-color);
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
            background: #fff0e0;
            border-radius: 8px;
            padding: 10px;
        }
        @media (max-width: 500px) {
            .login-container {
                width: 95vw;
                padding: 1.2rem 0.5rem 1rem 0.5rem;
            }
            .top-header {
                padding: 1rem;
            }
            .brand-logo {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="live-bg"></div>
    <header class="top-header">
        <div class="logo-container">
            <div class="brand-logo">FOODMANIA</div>
            <div class="dashboard-title">Delivery Login</div>
        </div>
    </header>
    <div class="login-container">
        <h2>Delivery Person Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="links-container" style="text-align: center; margin-top: 1rem;">
            <div class="register-link" style="margin-bottom: 0.5rem;">
                New delivery partner? <a href="delivery_register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Register here</a>
            </div>
            <div class="forgot-link">
                <a href="delivery_forgot_password.php" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html> 