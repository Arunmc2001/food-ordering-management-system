# Food Delivery Tracking System

This system allows delivery personnel to update their location in real-time, which is then displayed to both customers and administrators.

## Components

1. **Node.js Tracking Server** (`tracking_server.js`)
   - Handles real-time location updates
   - Stores location data in the database
   - Broadcasts updates to connected clients

2. **Delivery Boy Interface** (`track_order.php`)
   - Allows delivery personnel to update their location
   - Shows their current position on a map
   - Displays order details

3. **Admin Tracking Interface** (`admin/track_delivery.php`)
   - Allows administrators to track delivery personnel
   - Shows delivery route and estimated arrival time
   - Displays order and customer details

4. **Customer Tracking Interface** (existing `track_order.php`)
   - Allows customers to track their order
   - Shows delivery personnel's location
   - Displays estimated arrival time

## Setup Instructions

### 1. Database Setup

Run the SQL commands in `update_database.sql` to add the necessary fields to your database:

```sql
ALTER TABLE orders 
ADD COLUMN delivery_lat DECIMAL(10, 8) DEFAULT NULL,
ADD COLUMN delivery_lng DECIMAL(11, 8) DEFAULT NULL,
ADD COLUMN location_updated_at DATETIME DEFAULT NULL;
```

### 2. Node.js Server Setup

1. Install Node.js if not already installed
2. Navigate to the project directory
3. Install dependencies:
   ```
   npm install
   ```
4. Start the server:
   ```
   npm start
   ```
5. The server will run on port 3000 by default

### 3. Google Maps API

The system uses a keyless Google Maps API solution that doesn't require an API key. This is implemented using the following script:

```html
<script src="https://cdn.jsdelivr.net/gh/somanchiu/Keyless-Google-Maps-API@v6.9/mapsJavaScriptAPI.js"></script>
```

This approach allows you to use Google Maps without directly providing an API key in your code. If you prefer to use your own Google Maps API key, you can replace this script with:

```html
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
```

And make sure your API key has the following APIs enabled:
- Maps JavaScript API
- Geocoding API
- Directions API

## Usage

### For Delivery Personnel

1. Navigate to `track_order.php?order_id=X` where X is the order ID
2. Click the "Update My Location" button to send your current location
3. The map will update to show your position

### For Administrators

1. Navigate to `admin/all_orders.php`
2. Click the "Track Delivery" button next to an order
3. The tracking page will show the delivery personnel's location and route

### For Customers

1. Navigate to `track_order.php?order_id=X` where X is their order ID
2. The page will show the delivery personnel's location and estimated arrival time

## Troubleshooting

1. **Server Not Connecting**
   - Make sure the Node.js server is running
   - Check that port 3000 is not blocked by a firewall
   - Verify that the Socket.IO client is connecting to the correct URL

2. **Location Not Updating**
   - Check browser console for errors
   - Make sure geolocation is enabled in the browser
   - Verify that the delivery person has granted location permissions

3. **Map Not Loading**
   - Check that the Google Maps API key is valid
   - Verify that the necessary APIs are enabled
   - Check browser console for API errors

## Security Considerations

1. The tracking server should be secured in a production environment
2. Consider implementing authentication for the tracking server
3. Use HTTPS for all connections in production
4. Implement rate limiting to prevent abuse

## Future Enhancements

1. Add push notifications for location updates
2. Implement a mobile app for delivery personnel
3. Add historical tracking data
4. Implement geofencing for delivery zones
5. Add estimated time of arrival calculations 