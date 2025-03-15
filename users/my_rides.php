<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch driver's posted rides
$result = $conn->query("SELECT id, source, destination, date, time, seats_available, fare, status 
                        FROM rides WHERE driver_id = $driver_id ORDER BY date DESC");

$message = "";

// Handle ride deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_ride'])) {
    $ride_id = $_POST['ride_id'];
    $delete = $conn->query("DELETE FROM rides WHERE id = $ride_id");

    if ($delete) {
        $message = "Ride deleted successfully!";
    } else {
        $message = "Error deleting ride.";
    }
}

// Handle ride closure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['close_ride'])) {
    $ride_id = $_POST['ride_id'];
    $update = $conn->query("UPDATE rides SET status = 'closed' WHERE id = $ride_id");

    $message = $update ? "Ride closed successfully!" : "Error closing ride.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rides - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="ride-list-container">
        <h2>My Posted Rides</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <?php while ($ride = $result->fetch_assoc()) { ?>
            <div class="ride-card">
                <p><strong>From:</strong> <?= $ride['source'] ?></p>
                <p><strong>To:</strong> <?= $ride['destination'] ?></p>
                <p><strong>Date:</strong> <?= $ride['date'] ?></p>
                <p><strong>Time:</strong> <?= $ride['time'] ?></p>
                <p><strong>Seats Available:</strong> <?= $ride['seats_available'] ?></p>
                <p><strong>Fare:</strong> ₹<?= $ride['fare'] ?></p>
                <p><strong>Status:</strong> <?= ucfirst($ride['status']) ?></p>
                <?php if ($ride['status'] !== 'closed') { ?>
                    <form method="POST">
                        <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                        <button type="submit" name="close_ride">Close Ride</button>
                        <button type="submit" name="delete_ride" class="delete-btn">Delete Ride</button>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</body>
</html>
