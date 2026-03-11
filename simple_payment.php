<?php
session_start();
include("connection/connect.php");

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $currency = isset($_POST['currency']) ? $_POST['currency'] : 'INR';
    
    // Generate a unique order ID
    $order_id = 'ORD-' . time() . '-' . rand(1000, 9999);
    
    // Store the order details in the session
    $_SESSION['order_id'] = $order_id;
    $_SESSION['amount'] = $amount;
    $_SESSION['currency'] = $currency;
    
    // Debug information
    echo "Order details stored in session:<br>";
    echo "Order ID: " . $order_id . "<br>";
    echo "Amount: " . $amount . "<br>";
    echo "Currency: " . $currency . "<br>";
    echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "<br>";
    
    // Redirect to the payment confirmation page
    header("Location: payment_confirmation.php");
    exit;
} else {
    // If not submitted via POST, redirect to the home page
    header("Location: index.php");
    exit;
}
?> 