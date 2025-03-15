<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];

// Fetch available rides
$result = $conn->query("SELECT r.id, r.source, r.destination, r.date, r.time, r.seats_available, r.fare, 
                        u.name AS driver_name, d.id_proof, d.driving_license 
                        FROM rides r 
                        JOIN users u ON r.driver_id = u.id 
                        JOIN drivers d ON r.driver_id = d.user_id
                        WHERE r.seats_available > 0");

$message = "";

// Handle booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ride_id'])) {
    $ride_id = $_POST['ride_id'];

    // Check available seats
    $seatCheck = $conn->query("SELECT seats_available FROM rides WHERE id = $ride_id");
    $ride = $seatCheck->fetch_assoc();
    
    if ($ride['seats_available'] > 0) {
        // Insert booking request
        $stmt = $conn->prepare("INSERT INTO bookings (ride_id, passenger_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $ride_id, $passenger_id);
        
        if ($stmt->execute()) {
            // Decrease available seats
            $conn->query("UPDATE rides SET seats_available = seats_available - 1 WHERE id = $ride_id");
            $message = "Booking request sent to the driver!";
        } else {
            $message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Sorry, no seats available!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Ride - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-list-container">
        <h2>Available Rides</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <?php while ($ride = $result->fetch_assoc()) { ?>
            <div class="ride-card">
                <p><strong>From:</strong> <?= $ride['source'] ?></p>
                <p><strong>To:</strong> <?= $ride['destination'] ?></p>
                <p><strong>Date:</strong> <?= $ride['date'] ?></p>
                <p><strong>Time:</strong> <?= $ride['time'] ?></p>
                <p><strong>Seats Available:</strong> <?= $ride['seats_available'] ?></p>
                <p><strong>Fare:</strong> ₹<?= $ride['fare'] ?></p>
                <p><strong>Driver:</strong> <?= $ride['driver_name'] ?></p>
                <p><strong>ID Proof:</strong> <a href="<?= $ride['id_proof'] ?>" target="_blank">View</a></p>
                <p><strong>License:</strong> <a href="<?= $ride['driving_license'] ?>" target="_blank">View</a></p>
                <form method="POST">
                    <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                    <button type="submit">Book Seat</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>
