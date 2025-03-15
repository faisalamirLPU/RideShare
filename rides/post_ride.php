<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and verified as a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Check if the driver is verified
$result = $conn->query("SELECT verified FROM drivers WHERE user_id = $driver_id");
$driver = $result->fetch_assoc();
if ($driver['verified'] !== 'approved') {
    die("Your account is not verified yet. Please wait for admin approval.");
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = trim($_POST['source']);
    $destination = trim($_POST['destination']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $seats = intval($_POST['seats']);
    $fare = floatval($_POST['fare']);

    if (!empty($source) && !empty($destination) && $seats > 0 && $fare > 0) {
        $stmt = $conn->prepare("INSERT INTO rides (driver_id, source, destination, date, time, seats_available, fare) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssii", $driver_id, $source, $destination, $date, $time, $seats, $fare);
        
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Ride - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-form-container">
        <h2>Post a Ride</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST">
            <label>Source Location:</label>
            <input type="text" name="source" required>

            <label>Destination:</label>
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
