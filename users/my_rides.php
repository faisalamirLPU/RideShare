<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$message = "";

// Handle ride deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_ride'])) {
    $ride_id = $_POST['ride_id'];
    $delete = $conn->query("DELETE FROM rides WHERE id = $ride_id");
    $message = $delete ? "Ride deleted successfully!" : "Error deleting ride.";
}

// Handle ride closure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['close_ride'])) {
    $ride_id = $_POST['ride_id'];
    $update = $conn->query("UPDATE rides SET status = 'closed' WHERE id = $ride_id");
    $message = $update ? "Ride closed successfully!" : "Error closing ride.";
}

// Fetch driver's posted rides (AFTER handling post)
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
    <title>My Rides - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-list-container">
        <h2>My Posted Rides</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
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
</body>
</html>
