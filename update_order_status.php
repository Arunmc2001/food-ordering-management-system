<?php
session_start();
include("connection/connect.php");

// Check if delivery person is logged in
if (!isset($_SESSION['delivery_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$order_id = $_POST['order_id'];
$status = $_POST['status'];
$delivery_id = $_SESSION['delivery_id'];
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

// Validate status
$valid_statuses = ['picked_up', 'on_the_way', 'delivered'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Update order status
$query = "UPDATE users_orders SET status = ? WHERE o_id = ? AND delivery_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("sii", $status, $order_id, $delivery_id);

if ($stmt->execute()) {
    // If order is delivered, update delivery location
    if ($status === 'delivered' && $latitude && $longitude) {
        $location_query = "UPDATE delivery_locations SET latitude = ?, longitude = ?, delivered_at = NOW() 
                          WHERE order_id = ?";
        $location_stmt = $db->prepare($location_query);
        $location_stmt->bind_param("ddi", $latitude, $longitude, $order_id);
        $location_stmt->execute();
    }
    
    // Update delivery location for all statuses if coordinates are provided
    if ($latitude && $longitude) {
        // Check if location record exists
        $check_query = "SELECT * FROM delivery_locations WHERE order_id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $update_query = "UPDATE delivery_locations SET latitude = ?, longitude = ?, updated_at = NOW() 
                            WHERE order_id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bind_param("ddi", $latitude, $longitude, $order_id);
            $update_stmt->execute();
        } else {
            // Insert new record
            $insert_query = "INSERT INTO delivery_locations (order_id, delivery_id, latitude, longitude) 
                            VALUES (?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bind_param("iidd", $order_id, $delivery_id, $latitude, $longitude);
            $insert_stmt->execute();
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update order status: ' . $db->error]);
}
?> 