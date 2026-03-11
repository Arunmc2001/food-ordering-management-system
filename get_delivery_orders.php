<?php
session_start();
include("connection/connect.php");

// Check if delivery person is logged in
if (!isset($_SESSION['delivery_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$delivery_id = $_SESSION['delivery_id'];

// Get orders assigned to this delivery person
$query = "SELECT o.*, u.username as u_name, u.address,
          (SELECT GROUP_CONCAT(CONCAT(title, ':', quantity) SEPARATOR '|')
           FROM order_items 
           WHERE order_id = o.o_id) as items_list
          FROM users_orders o 
          JOIN users u ON o.u_id = u.u_id 
          WHERE o.delivery_id = ? AND (o.status IN ('pending', 'picked_up', 'on_the_way') OR o.status IS NULL)
          ORDER BY o.date DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $delivery_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Process items list
    $items = [];
    if ($row['items_list']) {
        $items_array = explode('|', $row['items_list']);
        foreach ($items_array as $item) {
            list($title, $quantity) = explode(':', $item);
            $items[] = [
                'title' => $title,
                'quantity' => $quantity
            ];
        }
    }
    $row['items'] = $items;
    unset($row['items_list']); // Remove the concatenated string
    $orders[] = $row;
}

echo json_encode($orders);
?> 