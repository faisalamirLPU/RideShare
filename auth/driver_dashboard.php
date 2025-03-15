<?php
session_start();
require_once "../database/db_config.php";

// Check if the user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch rides posted by the driver
$stmt = $conn->prepare("
    SELECT r.id, r.pickup_location, r.drop_location, r.fare, 
           r.seats_available, r.status,
           COUNT(b.id) AS booked_seats
    FROM rides r
    LEFT JOIN bookings b ON r.id = b.ride_id AND b.booking_status = 'confirmed'
    WHERE r.driver_id = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include "../includes/user_header.php"; ?>

<div class="dashboard-container">
    <h2>Welcome, Driver</h2>
    <a href="../rides/post_ride.php" class="btn">Post a New Ride</a>
    
    <h3>Your Rides</h3>
    <table>
        <tr>
            <th>Pickup</th>
            <th>Drop</th>
            <th>Fare</th>
            <th>Seats Available</th>
            <th>Seats Booked</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($ride = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                <td><?= htmlspecialchars($ride['drop_location']) ?></td>
                <td>₹<?= htmlspecialchars($ride['fare']) ?></td>
                <td><?= htmlspecialchars($ride['seats_available']) ?></td>
                <td><?= htmlspecialchars($ride['booked_seats']) ?></td>
                <td>
                    <?php
                    if ($ride['status'] === 'active') {
                        echo '<span class="status active">Active</span>';
                    } elseif ($ride['status'] === 'completed') {
                        echo '<span class="status completed">Completed</span>';
                    } else {
                        echo '<span class="status cancelled">Cancelled</span>';
                    }
                    ?>
                </td>
                <td>
                    <a href="../rides/manage_requests.php?ride_id=<?= $ride['id'] ?>" class="btn">Manage Requests</a>
                    <?php if ($ride['status'] === 'active'): ?>
                        <a href="../rides/edit_ride.php?ride_id=<?= $ride['id'] ?>" class="btn">Edit</a>
                        <a href="../rides/delete_ride.php?ride_id=<?= $ride['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
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
