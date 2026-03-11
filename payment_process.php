<?php
session_start();
require('connection/connect.php');
require_once 'razorpay-php/config.php';

if (!isset($_POST['amount'])) {
    header("location:index.php");
    exit();
}

$amount = $_POST['amount'];
$u_id = $_SESSION["user_id"];
$status = "pending";

// Get Razorpay configuration
$config = require 'razorpay-php/config.php';

// Initialize API
try {
    $api = new \Razorpay\Api\Api($config['key_id'], $config['key_secret']);
} catch (Exception $e) {
    die('Error initializing Razorpay API: ' . $e->getMessage());
}

// Get items from session cart
if (isset($_SESSION["cart_item"])) {
    $order_total = 0;
    foreach ($_SESSION["cart_item"] as $item) {
        $item_total = ($item["price"] * $item["quantity"]);
        $order_total += $item_total;
    }

    // Convert amount to paise
    $amount_in_paise = (int) ($order_total * 100);

    // Create order
    try {
        $order = $api->order->create([
            'receipt'         => 'order_' . uniqid(),
            'amount'          => $amount_in_paise,
            'currency'        => $config['display_currency'],
            'payment_capture' => 1
        ]);

        // Save order details in session
        $_SESSION['razorpay_order_id'] = $order['id'];
        $_SESSION['razorpay_amount'] = $order_total;

        // Insert order into database with pending status
        foreach ($_SESSION["cart_item"] as $item) {
            $SQL = "INSERT INTO users_orders(u_id, title, quantity, price, status, date, razorpay_order_id) VALUES (?,?,?,?,?,?,?)";
            $stmt = $db->prepare($SQL);
            $stmt->bind_param("isiissi", $u_id, $item["title"], $item["quantity"], $item_total, $status, date('Y-m-d H:i:s'), $order['id']);
            $stmt->execute();
        }

        // Clear cart
        unset($_SESSION["cart_item"]);
        
        // Redirect to payment page with order details
        header("location:online_payment.php?amount=" . $order_total . "&order_id=" . $order['id']);
        exit();

    } catch (Exception $e) {
        $_SESSION['order_error'] = "Error creating order: " . $e->getMessage();
        header("location:checkout.php");
        exit();
    }
} else {
    header("location:index.php");
    exit();
}
?>