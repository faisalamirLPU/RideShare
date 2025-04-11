<?php
session_start();
include '../database/db_config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if driver profile exists
$driver_query = $conn->prepare("SELECT id, verified FROM drivers WHERE user_id = ?");
$driver_query->bind_param("i", $user_id);
$driver_query->execute();
$driver_result = $driver_query->get_result();
$driver = $driver_result->fetch_assoc();
$driver_query->close();

// Redirect to create driver profile if not exists
if (!$driver) {
    header("Location: create_driver_profile.php");
    exit();
}

// If profile is pending or rejected
if ($driver['verified'] !== 'approved') {
    $message = "Your driver profile is currently <strong>{$driver['verified']}</strong>. You cannot post rides until it is approved.";
} else {
    $driver_id = $driver['id'];
    $message = "";

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pickup = trim($_POST['source']);
        $drop = trim($_POST['destination']);
        $date = $_POST['date'];
        $time = $_POST['time'];
        $seats = intval($_POST['seats']);
        $fare = floatval($_POST['fare']);

        if (!empty($pickup) && !empty($drop) && $seats > 0 && $fare > 0) {
            $stmt = $conn->prepare("INSERT INTO rides (driver_id, pickup_location, drop_location, travel_date, travel_time, seats_available, fare, status, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->bind_param("issssii", $driver_id, $pickup, $drop, $date, $time, $seats, $fare);

            if ($stmt->execute()) {
                $message = "✅ Ride posted successfully!";
            } else {
                $message = "❌ Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "⚠️ Please fill all fields correctly.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a Ride - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="ride-form-container">
    <h2>Post a Ride</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <?php if ($driver && $driver['verified'] === 'approved'): ?>
    <form method="POST">
        <label>Pickup Location:</label>
        <input type="text" name="source" required>

        <label>Drop Location:</label>
        <input type="text" name="destination" required>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Time:</label>
        <input type="time" name="time" required>

        <label>Seats Available:</label>
        <input type="number" name="seats" min="1" required>

        <label>Fare per Seat (₹):</label>
        <input type="number" name="fare" min="1" required>

        <button type="submit">Post Ride</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
