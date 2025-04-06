<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = intval($_SESSION['user_id']);
$message = "";

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);

    // Update booking status to cancelled
    $update = $conn->query("UPDATE bookings SET booking_status = 'cancelled' WHERE id = $booking_id");

    if ($update) {
        // Restore seat count
        $conn->query("UPDATE rides r 
                      JOIN bookings b ON r.id = b.ride_id 
                      SET r.seats_available = r.seats_available + b.seats_booked 
                      WHERE b.id = $booking_id");
        $message = "Booking cancelled successfully!";
    } else {
        $message = "Error cancelling booking.";
    }
}

// Fetch passenger's booked rides
$sql = "SELECT b.id AS booking_id, r.pickup_location, r.drop_location, r.travel_date, r.travel_time, r.fare, 
        u.name AS driver_name, b.booking_status 
        FROM bookings b
        JOIN rides r ON b.ride_id = r.id
        JOIN users u ON r.driver_id = u.id
        WHERE b.passenger_id = $passenger_id
        ORDER BY FIELD(b.booking_status, 'pending', 'confirmed', 'completed', 'cancelled')";

$result = $conn->query($sql);

if (!$result) {
    die("Query Error: " . $conn->error);
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
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($booking = $result->fetch_assoc()) { ?>
                <div class="ride-card">
                    <p><strong>From:</strong> <?= htmlspecialchars($booking['pickup_location']) ?></p>
                    <p><strong>To:</strong> <?= htmlspecialchars($booking['drop_location']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($booking['travel_date']) ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($booking['travel_time']) ?></p>
                    <p><strong>Fare:</strong> ₹<?= htmlspecialchars($booking['fare']) ?></p>
                    <p><strong>Driver:</strong> <?= htmlspecialchars($booking['driver_name']) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($booking['booking_status'])) ?></p>

                    <?php if ($booking['booking_status'] === 'pending') { ?>
                        <form method="POST">
                            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                            <button type="submit" name="cancel_booking" class="cancel-btn">Cancel Booking</button>
                        </form>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No bookings found.</p>
        <?php } ?>
    </div>
</body>
</html>
