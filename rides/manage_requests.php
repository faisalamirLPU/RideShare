<?php
session_start();
require_once "../database/db_config.php";

// Check if the user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$message = "";

// Handle status update (approve/cancel)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    // Update booking status
    $update = $conn->prepare("UPDATE bookings SET booking_status = ? WHERE id = ?");
    $update->bind_param("si", $new_status, $booking_id);
    if ($update->execute()) {
        $message = "Booking status updated successfully.";
    } else {
        $message = "Failed to update booking status.";
    }
    $update->close();
}

// Get ride_id from query string
if (!isset($_GET['ride_id'])) {
    die("Ride ID not specified.");
}
$ride_id = $_GET['ride_id'];

// Confirm that this ride belongs to the logged-in driver
$check = $conn->prepare("SELECT id FROM rides WHERE id = ? AND driver_id = ?");
$check->bind_param("ii", $ride_id, $driver_id);
$check->execute();
$result = $check->get_result();
if ($result->num_rows === 0) {
    die("Unauthorized access to ride bookings.");
}
$check->close();

// Fetch all booking requests for this ride
$stmt = $conn->prepare("
    SELECT b.id, b.seats_booked, b.booking_status, b.payment_status, b.created_at,
           u.name AS passenger_name, u.email
    FROM bookings b
    JOIN users u ON b.passenger_id = u.id
    WHERE b.ride_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $ride_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Booking Requests</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include "../includes/user_header.php"; ?>

<div class="dashboard-container">
    <h2>Manage Booking Requests</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <?php if ($bookings->num_rows > 0): ?>
        <table>
            <tr>
                <th>Passenger</th>
                <th>Email</th>
                <th>Seats</th>
                <th>Booking Status</th>
                <th>Payment</th>
                <th>Requested On</th>
                <th>Action</th>
            </tr>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['passenger_name']) ?></td>
                    <td><?= htmlspecialchars($booking['email']) ?></td>
                    <td><?= $booking['seats_booked'] ?></td>
                    <td><?= ucfirst($booking['booking_status']) ?></td>
                    <td><?= ucfirst($booking['payment_status']) ?></td>
                    <td><?= $booking['created_at'] ?></td>
                    <td>
                        <?php if ($booking['booking_status'] === 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <input type="hidden" name="new_status" value="confirmed">
                                <button type="submit">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <input type="hidden" name="new_status" value="cancelled">
                                <button type="submit">Cancel</button>
                            </form>
                        <?php else: ?>
                            <em>No action needed</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No booking requests yet for this ride.</p>
    <?php endif; ?>
</div>

<?php include "../includes/user_footer.php"; ?>

</body>
</html>
