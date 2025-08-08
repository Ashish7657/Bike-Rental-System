-- Add latitude and longitude columns to bikes table
ALTER TABLE bikes
ADD COLUMN latitude DOUBLE NOT NULL DEFAULT 0,
ADD COLUMN longitude DOUBLE NOT NULL DEFAULT 0;

-- Optionally, add latitude and longitude columns to rentals table if you want to store user location
ALTER TABLE rentals
ADD COLUMN user_latitude DOUBLE DEFAULT NULL,
ADD COLUMN user_longitude DOUBLE DEFAULT NULL,
ADD COLUMN distance_km DOUBLE DEFAULT NULL;
