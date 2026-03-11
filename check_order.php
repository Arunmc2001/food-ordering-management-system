<?php
include("connection/connect.php");

// Get order ID from URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo "<h2>Error</h2>";
    echo "<p>Please provide a valid order ID.</p>";
    exit;
}

// Check if the order exists
$query = "SELECT * FROM users_orders WHERE o_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<h2>Order Not Found</h2>";
    echo "<p>Order ID $order_id does not exist in the database.</p>";
    exit;
}

// Display order details
echo "<h2>Order Details</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Order ID</th><td>" . $order['o_id'] . "</td></tr>";
echo "<tr><th>User ID</th><td>" . $order['u_id'] . "</td></tr>";
echo "<tr><th>Title</th><td>" . $order['title'] . "</td></tr>";
echo "<tr><th>Quantity</th><td>" . $order['quantity'] . "</td></tr>";
echo "<tr><th>Price</th><td>" . $order['price'] . "</td></tr>";
echo "<tr><th>Status</th><td>" . $order['status'] . "</td></tr>";
echo "<tr><th>Date</th><td>" . $order['date'] . "</td></tr>";

// Check if tracking columns exist and have values
$has_lat = isset($order['delivery_lat']) && $order['delivery_lat'] !== null;
$has_lng = isset($order['delivery_lng']) && $order['delivery_lng'] !== null;

echo "<tr><th>Delivery Latitude</th><td>" . ($has_lat ? $order['delivery_lat'] : "Not set") . "</td></tr>";
echo "<tr><th>Delivery Longitude</th><td>" . ($has_lng ? $order['delivery_lng'] : "Not set") . "</td></tr>";
echo "</table>";

// Check if the delivery_lat and delivery_lng columns exist
$query = "SHOW COLUMNS FROM users_orders LIKE 'delivery_lat'";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Tracking columns (delivery_lat, delivery_lng) exist in the database.</p>";
} else {
    echo "<p style='color: red;'>✗ Tracking columns (delivery_lat, delivery_lng) are missing. Please run the SQL script in realtime/update_db.sql</p>";
}

// Check if the Socket.IO server is running
$socket_url = "http://localhost:3000";
$ch = curl_init($socket_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "<p style='color: green;'>✓ Socket.IO server is running.</p>";
} else {
    echo "<p style='color: red;'>✗ Socket.IO server is not running. Please start the server using the start_server.bat file.</p>";
    echo "<p>Error code: $http_code</p>";
}

// Provide instructions for fixing issues
if (!$has_lat || !$has_lng) {
    echo "<h3>To fix tracking issues:</h3>";
    echo "<ol>";
    echo "<li>Run the SQL script in realtime/update_db.sql to add tracking columns</li>";
    echo "<li>Update the order with delivery coordinates:</li>";
    echo "<pre>UPDATE users_orders SET delivery_lat = 20.5937, delivery_lng = 78.9629 WHERE o_id = $order_id;</pre>";
    echo "</ol>";
}

if ($http_code != 200) {
    echo "<h3>To start the Socket.IO server:</h3>";
    echo "<ol>";
    echo "<li>Make sure Node.js is installed on your system</li>";
    echo "<li>Double-click on start_server.bat in the realtime folder</li>";
    echo "<li>Or open a command prompt and run: cd C:\\xampp\\htdocs\\food\\realtime && node server.js</li>";
    echo "</ol>";
}
?> 