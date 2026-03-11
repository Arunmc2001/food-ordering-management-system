-- Add reset token columns to delivery_persons table
ALTER TABLE delivery_persons 
ADD COLUMN reset_token VARCHAR(64) NULL,
ADD COLUMN reset_token_expiry DATETIME NULL; 