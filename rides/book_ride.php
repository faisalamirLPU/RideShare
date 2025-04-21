<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$message = "";

// Handle booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ride_id'])) {
    $ride_id = intval($_POST['ride_id']);

    // Check seat availability
    $seatCheck = $conn->prepare("SELECT seats_available FROM rides WHERE id = ? AND status = 'active'");
    $seatCheck->bind_param("i", $ride_id);
    $seatCheck->execute();
    $result = $seatCheck->get_result();
    $ride = $result->fetch_assoc();
    $seatCheck->close();

    if ($ride && $ride['seats_available'] > 0) {
        $seats_booked = 1;
        $booking_status = 'pending';
        $payment_status = 'pending'; // Ensuring value is set correctly

        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (ride_id, passenger_id, seats_booked, booking_status, payment_status, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())");

        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $bind = $stmt->bind_param("iiiss", $ride_id, $passenger_id, $seats_booked, $booking_status, $payment_status);
        if ($bind === false) {
            die("Bind failed: " . $stmt->error);
        }

        if ($stmt->execute()) {
            // Reduce available seat count
            $update = $conn->prepare("UPDATE rides SET seats_available = seats_available - 1 WHERE id = ?");
            $update->bind_param("i", $ride_id);
            $update->execute();
            $update->close();

            $message = "Booking request sent to the driver!";
        } else {
            $message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Sorry, no seats available!";
    }
}

// Fetch available rides
$query = "SELECT r.id, r.pickup_location, r.drop_location, r.travel_date, r.travel_time, r.seats_available, r.fare,
                 u.name AS driver_name, d.id_proof, d.driving_license
          FROM rides r
          JOIN drivers d ON r.driver_id = d.id
          JOIN users u ON d.user_id = u.id
          WHERE r.seats_available > 0 AND r.status = 'active'
          ORDER BY r.travel_date, r.travel_time";

$result = $conn->query($query);
if (!$result) {
    die("Error fetching rides: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Ride - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="ride-list-container">
    <h2>Available Rides</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($ride = $result->fetch_assoc()): ?>
            <div class="ride-card">
                <p><strong>From:</strong> <?= htmlspecialchars($ride['pickup_location']) ?></p>
                <p><strong>To:</strong> <?= htmlspecialchars($ride['drop_location']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($ride['travel_date']) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($ride['travel_time']) ?></p>
                <p><strong>Seats Available:</strong> <?= $ride['seats_available'] ?></p>
                <p><strong>Fare (₹):</strong> <?= $ride['fare'] ?></p>
                <p><strong>Driver:</strong> <?= htmlspecialchars($ride['driver_name']) ?></p>
                <p><strong>ID Proof:</strong> <a href="<?= htmlspecialchars($ride['id_proof']) ?>" target="_blank">View</a></p>
                <p><strong>License:</strong> <a href="<?= htmlspecialchars($ride['driving_license']) ?>" target="_blank">View</a></p>

                <form method="GET" action="ride_details.php" class="book-form">
                    <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                    <button type="submit">Book Seat</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No rides available at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
