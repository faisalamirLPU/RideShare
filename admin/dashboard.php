<?php
session_start();
include('../database/db_config.php');
include('include/header.php');

// Simple authentication check
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Fetch counts
$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE user_type='user'"));
$drivers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM drivers"));
$rides = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM rides"));
$bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings"));
$payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM payments WHERE payment_status='success'"));
?>

<div class="p-10 bg-gray-100 min-h-screen">
  <h2 class="text-3xl font-bold mb-6 text-gray-800">Admin Dashboard</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Users -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
      <h3 class="text-lg font-semibold text-blue-700">Total Users</h3>
      <p class="text-3xl font-bold text-gray-900 mt-2"><?= $users['total'] ?></p>
    </div>

    <!-- Drivers -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
      <h3 class="text-lg font-semibold text-green-700">Drivers</h3>
      <p class="text-3xl font-bold text-gray-900 mt-2"><?= $drivers['total'] ?></p>
    </div>

    <!-- Rides -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
      <h3 class="text-lg font-semibold text-purple-700">Rides</h3>
      <p class="text-3xl font-bold text-gray-900 mt-2"><?= $rides['total'] ?></p>
    </div>

    <!-- Bookings -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
      <h3 class="text-lg font-semibold text-yellow-700">Bookings</h3>
      <p class="text-3xl font-bold text-gray-900 mt-2"><?= $bookings['total'] ?></p>
    </div>

    <!-- Earnings -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
      <h3 class="text-lg font-semibold text-rose-700">Total Earnings</h3>
      <p class="text-3xl font-bold text-gray-900 mt-2">₹<?= $payments['total'] ?? 0 ?></p>
    </div>
  </div>
</div>


<?php include('include/footer.php'); ?>
