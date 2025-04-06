<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if driver exists
$driver_query = $conn->query("SELECT id FROM drivers WHERE user_id = $user_id");
$driver = $driver_query->fetch_assoc();

if (!$driver) {
    // Create a new driver profile with default values
    $defaultIdProof = '';
    $defaultLicense = '';
    $defaultVehicleNumber = 'UNKNOWN';
    $defaultVehicleModel = 'Not Specified';

    $insert_driver = $conn->prepare("INSERT INTO drivers (user_id, id_proof, driving_license, vehicle_number, vehicle_model, verified) 
                                     VALUES (?, ?, ?, ?, ?, 'approved')");
    $insert_driver->bind_param("issss", $user_id, $defaultIdProof, $defaultLicense, $defaultVehicleNumber, $defaultVehicleModel);
    
    if ($insert_driver->execute()) {
        $driver_id = $insert_driver->insert_id;
    } else {
        die("Error creating driver profile: " . $conn->error);
    }
    $insert_driver->close();
} else {
    $driver_id = $driver['id'];
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pickup = trim($_POST['source']);
    $drop = trim($_POST['destination']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $seats = intval($_POST['seats']);
    $fare = floatval($_POST['fare']);

    if (!empty($pickup) && !empty($drop) && $seats > 0 && $fare > 0) {
        $stmt = $conn->prepare("INSERT INTO rides (driver_id, pickup_location, drop_location, travel_date, travel_time, seats_available, fare, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("issssii", $driver_id, $pickup, $drop, $date, $time, $seats, $fare);
        
        if ($stmt->execute()) {
            $message = "Ride posted successfully!";
        } else {
            $message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a Ride - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-form-container">
        <h2>Post a Ride</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST">
            <label>Pickup Location:</label>
            <input type="text" name="source" required>

            <label>Drop Location:</label>
            <input type="text" name="destination" required>

            <label>Date:</label>
            <input type="date" name="date" required>

            <label>Time:</label>
            <input type="time" name="time" required>

            <label>Seats Available:</label>
            <input type="number" name="seats" min="1" required>

            <label>Fare per Seat (₹):</label>
            <input type="number" name="fare" min="1" required>

            <button type="submit">Post Ride</button>
        </form>
    </div>
</body>
</html>
