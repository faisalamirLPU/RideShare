<?php
session_start();
require_once '../database/db_config.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all payments with booking and user info
$sql = "SELECT 
            payments.*, 
            users.name AS passenger_name,
            users.phone AS passenger_phone,
            bookings.seats_booked,
            rides.pickup_location,
            rides.drop_location,
            rides.travel_date,
            rides.travel_time
        FROM payments
        INNER JOIN bookings ON payments.booking_id = bookings.id
        INNER JOIN users ON bookings.passenger_id = users.id
        INNER JOIN rides ON bookings.ride_id = rides.id
        ORDER BY payments.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Payments - Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <?php include 'include/header.php'; ?>

  <main class="max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">💳 Manage Payments</h1>

    <div class="bg-white shadow-md rounded-xl overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold">#</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Passenger</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Ride Info</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Amount</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Method</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Transaction ID</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold">Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
          <?php if ($result->num_rows > 0): ?>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?php echo $i++; ?></td>
                <td class="px-6 py-4 text-sm">
                  <?php echo htmlspecialchars($row['passenger_name']); ?><br>
                  <span class="text-xs text-gray-500"><?php echo $row['passenger_phone']; ?></span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <?php echo htmlspecialchars($row['pickup_location']); ?> →<br>
                  <?php echo htmlspecialchars($row['drop_location']); ?><br>
                  <span class="text-xs"><?php echo date('d M Y, h:i A', strtotime($row['travel_date'] . ' ' . $row['travel_time'])); ?></span>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-green-600">₹<?php echo $row['amount']; ?></td>
                <td class="px-6 py-4 text-sm"><?php echo ucfirst($row['payment_method']); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo $row['transaction_id']; ?></td>
                <td class="px-6 py-4 text-sm">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $row['payment_status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo ucfirst($row['payment_status']); ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-sm"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-6 text-gray-500">No payments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <?php include 'include/footer.php'; ?>
</body>
</html>
