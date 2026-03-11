const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql');
const cors = require('cors');

const app = express();
app.use(cors());
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// Database connection
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'food'
});

db.connect((err) => {
  if (err) {
    console.error('Error connecting to database:', err);
    return;
  }
  console.log('Connected to database');
});

// Store active tracking sessions
const activeTracking = new Map();

io.on('connection', (socket) => {
  console.log('Client connected:', socket.id);
  
  // Handle tracking request
  socket.on('track_order', (data) => {
    console.log('Tracking requested for order:', data.order_id);
    
    // Store the socket for this order
    activeTracking.set(data.order_id, socket.id);
    
    // Get initial location from database if available
    db.query(
      'SELECT delivery_lat, delivery_lng FROM users_orders WHERE o_id = ?',
      [data.order_id],
      (err, results) => {
        if (err) {
          console.error('Error fetching order location:', err);
          socket.emit('tracking_error', { message: 'Failed to fetch order location' });
          return;
        }
        
        if (results.length > 0 && results[0].delivery_lat && results[0].delivery_lng) {
          // Send initial location
          socket.emit('tracking_started', {
            order_id: data.order_id,
            initial_location: {
              lat: parseFloat(results[0].delivery_lat),
              lng: parseFloat(results[0].delivery_lng)
            }
          });
        } else {
          // No location yet, just confirm tracking started
          socket.emit('tracking_started', { order_id: data.order_id });
        }
      }
    );
  });
  
  // Handle location updates from delivery person
  socket.on('update_delivery_location', (data) => {
    console.log('Location update received for order:', data.order_id);
    
    // Update database
    db.query(
      'UPDATE users_orders SET delivery_lat = ?, delivery_lng = ?, location_updated_at = NOW() WHERE o_id = ?',
      [data.lat, data.lng, data.order_id],
      (err, result) => {
        if (err) {
          console.error('Error updating location:', err);
          socket.emit('location_update_error', { message: 'Failed to update location' });
          return;
        }
        
        // Broadcast to all clients tracking this order
        io.emit('location_update', {
          order_id: data.order_id,
          lat: data.lat,
          lng: data.lng,
          timestamp: new Date().toISOString()
        });
        
        socket.emit('location_update_success', { message: 'Location updated successfully' });
      }
    );
  });
  
  // Handle disconnection
  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id);
    
    // Remove from active tracking
    for (const [orderId, socketId] of activeTracking.entries()) {
      if (socketId === socket.id) {
        activeTracking.delete(orderId);
        break;
      }
    }
  });
});

// Basic API endpoint to check server status
app.get('/', (req, res) => {
  res.send('Tracking server is running');
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Tracking server running on port ${PORT}`);
}); 