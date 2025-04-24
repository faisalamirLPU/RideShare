<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ride_id'])) {
    $ride_id = intval($_POST['ride_id']);

    $seatCheck = $conn->prepare("SELECT seats_available FROM rides WHERE id = ? AND status = 'active'");
    $seatCheck->bind_param("i", $ride_id);
    $seatCheck->execute();
    $result = $seatCheck->get_result();
    $ride = $result->fetch_assoc();
    $seatCheck->close();

    if ($ride && $ride['seats_available'] > 0) {
        $seats_booked = 1;
        $booking_status = 'pending';
        $payment_status = 'pending';

        $stmt = $conn->prepare("INSERT INTO bookings (ride_id, passenger_id, seats_booked, booking_status, payment_status, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())");

        if ($stmt && $stmt->bind_param("iiiss", $ride_id, $passenger_id, $seats_booked, $booking_status, $payment_status)) {
            if ($stmt->execute()) {
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
            $message = "Statement preparation failed.";
        }
    } else {
        $message = "Sorry, no seats available!";
    }
}

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

$userQuery = $conn->prepare("SELECT phone FROM users WHERE id = ?");
$userQuery->bind_param("i", $passenger_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();
$userQuery->close();

$user_phone = $userData ? $userData['phone'] : "Unavailable";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Rides - RideShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('../car.png');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>
<body class="text-white min-h-screen bg-black bg-opacity-40 backdrop-blur-md">
  <?php include '../includes/header.php'; ?>

  <div class="max-w-6xl mx-auto px-4 pt-28 pb-16">
    <h2 class="text-4xl font-extrabold text-center mb-10 drop-shadow">Available Rides</h2>

    <?php if (!empty($message)): ?>
      <div class="bg-yellow-400 text-black px-6 py-3 rounded-lg mb-6 shadow-lg text-center font-semibold">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
      <div class="grid md:grid-cols-2 gap-6">
        <?php while ($ride = $result->fetch_assoc()): ?>
          <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl shadow-2xl border border-white/20">
            <p><strong>From:</strong> <?= htmlspecialchars($ride['pickup_location']) ?></p>
            <p><strong>To:</strong> <?= htmlspecialchars($ride['drop_location']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($ride['travel_date']) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($ride['travel_time']) ?></p>
            <p><strong>Seats Available:</strong> <?= $ride['seats_available'] ?></p>
            <p><strong>Fare (₹):</strong> <?= $ride['fare'] ?></p>
            <p><strong>Driver:</strong> <?= htmlspecialchars($ride['driver_name']) ?></p>
            <p><strong>Your Phone:</strong> <?= htmlspecialchars($user_phone) ?></p>

            <div class="mt-4 space-x-4">
              <a href="/RideShare/uploads/<?= basename($ride['id_proof']) ?>" target="_blank" class="text-yellow-300 underline">View ID</a>
              <a href="https://myaadhaar.uidai.gov.in/check-aadhaar-validity" target="_blank" class="text-yellow-300 underline">Verify Aadhaar</a>
              <a href="/RideShare/uploads/<?= basename($ride['driving_license']) ?>" target="_blank" class="text-yellow-300 underline">View License</a>
            </div>

            <form method="GET" action="ride_details.php" class="mt-6">
              <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
              <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 px-6 rounded-full shadow-md transition">🚘 Book Seat</button>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-lg text-gray-200 mt-10">No rides available at the moment. Please check back later.</p>
    <?php endif; ?>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
