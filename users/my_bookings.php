<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];

// Fetch passenger's booked rides
$result = $conn->query("SELECT b.id AS booking_id, r.source, r.destination, r.date, r.time, r.fare, 
                        u.name AS driver_name, b.status 
                        FROM bookings b
                        JOIN rides r ON b.ride_id = r.id
                        JOIN users u ON r.driver_id = u.id
                        WHERE b.passenger_id = $passenger_id
                        ORDER BY b.status DESC");

$message = "";

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $update = $conn->query("UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id");

    if ($update) {
        // Restore seat count
        $conn->query("UPDATE rides r 
                      JOIN bookings b ON r.id = b.ride_id 
                      SET r.seats_available = r.seats_available + 1 
                      WHERE b.id = $booking_id");
        $message = "Booking cancelled successfully!";
    } else {
        $message = "Error cancelling booking.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-list-container">
        <h2>My Bookings</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <?php while ($booking = $result->fetch_assoc()) { ?>
            <div class="ride-card">
                <p><strong>From:</strong> <?= $booking['source'] ?></p>
                <p><strong>To:</strong> <?= $booking['destination'] ?></p>
                <p><strong>Date:</strong> <?= $booking['date'] ?></p>
                <p><strong>Time:</strong> <?= $booking['time'] ?></p>
                <p><strong>Fare:</strong> ₹<?= $booking['fare'] ?></p>
                <p><strong>Driver:</strong> <?= $booking['driver_name'] ?></p>
                <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                <?php if ($booking['status'] === 'pending') { ?>
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                        <button type="submit" name="cancel_booking" class="cancel-btn">Cancel Booking</button>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</body>
</html>
