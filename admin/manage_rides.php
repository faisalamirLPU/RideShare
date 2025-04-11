<?php
session_start();
require_once '../database/db_config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch rides with driver and user info
$sql = "SELECT 
            rides.*, 
            drivers.vehicle_model, 
            drivers.vehicle_number, 
            users.name AS driver_name, 
            users.phone AS driver_phone 
        FROM rides
        INNER JOIN drivers ON rides.driver_id = drivers.id
        INNER JOIN users ON drivers.user_id = users.id
        ORDER BY rides.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Rides - Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <?php include 'include/header.php'; ?>

  <main class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">🚗 Manage Rides</h1>

    <div class="bg-white shadow-md rounded-xl overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold">#</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Driver</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Vehicle</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">From</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">To</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Date</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Time</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Fare</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Seats</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
            <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
          <?php if ($result->num_rows > 0): ?>
            <?php $i = 1; while ($ride = $result->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?php echo $i++; ?></td>
                <td class="px-6 py-4 text-sm">
                  <?php echo htmlspecialchars($ride['driver_name']); ?><br>
                  <span class="text-xs text-gray-500"><?php echo htmlspecialchars($ride['driver_phone']); ?></span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <?php echo $ride['vehicle_model']; ?><br>
                  <span class="text-xs text-gray-500"><?php echo $ride['vehicle_number']; ?></span>
                </td>
                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($ride['pickup_location']); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($ride['drop_location']); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo date('d M Y', strtotime($ride['travel_date'])); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo date('h:i A', strtotime($ride['travel_time'])); ?></td>
                <td class="px-6 py-4 text-sm">₹<?php echo $ride['fare']; ?></td>
                <td class="px-6 py-4 text-sm"><?php echo $ride['seats_available']; ?></td>
                <td class="px-6 py-4 text-sm capitalize">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $ride['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $ride['status']; ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-center text-sm">
                  <a href="ride_details.php?id=<?php echo $ride['id']; ?>" class="text-blue-600 hover:underline mr-3">View</a>
                  <a href="delete_ride.php?id=<?php echo $ride['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this ride?')">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="11" class="text-center py-6 text-gray-500">No rides found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <?php include 'include/footer.php'; ?>
</body>
</html>
