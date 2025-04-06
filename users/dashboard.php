<?php
session_start();
require_once '../database/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql_user = "SELECT name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Fetch upcoming bookings
$sql_bookings = "SELECT r.pickup_location, r.drop_location, r.travel_date, r.travel_time, 
                        b.payment_status, b.seats_booked
                 FROM bookings b
                 JOIN rides r ON b.ride_id = r.id
                 WHERE b.passenger_id = ? AND b.booking_status = 'confirmed'
                 ORDER BY r.travel_date ASC";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $user_id);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

// ✅ FIX: Changed `available_seats` to `seats_available`
$sql_rides = "SELECT pickup_location, drop_location, travel_date, travel_time, seats_available 
              FROM rides WHERE driver_id = ? ORDER BY travel_date ASC";
$stmt_rides = $conn->prepare($sql_rides);
$stmt_rides->bind_param("i", $user_id);
$stmt_rides->execute();
$result_rides = $stmt_rides->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/user_header.php'; ?>

    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>

        <div class="dashboard-actions">
            <a href="../rides/book_ride.php" class="dashboard-btn">Book a Ride</a>
            <a href="../rides/post_ride.php" class="dashboard-btn">Post a Ride</a>
        </div>

        <div class="dashboard-section">
            <h3>🚗 Your Upcoming Bookings</h3>
            <?php if ($result_bookings->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Pickup</th>
                        <th>Drop</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seats</th>
                        <th>Payment</th>
                    </tr>
                    <?php while ($booking = $result_bookings->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['drop_location']); ?></td>
                            <td><?php echo $booking['travel_date']; ?></td>
                            <td><?php echo $booking['travel_time']; ?></td>
                            <td><?php echo $booking['seats_booked']; ?></td>
                            <td><?php echo ucfirst($booking['payment_status']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No upcoming bookings.</p>
            <?php } ?>
        </div>

        <div class="dashboard-section">
            <h3>🛣️ Rides You Have Posted</h3>
            <?php if ($result_rides->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Pickup</th>
                        <th>Drop</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seats Available</th>
                    </tr>
                    <?php while ($ride = $result_rides->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ride['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($ride['drop_location']); ?></td>
                            <td><?php echo $ride['travel_date']; ?></td>
                            <td><?php echo $ride['travel_time']; ?></td>
                            <td><?php echo $ride['seats_available']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>You have not posted any rides.</p>
            <?php } ?>
        </div>
    </div>

    <?php include '../includes/user_footer.php'; ?>
</body>
</html>
