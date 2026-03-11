<?php
session_start();
include("connection/connect.php");

// Check if delivery person is logged in
if (!isset($_SESSION['delivery_id'])) {
    header("Location: delivery_login.php");
    exit();
}

$delivery_id = $_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'];

// Get delivery person's orders
$query = "SELECT users_orders.*, users.username, users.address 
          FROM users_orders 
          JOIN users ON users_orders.u_id = users.u_id 
          WHERE users_orders.delivery_id = ? 
            AND users_orders.status IN ('picked_up', 'on_the_way')";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $delivery_id);
$stmt->execute();
$result = $stmt->get_result();
$active_orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>FOODMANIA - Delivery Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/somanchiu/Keyless-Google-Maps-API@v6.9/mapsJavaScriptAPI.js"></script>
    <style>
        :root {
            --primary-color: #ff6b00;
            --primary-gradient: linear-gradient(45deg, #ff6b00, #ff9500);
            --secondary-color: #ff9500;
            --accent-color: #ffd700;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: #ecf0f1;
            --header-bg: #1a1a1a;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .top-header {
            background-color: var(--header-bg);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .brand-logo {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .dashboard-title {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 500;
            padding-left: 1.5rem;
            border-left: 2px solid var(--primary-color);
        }

        .dashboard {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .map-container {
            background: var(--card-background);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        #map {
            height: 500px;
            width: 100%;
        }

        .orders-container {
            background: var(--card-background);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .orders-header h2 {
            font-size: 1.25rem;
            color: var(--text-primary);
        }

        .order-card {
            background: var(--background-color);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateX(5px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-id {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            background: var(--primary-gradient);
            color: white;
            letter-spacing: 0.5px;
        }

        .status-picked_up {
            background-color: var(--warning-color);
            color: white;
        }

        .status-on_the_way {
            background-color: var(--success-color);
            color: white;
        }

        .order-details {
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: var(--text-secondary);
        }

        .detail-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .update-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }

        .update-btn i {
            font-size: 1rem;
        }

        .no-orders {
            text-align: center;
            padding: 3rem 2rem;
        }

        .no-orders i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .no-orders h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .no-orders p {
            color: var(--text-secondary);
        }

        .map-error {
            padding: 3rem 2rem;
            text-align: center;
            background-color: var(--background-color);
        }

        .map-error i {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        .logout-btn {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logout-btn:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .dashboard {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .top-header {
                padding: 1rem;
            }

            .logo-text {
                font-size: 1.2rem;
            }
        }

        /* Location Error Popup Styles */
        .location-error-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .error-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .error-content i {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        .error-content p {
            margin: 1rem 0;
            color: var(--text-primary);
            line-height: 1.5;
        }

        .error-content button {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.5rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .error-content button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }
    </style>
</head>
<body>
    <header class="top-header">
        <div class="logo-container">
            <div class="brand-logo">FOODMANIA</div>
            <div class="dashboard-title">Delivery Dashboard</div>
        </div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </button>
    </header>

    <div class="dashboard">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($active_orders); ?></h3>
                    <p>Active Deliveries</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>25 min</h3>
                    <p>Average Delivery Time</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3>4.8</h3>
                    <p>Rating</p>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="map-container">
                <div id="map"></div>
                <div id="map-error" class="map-error" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Unable to load Google Maps. Please check your internet connection and try again.</p>
                </div>
                <button id="random-location-btn" class="update-location-btn" style="right: 180px;">Random Location</button>
            </div>

            <div class="orders-container">
                <div class="orders-header">
                    <h2>Active Orders</h2>
                </div>
                <?php if (empty($active_orders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-inbox"></i>
                        <h3>No Active Deliveries</h3>
                        <p>You don't have any active deliveries at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($active_orders as $order): ?>
                        <div class="order-card" data-order-id="<?php echo $order['o_id']; ?>">
                            <div class="order-header">
                                <span class="order-id">Order #<?php echo $order['o_id']; ?></span>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($order['username']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($order['address']); ?></span>
                                </div>
                            </div>

                            <button class="update-btn" onclick="updateOrderStatus(<?php echo $order['o_id']; ?>, '<?php echo $order['status'] === 'picked_up' ? 'on_the_way' : 'delivered'; ?>')">
                                <i class="fas fa-<?php echo $order['status'] === 'picked_up' ? 'truck' : 'check'; ?>"></i>
                                Mark as <?php echo $order['status'] === 'picked_up' ? 'On The Way' : 'Delivered'; ?>
                            </button>
                            <button class="update-btn" onclick="updateOrderLocation(<?php echo $order['o_id']; ?>)">
                                <i class="fas fa-location-arrow"></i> Update My Location
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let map, currentMarker, customerMarkers = {};
        let watchId = null;
        let mapError = false;
        const socket = io('http://localhost:3000');

        // Initialize map when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document ready, initializing map...');
            initMap();
        });

        // Store customer locations with fallback coordinates
        const customerLocations = {
            <?php foreach ($active_orders as $order): ?>
            <?php 
                // This is a simple way to extract coordinates from the address string
                // Format should be: "address_text [lat,lng]" or just "address_text"
                $address = $order['address'];
                $coords = null;
                if (preg_match('/\[([-\d.]+),([-\d.]+)\]/', $address, $matches)) {
                    $coords = ['lat' => floatval($matches[1]), 'lng' => floatval($matches[2])];
                    $address = trim(preg_replace('/\[[-\d.,]+\]/', '', $address));
                }
            ?>
            <?php echo $order['o_id']; ?>: {
                address: "<?php echo addslashes($address); ?>",
                lat: <?php echo $coords ? $coords['lat'] : 'null'; ?>,
                lng: <?php echo $coords ? $coords['lng'] : 'null'; ?>
            },
            <?php endforeach; ?>
        };

        function initMap() {
            try {
                console.log('Initializing map...');
                
                // Get delivery person's location first
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const initialPos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            setupMap(initialPos);
                            startTracking();
                        },
                        (error) => {
                            console.error('Geolocation error:', error);
                            // If can't get delivery location, center on first customer or default location
                            const firstCustomer = Object.values(customerLocations)[0];
                            if (firstCustomer && firstCustomer.lat) {
                                setupMap({ lat: firstCustomer.lat, lng: firstCustomer.lng });
                            } else {
                                setupMap({ lat: 12.9716, lng: 77.5946 }); // Default to Bangalore
                            }
                            showLocationError("Please enable location services to track your position.");
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 30000,
                            maximumAge: 0
                        }
                    );
                }
            } catch (error) {
                console.error('Critical map error:', error);
                document.getElementById('map').style.display = 'none';
                document.getElementById('map-error').style.display = 'block';
                mapError = true;
            }
        }

        function setupMap(initialPosition) {
            const mapStyle = [
                {
                    "featureType": "poi",
                    "elementType": "labels",
                    "stylers": [{ "visibility": "off" }]
                },
                {
                    "featureType": "transit",
                    "elementType": "labels",
                    "stylers": [{ "visibility": "off" }]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [
                        { "color": "#E3F2FD" }
                    ]
                },
                {
                    "featureType": "landscape",
                    "elementType": "geometry",
                    "stylers": [
                        { "color": "#F5F5F5" }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [
                        { "color": "#FFFFFF" }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.stroke",
                    "stylers": [
                        { "color": "#E0E0E0" }
                    ]
                }
            ];

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: initialPosition,
                styles: mapStyle,
                disableDefaultUI: true,
                zoomControl: true,
                mapTypeControl: false,
                scaleControl: true,
                streetViewControl: false,
                rotateControl: false,
                fullscreenControl: true
            });

            // Create delivery person marker (blue dot)
            const deliveryMarkerIcon = {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: '#4F46E5',
                fillOpacity: 1,
                strokeColor: '#FFFFFF',
                strokeWeight: 2,
                scale: 8
            };

            currentMarker = new google.maps.Marker({
                position: initialPosition,
                map: map,
                icon: deliveryMarkerIcon,
                title: 'Your Location'
            });

            // Create customer markers (red pins)
            const customerMarkerIcon = {
                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            };

            // Add customer markers only if they have coordinates
            Object.keys(customerLocations).forEach(orderId => {
                const location = customerLocations[orderId];
                if (location.lat && location.lng) {
                    const marker = new google.maps.Marker({
                        position: { lat: location.lat, lng: location.lng },
                        map: map,
                        icon: customerMarkerIcon,
                        title: `Order #${orderId} - Customer Location`
                    });

                    // Add info window for customer marker
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="padding: 10px;">
                                <h3 style="margin: 0 0 5px 0;">Order #${orderId}</h3>
                                <p style="margin: 0;">${location.address}</p>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    customerMarkers[orderId] = marker;
                }
            });

            // Fit map bounds to show all markers
            const bounds = new google.maps.LatLngBounds();
            if (currentMarker) bounds.extend(currentMarker.getPosition());
            Object.values(customerMarkers).forEach(marker => {
                bounds.extend(marker.getPosition());
            });
            if (Object.keys(customerMarkers).length > 0 || currentMarker) {
                map.fitBounds(bounds);
            }

            document.getElementById('map-error').style.display = 'none';
            document.getElementById('map').style.display = 'block';
            mapError = false;
        }

        function startTracking() {
            if (!navigator.geolocation) {
                showLocationError('Geolocation is not supported by your browser');
                return;
            }

            console.log('Starting location tracking...');
            
            // Clear existing watch if any
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    console.log('New position received:', position);
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    if (!mapError && map && currentMarker) {
                        console.log('Updating marker position:', pos);
                        currentMarker.setPosition(pos);
                        map.panTo(pos);
                    }

                    socket.emit('update-location', {
                        latitude: pos.lat,
                        longitude: pos.lng,
                        deliveryId: <?php echo $delivery_id; ?>
                    });
                },
                (error) => {
                    console.error('Tracking error:', error);
                    let errorMessage = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Please enable location access in both your browser and system settings.";
                            // Try to detect if running in HTTP
                            if (window.location.protocol === 'http:') {
                                errorMessage += " Note: Location services require HTTPS. Please use a secure connection.";
                            }
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Cannot detect your location. Please check if GPS is enabled and you have a clear view of the sky.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "Location request timed out. Please check your internet connection and GPS signal.";
                            break;
                        default:
                            errorMessage = "Location error: " + error.message;
                            break;
                    }
                    showLocationError(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 60000,
                    maximumAge: 0
                }
            );
        }

        function updateOrderStatus(orderId, newStatus) {
            const btn = document.querySelector(`.order-card[data-order-id="${orderId}"] .update-btn`);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            $.ajax({
                url: 'update_order_status.php',
                method: 'POST',
                data: {
                    order_id: orderId,
                    status: newStatus
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        const orderCard = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
                        if (orderCard) {
                            if (newStatus === 'delivered') {
                                orderCard.style.opacity = '0';
                                setTimeout(() => {
                                    orderCard.remove();
                                    if (document.querySelectorAll('.order-card').length === 0) {
                                        location.reload();
                                    }
                                }, 300);
                            } else {
                                const statusBadge = orderCard.querySelector('.status-badge');
                                statusBadge.className = `status-badge status-${newStatus}`;
                                statusBadge.textContent = newStatus.split('_').map(word => 
                                    word.charAt(0).toUpperCase() + word.slice(1)
                                ).join(' ');
                                
                                btn.innerHTML = '<i class="fas fa-check"></i> Mark as Delivered';
                                btn.onclick = () => updateOrderStatus(orderId, 'delivered');
                            }
                        }
                        
                        socket.emit('status-updated', {
                            order_id: orderId,
                            status: newStatus
                        });
                    } else {
                        alert('Failed to update order status: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = `<i class="fas fa-${newStatus === 'delivered' ? 'check' : 'truck'}"></i> Mark as ${newStatus === 'delivered' ? 'Delivered' : 'On The Way'}`;
                    }
                },
                error: function() {
                    alert('Failed to update order status. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = `<i class="fas fa-${newStatus === 'delivered' ? 'check' : 'truck'}"></i> Mark as ${newStatus === 'delivered' ? 'Delivered' : 'On The Way'}`;
                }
            });
        }

        function updateOrderLocation(orderId) {
            // Generate random coordinates near Bangalore
            const lat = 12.9716 + (Math.random() - 0.5) * 0.1;
            const lng = 77.5946 + (Math.random() - 0.5) * 0.1;

            const btn = document.querySelector(`.order-card[data-order-id="${orderId}"] .update-btn`);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            fetch('update_location.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `order_id=${orderId}&latitude=${lat}&longitude=${lng}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update the marker on the map
                    if (!mapError && map) {
                        const pos = { lat: lat, lng: lng };
                        currentMarker.setPosition(pos);
                        map.panTo(pos);
                    }
                    btn.innerHTML = '<i class="fas fa-check"></i> Location Updated';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Update My Location';
                        btn.disabled = false;
                    }, 2000);
                } else {
                    btn.innerHTML = '<i class="fas fa-location-arrow"></i> Update My Location';
                    btn.disabled = false;
                    alert('Error: ' + (data.message || 'Failed to update location'));
                }
            })
            .catch(error => {
                btn.innerHTML = '<i class="fas fa-location-arrow"></i> Update My Location';
                btn.disabled = false;
                alert('Network error. Please try again.');
            });
        }

        function showLocationError(message) {
            console.error('Location Error:', message);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'location-error-popup';
            errorDiv.innerHTML = `
                <div class="error-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${message}</p>
                    <div class="error-buttons">
                        <button onclick="retryLocation()">Try Again</button>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(errorDiv);
        }

        function retryLocation() {
            console.log('Retrying location...');
            // Remove existing error popup
            const existingPopup = document.querySelector('.location-error-popup');
            if (existingPopup) {
                existingPopup.remove();
            }
            // Restart location services
            initMap();
        }

        window.gm_authFailure = function() {
            document.getElementById('map').style.display = 'none';
            document.getElementById('map-error').style.display = 'block';
            mapError = true;
        };

        document.getElementById('random-location-btn').addEventListener('click', function() {
            // Generate random coordinates near Bangalore
            const lat = 12.9716 + (Math.random() - 0.5) * 0.1;
            const lng = 77.5946 + (Math.random() - 0.5) * 0.1;
            const location = {
                order_id: orderId,
                lat: lat,
                lng: lng,
                timestamp: new Date().toISOString()
            };

            // Update marker on map
            deliveryMarker.setPosition({ lat: location.lat, lng: location.lng });
            map.panTo({ lat: location.lat, lng: location.lng });

            // Send to server (if you want to simulate backend update)
            if (typeof socket !== 'undefined') {
                socket.emit('update_delivery_location', location);
            }

            // Update UI
            document.getElementById('current-location').textContent =
                `${location.lat.toFixed(6)}, ${location.lng.toFixed(6)}`;
            document.getElementById('last-updated').textContent =
                new Date().toLocaleTimeString();
        });
    </script>
</body>
</html> 