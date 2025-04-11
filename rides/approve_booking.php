<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch ride requests for the logged-in driver
$result = $conn->query("SELECT b.id AS booking_id, r.source, r.destination, r.date, r.time, u.name AS passenger_name, b.status 
                        FROM bookings b
                        JOIN rides r ON b.ride_id = r.id
                        JOIN users u ON b.passenger_id = u.id
                        WHERE r.driver_id = $driver_id AND b.status = 'pending'");

$message = "";

// Handle approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update = $conn->query("UPDATE bookings SET status = 'approved' WHERE id = $booking_id");
        $message = $update ? "Booking approved!" : "Error updating status.";
    } elseif ($action === 'reject') {
        $update = $conn->query("UPDATE bookings SET status = 'rejected' WHERE id = $booking_id");
        if ($update) {
            // Restore seat count if rejected
            $conn->query("UPDATE rides r 
                          JOIN bookings b ON r.id = b.ride_id 
                          SET r.seats_available = r.seats_available + 1 
                          WHERE b.id = $booking_id");
            $message = "Booking rejected!";
        } else {
            $message = "Error updating status.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Bookings - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/user_header.php'; ?>
    <div class="ride-approval-container">
        <h2>Ride Booking Requests</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <?php while ($booking = $result->fetch_assoc()) { ?>
            <div class="ride-card">
                <p><strong>Passenger:</strong> <?= $booking['passenger_name'] ?></p>
                <p><strong>From:</strong> <?= $booking['source'] ?></p>
                <p><strong>To:</strong> <?= $booking['destination'] ?></p>
                <p><strong>Date:</strong> <?= $booking['date'] ?></p>
                <p><strong>Time:</strong> <?= $booking['time'] ?></p>
                <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                <?php if ($booking['status'] === 'pending') { ?>
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                        <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</body>
</html>
