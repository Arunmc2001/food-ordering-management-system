<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'razorpay-php/config.php';
require_once 'connection/connect.php';

use Razorpay\Api\Api;

// Initialize Razorpay API
try {
    $config = require_once 'razorpay-php/config.php';
    $api = new \Razorpay\Api\Api($config['key_id'], $config['key_secret']);
} catch (Exception $e) {
    die('Error initializing Razorpay API: ' . $e->getMessage());
}

// Get payment details from URL
$razorpay_payment_id = $_GET['razorpay_payment_id'] ?? '';
$razorpay_order_id = $_GET['razorpay_order_id'] ?? '';
$razorpay_signature = $_GET['razorpay_signature'] ?? '';

// Verify payment signature
try {
    $attributes = array(
        'razorpay_order_id' => $razorpay_order_id,
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_signature' => $razorpay_signature
    );
    
    $api->utility->verifyPaymentSignature($attributes);
    
    // Payment verified successfully
    $payment = $api->payment->fetch($razorpay_payment_id);
    $amount = $payment['amount'] / 100; // Convert paise to rupees
    $status = $payment['status'];
    $payment_date = date('Y-m-d H:i:s', $payment['created_at']);
    
    // Update existing orders with payment details
    $update_query = "UPDATE users_orders 
                    SET payment_status = ?, 
                        razorpay_payment_id = ?, 
                        payment_date = ? 
                    WHERE razorpay_order_id = ?";
    
    $stmt = $db->prepare($update_query);
    $stmt->bind_param("ssss", $status, $razorpay_payment_id, $payment_date, $razorpay_order_id);
    
    if ($stmt->execute()) {
        // Redirect to success page
        header("location:your_orders.php?payment_status=success");
        exit();
    } else {
        // Handle database error
        throw new Exception("Error updating payment details in database");
    }
} catch (Exception $e) {
    // Handle verification error
    $_SESSION['payment_error'] = "Payment verification failed: " . $e->getMessage();
    header("location:your_orders.php?payment_status=failed");
    exit();
}

// Get the order ID from the database
$order_query = "SELECT o_id FROM users_orders WHERE razorpay_order_id = '$razorpay_order_id'";
$order_result = mysqli_query($db, $order_query);
$order_row = mysqli_fetch_assoc($order_result);
$db_order_id = $order_row['o_id'] ?? null;

echo "Database Order ID: " . ($db_order_id ?? 'Not found') . "<br>";

// Clear the session variables
unset($_SESSION['order_id']);
unset($_SESSION['amount']);
unset($_SESSION['currency']);

// Redirect to the order tracking page
if ($db_order_id) {
    echo "Redirecting to track_order.php?order_id=$db_order_id<br>";
    header("Location: track_order.php?order_id=$db_order_id");
    exit;
} else {
    echo "Redirecting to index.php<br>";
    header("Location: index.php");
    exit;
}
?> 