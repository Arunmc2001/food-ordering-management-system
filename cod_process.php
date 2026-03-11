<?php
session_start();
require('connection/connect.php');

if (!isset($_POST['amount']) || !isset($_SESSION["cart_item"])) {
    header("location:index.php");
    exit();
}

try {
    foreach ($_SESSION["cart_item"] as $item) {
        $item_total = ($item["price"] * $item["quantity"]);
        
        // Insert order for COD
        $SQL = "INSERT INTO users_orders(u_id, title, quantity, price, status, date) 
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $db->prepare($SQL);
        $stmt->bind_param("isid", 
            $_SESSION["user_id"],
            $item["title"],
            $item["quantity"],
            $item_total
        );
        $stmt->execute();
        
        if ($stmt->affected_rows <= 0) {
            throw new Exception("Failed to create order");
        }
    }
    
    // Clear cart after successful order
    unset($_SESSION["cart_item"]);
    
    // Set success message
    $_SESSION['order_success'] = true;
    $_SESSION['order_msg'] = "Your order has been placed successfully. You can pay on delivery.";
    
    header("Location: your_orders.php");
    exit();
    
} catch (Exception $e) {
    $_SESSION['order_error'] = "Error placing order: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}
?> 