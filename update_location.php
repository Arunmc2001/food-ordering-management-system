<?php
session_start();
include("connection/connect.php");

header('Content-Type: application/json');

// Check if delivery person is logged in
if (!isset($_SESSION['delivery_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['latitude']) || !isset($_POST['longitude']) || !isset($_POST['order_id'])) {
    echo json_encode(['error' => 'Missing location coordinates or order ID']);
    exit;
}

$delivery_id = $_SESSION['delivery_id'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$order_id = $_POST['order_id'];

// Update delivery person's location
$query = "UPDATE delivery_locations SET latitude = ?, longitude = ?, updated_at = NOW() 
          WHERE delivery_id = ? AND order_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ddii", $latitude, $longitude, $delivery_id, $order_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Location updated successfully',
        'data' => [
            'delivery_id' => $delivery_id,
            'order_id' => $order_id,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]
    ]);
} else {
    echo json_encode(['error' => 'Failed to update location']);
}
?> 