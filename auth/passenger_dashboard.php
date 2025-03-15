<?php
session_start();
require_once "../database/db_config.php";

// Check if the user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch booked rides
$stmt = $conn->prepare("
    SELECT r.pickup_location, r.drop_location, r.fare, 
           b.seats_booked, b.booking_status, b.payment_status,
           d.vehicle_model, u.name AS driver_name, u.phone AS driver_phone
    FROM bookings b
    JOIN rides r ON b.ride_id = r.id
    JOIN drivers d ON r.driver_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE b.passenger_id = ?
    ORDER BY b.created_at DESC");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include "../includes/user_header.php"; ?>

<div class="dashboard-container">
    <h2>Welcome, Passenger</h2>

    <h3>Your Booked Rides</h3>
    <table>
        <tr>
            <th>Pickup</th>
            <th>Drop</th>
            <th>Fare</th>
            <th>Seats Booked</th>
            <th>Driver</th>
            <th>Vehicle</th>
            <th>Contact</th>
            <th>Booking Status</th>
            <th>Payment Status</th>
            <th>Action</th>
        </tr>
        <?php while ($ride = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                <td><?= htmlspecialchars($ride['drop_location']) ?></td>
                <td>₹<?= htmlspecialchars($ride['fare']) ?></td>
                <td><?= htmlspecialchars($ride['seats_booked']) ?></td>
                <td><?= htmlspecialchars($ride['driver_name']) ?></td>
                <td><?= htmlspecialchars($ride['vehicle_model']) ?></td>
                <td><?= htmlspecialchars($ride['driver_phone']) ?></td>
                <td>
                    <?php
                    if ($ride['booking_status'] === 'confirmed') {
                        echo '<span class="status confirmed">Confirmed</span>';
                    } elseif ($ride['booking_status'] === 'pending') {
                        echo '<span class="status pending">Pending</span>';
                    } else {
                        echo '<span class="status cancelled">Cancelled</span>';
                    }
                    ?>
                </td>
                <td>
                    <?= $ride['payment_status'] === 'paid' ? '<span class="status paid">Paid</span>' : '<span class="status unpaid">Unpaid</span>' ?>
                </td>
                <td>
                    <?php if ($ride['booking_status'] === 'confirmed'): ?>
                        <a href="../rides/cancel_booking.php?ride_id=<?= $ride['id'] ?>" class="btn btn-danger" onclick="return confirm('Cancel this ride?');">Cancel</a>
                    <?php else: ?>
                        <span>---</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/user_footer.php"; ?>

</body>
</html>
