<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if ($booking_id <= 0) {
    die("Invalid Booking ID.");
}

// Fetch booking info
$sql = "SELECT b.*, r.fare, r.pickup_location, r.drop_location, r.travel_date, r.travel_time
        FROM bookings b
        JOIN rides r ON b.ride_id = r.id
        WHERE b.id = $booking_id AND b.passenger_id = {$_SESSION['user_id']}";

$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    die("Booking not found or access denied.");
}

$booking = $result->fetch_assoc();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $conn->query("UPDATE bookings SET payment_status = 'paid' WHERE id = $booking_id");
    header("Location: my_bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make Payment</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
  <div class="bg-white/10 border border-white/20 p-8 rounded-xl shadow-md w-full max-w-md text-white">
    <h2 class="text-2xl font-bold mb-4">💳 Payment Summary</h2>
    <p><strong>From:</strong> <?= htmlspecialchars($booking['pickup_location']); ?></p>
    <p><strong>To:</strong> <?= htmlspecialchars($booking['drop_location']); ?></p>
    <p><strong>Date:</strong> <?= $booking['travel_date']; ?> @ <?= $booking['travel_time']; ?></p>
    <p><strong>Total Fare:</strong> ₹<?= number_format($booking['fare'], 2); ?></p>

    <form method="POST" class="mt-6">
      <button type="submit" name="pay_now" class="bg-green-500 hover:bg-green-600 px-6 py-2 rounded-lg text-white">
        Confirm Payment
      </button>
    </form>
    <a href="my_bookings.php" class="block text-center text-sm mt-4 text-yellow-400 hover:underline">← Back to Bookings</a>
  </div>
</body>
</html>
