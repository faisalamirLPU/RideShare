<?php
session_start();
require_once "../database/db_config.php";

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch all rides posted by this driver
$query = "SELECT id, pickup_location, drop_location, travel_date, travel_time, seats_available, fare, status FROM rides WHERE driver_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Rides - Choose Ride to Manage</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include "../includes/user_header.php"; ?>

<div class="dashboard-container">
    <h2>Your Rides</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Seats</th>
                    <th>Fare</th>
                    <th>Status</th>
                    <th>Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ride = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($ride['drop_location']) ?></td>
                        <td><?= $ride['travel_date'] ?></td>
                        <td><?= $ride['travel_time'] ?></td>
                        <td><?= $ride['seats_available'] ?></td>
                        <td>₹<?= $ride['fare'] ?></td>
                        <td><?= ucfirst($ride['status']) ?></td>
                        <td>
                            <a href="manage_requests.php?ride_id=<?= $ride['id'] ?>" class="btn">Manage Requests</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't posted any rides yet.</p>
    <?php endif; ?>
</div>

<?php include "../includes/user_footer.php"; ?>

</body>
</html>
