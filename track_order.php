<?php include("connection/connect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $_GET['order_id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .update-location-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .update-location-btn:hover {
            background-color: #45a049;
        }
        .location-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .map-container {
            position: relative;
        }
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Track Order #<?php echo $_GET['order_id']; ?></h4>
                        <div>
                            <span id="connection-status" class="badge bg-secondary">Connecting...</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Order Details</h5>
                                <p><strong>Order ID:</strong> <?php echo $_GET['order_id']; ?></p>
                                <p><strong>Status:</strong> <span id="order-status" class="status-badge bg-info">Loading...</span></p>
                                <p><strong>Last Updated:</strong> <span id="last-updated">-</span></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Delivery Location</h5>
                                <p><strong>Current Location:</strong> <span id="current-location">Waiting for update...</span></p>
                                <div id="location-status" class="location-status"></div>
                            </div>
                        </div>
                        
                        <div class="map-container">
                            <div id="map"></div>
                            <button id="update-location-btn" class="update-location-btn">Update My Location</button>
                            <div id="loading" class="loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Updating location...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/somanchiu/Keyless-Google-Maps-API@v6.9/mapsJavaScriptAPI.js"></script>
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });

    let map;
    let deliveryMarker;
    let customerMarker;
    let directionsService;
    let directionsRenderer;
    let socket;
    const orderId = <?php echo $_GET['order_id']; ?>;
    
    // Initialize Socket.IO connection
    function initializeSocket() {
        socket = io('http://localhost:3000', {
            reconnection: true,
            reconnectionAttempts: 5,
            timeout: 10000
        });
        
        socket.on('connect', () => {
            console.log('Connected to tracking server');
            document.getElementById('connection-status').className = 'badge bg-success';
            document.getElementById('connection-status').textContent = 'Connected';
            
            // Request tracking for this order
            socket.emit('track_order', { 
                order_id: orderId,
                request_time: new Date().toISOString()
            });
        });
        
        socket.on('connect_error', (error) => {
            console.error('Connection error:', error);
            document.getElementById('connection-status').className = 'badge bg-danger';
            document.getElementById('connection-status').textContent = 'Connection Error';
        });
        
        socket.on('disconnect', () => {
            console.log('Disconnected from tracking server');
            document.getElementById('connection-status').className = 'badge bg-warning';
            document.getElementById('connection-status').textContent = 'Disconnected';
        });
        
        socket.on('tracking_started', (data) => {
            console.log('Tracking started for order:', data.order_id);
            if (data.initial_location) {
                updateDeliveryLocation(data);
            }
        });
        
        socket.on('location_update', (data) => {
            if (data.order_id == orderId) {
                updateDeliveryLocation(data);
            }
        });
        
        socket.on('location_update_success', (data) => {
            showLocationStatus('Location updated successfully!', 'success');
        });
        
        socket.on('location_update_error', (data) => {
            showLocationStatus('Error updating location: ' + data.message, 'error');
        });
        
        socket.on('tracking_error', (error) => {
            console.error('Tracking error:', error);
            showLocationStatus('Error tracking order: ' + error.message, 'error');
        });
    }
    
    function showLocationStatus(message, type) {
        const statusElement = document.getElementById('location-status');
        statusElement.textContent = message;
        statusElement.className = 'location-status ' + type;
        statusElement.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 5000);
    }
    
    // Initialize Google Maps
    function initMap() {
        // Default center (can be updated based on order details)
        const defaultLocation = { lat: 12.9716, lng: 77.5946 }; // Bangalore
        
        map = new google.maps.Map(document.getElementById('map'), {
            center: defaultLocation,
            zoom: 12,
            styles: [
                {
                    "featureType": "poi",
                    "elementType": "labels",
                    "stylers": [{ "visibility": "off" }]
                },
                {
                    "featureType": "transit",
                    "elementType": "labels",
                    "stylers": [{ "visibility": "off" }]
                }
            ]
        });
        
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true
        });
        
        // Create markers
        deliveryMarker = new google.maps.Marker({
            map: map,
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                scaledSize: new google.maps.Size(32, 32)
            }
        });
        
        customerMarker = new google.maps.Marker({
            map: map,
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                scaledSize: new google.maps.Size(32, 32)
            }
        });
        
        // Add event listener for update location button
        document.getElementById('update-location-btn').addEventListener('click', updateMyLocation);
        
        // Initialize Socket.IO
        initializeSocket();
    }
    
    function updateMyLocation() {
        const loadingElement = document.getElementById('loading');
        loadingElement.style.display = 'block';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const location = {
                        order_id: orderId,
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        timestamp: new Date().toISOString()
                    };
                    
                    // Update marker on map
                    deliveryMarker.setPosition({ lat: location.lat, lng: location.lng });
                    map.panTo({ lat: location.lat, lng: location.lng });
                    
                    // Send to server
                    socket.emit('update_delivery_location', location);
                    
                    // Update UI
                    document.getElementById('current-location').textContent = 
                        `${location.lat.toFixed(6)}, ${location.lng.toFixed(6)}`;
                    document.getElementById('last-updated').textContent = 
                        new Date().toLocaleTimeString();
                    
                    loadingElement.style.display = 'none';
                },
                function(error) {
                    console.error('Error getting location:', error);
                    showLocationStatus('Error getting location: ' + error.message, 'error');
                    loadingElement.style.display = 'none';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            showLocationStatus('Geolocation is not supported by this browser.', 'error');
            loadingElement.style.display = 'none';
        }
    }
    
    function updateDeliveryLocation(data) {
        if (!data.lat || !data.lng) return;
        
        const position = { lat: data.lat, lng: data.lng };
        
        // Update delivery marker
        deliveryMarker.setPosition(position);
        
        // If we have customer location, draw route
        if (customerMarker.getPosition()) {
            calculateAndDisplayRoute(position, customerMarker.getPosition());
        }
        
        // Update UI
        document.getElementById('current-location').textContent = 
            `${data.lat.toFixed(6)}, ${data.lng.toFixed(6)}`;
        document.getElementById('last-updated').textContent = 
            new Date(data.timestamp).toLocaleTimeString();
    }
    
    function calculateAndDisplayRoute(origin, destination) {
        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            },
            (response, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                } else {
                    console.error('Directions request failed:', status);
                }
            }
        );
    }
    </script>
</body>
</html>