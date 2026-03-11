<?php
session_start();
include("connection/connect.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    
    // Check if email exists
    $query = "SELECT * FROM delivery_persons WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        $update_query = "UPDATE delivery_persons SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bind_param("sss", $token, $expiry, $email);
        
        if ($update_stmt->execute()) {
            // Create reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/delivery_reset_password.php?token=" . $token;
            
            // Send email (you'll need to configure your email settings)
            $to = $email;
            $subject = "Password Reset - FOODMANIA Delivery";
            $message = "Hello,\n\nYou have requested to reset your password. Click the link below to reset your password:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this, please ignore this email.\n\n";
            $message .= "Best regards,\nFOODMANIA Team";
            $headers = "From: noreply@foodmania.com";
            
            if(mail($to, $subject, $message, $headers)) {
                $success = "Password reset instructions have been sent to your email";
            } else {
                $error = "Error sending email. Please try again later.";
            }
        } else {
            $error = "Error occurred. Please try again later.";
        }
    } else {
        // Don't reveal if email exists or not for security
        $success = "If your email exists in our system, you will receive password reset instructions";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FOODMANIA - Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reusing the same styles */
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
        }
        .dashboard-title {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 500;
            padding-left: 1.5rem;
            border-left: 2px solid var(--primary-color);
        }
        .forgot-container {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            width: 400px;
            margin: 5vh auto;
            position: relative;
            z-index: 2;
        }
        .forgot-container h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background: #f9f9f9;
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
            transition: all 0.3s ease;
        }
        button:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.2);
            transform: translateY(-2px);
        }
        .error, .success {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .error {
            color: var(--danger-color);
            background: #fff0e0;
        }
        .success {
            color: var(--success-color);
            background: #e0ffe0;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 500px) {
            .forgot-container {
                width: 95%;
                padding: 1.5rem;
                margin: 2vh auto;
            }
            .top-header {
                padding: 1rem;
            }
            .brand-logo {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="live-bg"></div>
    <header class="top-header">
        <div class="logo-container">
            <div class="brand-logo">FOODMANIA</div>
            <div class="dashboard-title">Forgot Password</div>
        </div>
    </header>
    <div class="forgot-container">
        <h2>Reset Your Password</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Reset Link</button>
        </form>
        <div class="back-link">
            <a href="delivery_login.php">Back to Login</a>
        </div>
    </div>
</body>
</html> 