<?php
session_start();
require('connection/connect.php');
require('vendor/autoload.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;
$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false) {
    $api = new Api('rzp_test_YOUR_KEY_ID', 'YOUR_KEY_SECRET');
    
    try {
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );
        
        $api->utility->verifyPaymentSignature($attributes);
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    // Payment successful, update order status
    if (isset($_SESSION["cart_item"])) {
        foreach ($_SESSION["cart_item"] as $item) {
            $item_total = ($item["price"] * $item["quantity"]);
            
            // Insert order with payment details
            $SQL = "INSERT INTO users_orders(u_id, title, quantity, price, status, date, razorpay_payment_id) 
                    VALUES (?, ?, ?, ?, 'paid', NOW(), ?)";
            $stmt = $db->prepare($SQL);
            $stmt->bind_param("isids", 
                $_SESSION["user_id"],
                $item["title"],
                $item["quantity"],
                $item_total,
                $_POST['razorpay_payment_id']
            );
            $stmt->execute();
        }
        
        // Clear cart after successful order
        unset($_SESSION["cart_item"]);
        
        // Set success message
        $_SESSION['payment_success'] = true;
        $_SESSION['payment_msg'] = "Your payment was successful. Payment ID: " . $_POST['razorpay_payment_id'];
        
        header("Location: your_orders.php");
        exit();
    }
} else {
    // Payment failed
    $_SESSION['payment_success'] = false;
    $_SESSION['payment_msg'] = $error;
    header("Location: checkout.php");
    exit();
}
?> 