<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$ride_id = isset($_GET['ride_id']) ? intval($_GET['ride_id']) : 0;
$message = "";

// Handle booking confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ride_id'], $_POST['seats'])) {
    $ride_id = intval($_POST['ride_id']);
    $seats_booked = intval($_POST['seats']);

    $stmt = $conn->prepare("SELECT seats_available, fare FROM rides WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    $stmt->close();

    if ($ride && $ride['seats_available'] >= $seats_booked) {
        $booking_status = 'pending';
        $payment_status = 'pending';

        $stmt = $conn->prepare("INSERT INTO bookings (ride_id, passenger_id, seats_booked, booking_status, payment_status, created_at)
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiiss", $ride_id, $passenger_id, $seats_booked, $booking_status, $payment_status);

        if ($stmt->execute()) {
            $update = $conn->prepare("UPDATE rides SET seats_available = seats_available - ? WHERE id = ?");
            $update->bind_param("ii", $seats_booked, $ride_id);
            $update->execute();
            $update->close();

            $message = "Booking successful! Seats booked: $seats_booked";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Requested seats not available.";
    }
}

// Fetch ride details
$stmt = $conn->prepare("SELECT r.*, u.name AS driver_name, d.id_proof, d.driving_license
                        FROM rides r
                        JOIN drivers d ON r.driver_id = d.id
                        JOIN users u ON d.user_id = u.id
                        WHERE r.id = ?");
$stmt->bind_param("i", $ride_id);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();
$stmt->close();

if (!$ride) {
    die("Ride not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ride Details</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script>
    function updateFare() {
        const farePerSeat = <?= $ride['fare'] ?>;
        const seats = document.getElementById("seats").value;
        document.getElementById("totalFare").innerText = "₹" + (farePerSeat * seats);
    }
    </script>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="ride-details-container">
    <h2>Ride Details</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
    <p><strong>Pickup:</strong> <?= htmlspecialchars($ride['pickup_location']) ?></p>
    <p><strong>Drop:</strong> <?= htmlspecialchars($ride['drop_location']) ?></p>
    <p><strong>Date:</strong> <?= $ride['travel_date'] ?></p>
    <p><strong>Time:</strong> <?= $ride['travel_time'] ?></p>
    <p><strong>Available Seats:</strong> <?= $ride['seats_available'] ?></p>
    <p><strong>Fare per seat:</strong> ₹<?= $ride['fare'] ?></p>
    <p><strong>Driver:</strong> <?= $ride['driver_name'] ?></p>
    <p><strong>ID Proof:</strong> <a href="<?= $ride['id_proof'] ?>" target="_blank">View</a></p>
    <p><strong>License:</strong> <a href="<?= $ride['driving_license'] ?>" target="_blank">View</a></p>

    <form method="POST">
        <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
        <label for="seats">Number of seats to book:</label>
        <select id="seats" name="seats" onchange="updateFare()">
            <?php for ($i = 1; $i <= $ride['seats_available']; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <p><strong>Total Fare:</strong> <span id="totalFare">₹<?= $ride['fare'] ?></span></p>
        <button type="submit">Confirm Booking</button>
    </form>
</div>
</body>
</html>
