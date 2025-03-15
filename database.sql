-- CREATE DATABASE rideshare_db;
-- USE rideshare_db;

-- Users Table (Stores general user details)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hashed password
    user_type ENUM('passenger', 'driver') NOT NULL DEFAULT 'passenger',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drivers Table (Stores additional details for drivers)
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    id_proof VARCHAR(255) NOT NULL,  -- File path to uploaded ID proof
    driving_license VARCHAR(255) NOT NULL,  -- File path to uploaded driving license
    vehicle_number VARCHAR(50) NOT NULL UNIQUE,
    vehicle_model VARCHAR(100) NOT NULL,
    verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Rides Table (Stores ride details posted by drivers)
CREATE TABLE rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    seats_available INT NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    drop_location VARCHAR(255) NOT NULL,
    travel_date DATE NOT NULL,
    travel_time TIME NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- Bookings Table (Tracks ride bookings made by passengers)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    passenger_id INT NOT NULL,
    seats_booked INT NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Payments Table (Stores transaction details for booked rides)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('UPI', 'cash') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    payment_status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Chatbot Responses Table (Stores predefined chatbot replies)
CREATE TABLE chatbot_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_input VARCHAR(255) UNIQUE NOT NULL,
    bot_response TEXT NOT NULL
);
