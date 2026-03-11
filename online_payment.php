<?php
session_start();
require('connection/connect.php');
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

if (!isset($_GET['amount']) || empty($_GET['amount'])) {
    header("location:index.php");
    exit();
}

$amount = $_GET['amount'];
$u_id = $_SESSION['user_id'];

// Initialize Razorpay API
$api = new Api(require_once 'razorpay-php/config.php');

// Create order
try {
    $order = $api->order->create([
        'amount' => $amount * 100, // Amount in paise
        'currency' => 'INR',
        'receipt' => 'order_' . uniqid(),
        'payment_capture' => 1
    ]);
} catch (Exception $e) {
    $_SESSION['payment_error'] = "Error creating payment order: " . $e->getMessage();
    header("location:checkout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment - Food Ordering System</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Online Payment</h2>
        <p>Amount to Pay: ₹<?php echo number_format($amount, 2); ?></p>
        
        <form action="process_online_payment.php" method="POST">
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            
            <div class="form-group">
                <label for="expiry">Expiry Date</label>
                <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
            </div>
            
            <div class="form-group">
                <label for="cvv">CVV</label>
                <input type="number" id="cvv" name="cvv" placeholder="123" required>
            </div>
            
            <div class="form-group">
                <label for="name">Cardholder Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" required>
            </div>
            
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            
            <button type="submit" class="btn">Pay Now</button>
            <a href="pay.php?name=<?php echo $amount; ?>" class="btn btn-secondary">Back</a>
        </form>
    </div>
    
    <script>
    // Basic form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Simulate payment processing
        alert('This is a demo payment page. In a real application, this would connect to a payment gateway.');
        
        // Redirect to success page
        window.location.href = 'your_orders.php';
    });
    </script>
</body>
</html> 