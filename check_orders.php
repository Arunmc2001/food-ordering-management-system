<?php
include("connection/connect.php");

// Check if there are any orders in the database
$query = "SELECT * FROM users_orders LIMIT 5";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h2>Recent Orders</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Order ID</th><th>User ID</th><th>Status</th><th>Date</th></tr>";
    
    while ($order = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $order['o_id'] . "</td>";
        echo "<td>" . $order['u_id'] . "</td>";
        echo "<td>" . $order['status'] . "</td>";
        echo "<td>" . $order['date'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<h2>No Orders Found</h2>";
    echo "<p>There are no orders in the database. You may need to:</p>";
    echo "<ol>";
    echo "<li>Import the database schema from DATABASE FILE/onlinefoodphp.sql</li>";
    echo "<li>Run the SQL script in realtime/update_db.sql to add tracking columns</li>";
    echo "<li>Create some test orders through the food ordering system</li>";
    echo "</ol>";
}

// Check if the delivery_lat and delivery_lng columns exist
$query = "SHOW COLUMNS FROM users_orders LIKE 'delivery_lat'";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Tracking columns (delivery_lat, delivery_lng) exist in the database.</p>";
} else {
    echo "<p style='color: red;'>✗ Tracking columns (delivery_lat, delivery_lng) are missing. Please run the SQL script in realtime/update_db.sql</p>";
}
?> 