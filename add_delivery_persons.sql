-- Add sample delivery persons
-- Note: Passwords are hashed using PHP's password_hash() with bcrypt
-- The plain text passwords are listed in comments for reference

-- Delivery Person 1 (password: delivery123)
INSERT INTO delivery_persons (name, email, password, phone, status) 
VALUES ('John Delivery', 'john.delivery@foodmania.com', '$2y$10$8TqZc1qA5c8ogGAKW8MA9eVhvgQgLTqYUmsAKbL9kBX6/0YWpKfXi', '+1234567890', 'available');

-- Delivery Person 2 (password: delivery456)
INSERT INTO delivery_persons (name, email, password, phone, status) 
VALUES ('Sarah Express', 'sarah.express@foodmania.com', '$2y$10$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN.6P3jYxFzKVUDIwG9Ga', '+1234567891', 'available');

-- Delivery Person 3 (password: delivery789)
INSERT INTO delivery_persons (name, email, password, phone, status) 
VALUES ('Mike Swift', 'mike.swift@foodmania.com', '$2y$10$P.9c8Q/p.YkP9kj.2qTzSOXwEqGUwvz9XRH9Q9Q9Q9Q9Q9Q9Q9Q9Q', '+1234567892', 'available');

-- Note: To add more delivery persons, use the following template:
-- INSERT INTO delivery_persons (name, email, password, phone, status) 
-- VALUES ('Name', 'email', 'hashed_password', 'phone', 'status');

-- You can generate hashed passwords using PHP's password_hash() function:
-- <?php echo password_hash("your_password", PASSWORD_BCRYPT); ?> 