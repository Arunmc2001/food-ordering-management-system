<?php
session_start();

require('vendor/autoload.php');

use Razorpay\Api\Api;

// Check if amount is set
if (!isset($_GET['amount'])) {
    die('Amount not specified');
}

// Razorpay API Key and Secret
$keyId = "rzp_test_fVWlxNwdI90zig";
$keySecret = "3hY88cB4f5DNc10FGOIpPYsH"; // Replace with your Razorpay key secret

$actual_amount = floatval($_GET['amount']);

// Create order using Razorpay API
$api = new Api($keyId, $keySecret);

$orderData = [
    'receipt'         => 'rcptid_' . time(),
    'amount'          => $actual_amount * 100, // amount in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // auto capture
];

try {
    $razorpayOrder = $api->order()->create($orderData);
    $order_id = $razorpayOrder['id'];

    // Store order ID in session for verification later
    $_SESSION['razorpay_order_id'] = $order_id;
} catch (Exception $e) {
    die('Error creating order: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | Food Ordering System</title>
    <style>
        .razorpay-payment-button {
            background: #3399cc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Complete Your Payment</h2>
        <p>Amount to Pay: ₹<?php echo number_format($actual_amount, 2); ?></p>
        
        <button class="razorpay-payment-button" onclick="makePayment()">Pay Now</button>
        
        <a href="index.php" class="back-button">Go Back to Home</a>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    var options = {
        "key": "<?php echo $keyId; ?>",
        "amount": "<?php echo ($actual_amount * 100); ?>",
        "currency": "INR",
        "name": "Food Ordering System",
        "description": "Order Payment",
        "image": "images/icn.png",
        "order_id": "<?php echo $order_id; ?>",
        "handler": function (response){
            // On payment success
            window.location.href = 'status.php?payment_id=' + response.razorpay_payment_id + 
                                 '&order_id=' + response.razorpay_order_id + 
                                 '&signature=' + response.razorpay_signature;
        },
        "prefill": {
            "name": "Customer Name",
            "email": "customer@example.com",
            "contact": "9999999999"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    
    function makePayment() {
        var rzp = new Razorpay(options);
        rzp.open();
        
        rzp.on('payment.failed', function (response){
            alert('Payment failed. Please try again.');
            console.error(response.error);
        });
    }
    </script>
</body>
</html>
