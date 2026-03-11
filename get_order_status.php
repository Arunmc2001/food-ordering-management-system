<?php
include("connection/connect.php");

$order_id = $_GET['order_id'] ?? null;

if ($order_id) {
    // Query to fetch order status from the database
    $query = "SELECT status FROM users_orders WHERE o_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Return the status as JSON
        echo json_encode(['status' => $row['status']]);
    } else {
        echo json_encode(['status' => 'Order not found']);
    }
} else {
    echo json_encode(['status' => 'Invalid Order ID']);
}
?>
