<?php
require_once '../database/db_config.php';

$pickup = $_GET['pickup'] ?? '';
$drop = $_GET['drop'] ?? '';

if (empty($pickup) || empty($drop)) {
    echo "<p>Please enter both pickup and drop locations.</p>";
    exit;
}

$sql = "SELECT r.*, u.name as driver_name
        FROM rides r
        JOIN drivers d ON r.driver_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE r.pickup_location LIKE ? AND r.drop_location LIKE ?
        ORDER BY r.travel_date, r.travel_time";

$stmt = $conn->prepare($sql);
$pickup_like = "%" . $pickup . "%";
$drop_like = "%" . $drop . "%";
$stmt->bind_param("ss", $pickup_like, $drop_like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Rides</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen px-4 py-10">
  <h1 class="text-3xl font-bold mb-6">Available Rides from "<?= htmlspecialchars($pickup) ?>" to "<?= htmlspecialchars($drop) ?>"</h1>

  <?php if ($result->num_rows > 0): ?>
    <div class="grid gap-6 md:grid-cols-2">
      <?php while ($ride = $result->fetch_assoc()): ?>
        <div class="bg-white/10 border border-white/20 rounded-2xl p-6 shadow-xl backdrop-blur-sm">
          <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars($ride['pickup_location']) ?> ➜ <?= htmlspecialchars($ride['drop_location']) ?></h2>
          <p class="text-gray-200">Driver: <?= htmlspecialchars($ride['driver_name']) ?></p>
          <p class="text-gray-300">Date: <?= $ride['travel_date'] ?> | Time: <?= $ride['travel_time'] ?></p>
          <p class="text-yellow-300">Seats Available: <?= $ride['seats_available'] ?></p>
          <a href="ride_details.php?id=<?= $ride['id'] ?>" class="inline-block mt-4 bg-yellow-400 hover:bg-yellow-500 text-black px-6 py-2 rounded-full font-semibold">View Ride</a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-red-400">No matching rides found.</p>
  <?php endif; ?>

</body>
</html>
