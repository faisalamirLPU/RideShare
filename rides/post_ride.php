<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$driver_query = $conn->prepare("SELECT id, verified FROM drivers WHERE user_id = ?");
$driver_query->bind_param("i", $user_id);
$driver_query->execute();
$driver_result = $driver_query->get_result();
$driver = $driver_result->fetch_assoc();
$driver_query->close();

if (!$driver) {
    header("Location: create_driver_profile.php");
    exit();
}

if ($driver['verified'] !== 'approved') {
    $message = "Your driver profile is currently <strong>{$driver['verified']}</strong>. You cannot post rides until it is approved.";
} else {
    $driver_id = $driver['id'];
    $message = "";

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
  <title>Offer a Ride - RideShare</title>
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

  <div class="max-w-3xl mx-auto px-4 pt-2 pb-16">
    <h2 class="text-4xl font-extrabold text-center mb-10 drop-shadow">🚘 Offer a Ride</h2>

    <?php if (!empty($message)): ?>
      <div class="bg-yellow-400 text-black px-6 py-3 rounded-lg mb-6 shadow-lg text-center font-semibold">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if ($driver && $driver['verified'] === 'approved'): ?>
      <form method="POST" class="bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-white/20 space-y-6">
        <div>
          <label class="block mb-2 font-semibold">Pickup Location:</label>
          <input type="text" name="source" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <div>
          <label class="block mb-2 font-semibold">Drop Location:</label>
          <input type="text" name="destination" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <div>
          <label class="block mb-2 font-semibold">Date:</label>
          <input type="date" name="date" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <div>
          <label class="block mb-2 font-semibold">Time:</label>
          <input type="time" name="time" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <div>
          <label class="block mb-2 font-semibold">Seats Available:</label>
          <input type="number" name="seats" min="1" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <div>
          <label class="block mb-2 font-semibold">Fare per Seat (₹):</label>
          <input type="number" step="0.01" name="fare" min="1" required class="w-full px-4 py-2 rounded-lg text-black">
        </div>

        <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 px-6 rounded-full shadow-md transition">✅ Post Ride</button>
      </form>
    <?php else: ?>
      <p class="text-center text-lg text-red-200 mt-10"><?= $message ?></p>
    <?php endif; ?>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
