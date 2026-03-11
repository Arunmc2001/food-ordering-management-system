<?php
include("connection/connect.php");

// Get order ID from URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo "<h2>Error</h2>";
    echo "<p>Please provide a valid order ID.</p>";
    exit;
}

// Update the order with delivery coordinates
$query = "UPDATE users_orders SET delivery_lat = 20.5937, delivery_lng = 78.9629 WHERE o_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $order_id);
$result = $stmt->execute();

if ($result) {
    echo "<h2>Success</h2>";
    echo "<p>Order ID $order_id has been updated with delivery coordinates.</p>";
    echo "<p>Latitude: 20.5937</p>";
    echo "<p>Longitude: 78.9629</p>";
    echo "<p><a href='track_order.php?order_id=$order_id'>Go to tracking page</a></p>";
} else {
    echo "<h2>Error</h2>";
    echo "<p>Failed to update order ID $order_id.</p>";
    echo "<p>Error: " . $db->error . "</p>";
}
?> 