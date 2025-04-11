<?php
session_start();
require_once '../database/db_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch dashboard stats
$users_count = $drivers_count = $rides_count = $bookings_count = 0;

$conn->query("SET SESSION sql_mode = ''"); // disable strict sql mode if any error
$users_count = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'user'")->fetch_row()[0];
$drivers_count = $conn->query("SELECT COUNT(*) FROM drivers")->fetch_row()[0];
$rides_count = $conn->query("SELECT COUNT(*) FROM rides")->fetch_row()[0];
$bookings_count = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - RideShare</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">

<?php include('include/header.php'); ?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-8">👋 Welcome, Admin</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Users -->
        <div class="bg-white shadow-md rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 text-blue-600">Total Users</h2>
            <p class="text-3xl font-bold"><?= $users_count ?></p>
        </div>

        <!-- Drivers -->
        <div class="bg-white shadow-md rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 text-green-600">Total Drivers</h2>
            <p class="text-3xl font-bold"><?= $drivers_count ?></p>
        </div>

        <!-- Rides -->
        <div class="bg-white shadow-md rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 text-purple-600">Total Rides</h2>
            <p class="text-3xl font-bold"><?= $rides_count ?></p>
        </div>

        <!-- Bookings -->
        <div class="bg-white shadow-md rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 text-yellow-600">Total Bookings</h2>
            <p class="text-3xl font-bold"><?= $bookings_count ?></p>
        </div>
    </div>
</div>

</body>
</html>
