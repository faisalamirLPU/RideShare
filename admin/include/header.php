<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - RideShare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

<!-- Navbar -->
<nav class="bg-gray-900 text-white px-6 py-4 shadow-md">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-2xl font-bold">RideShare Admin</div>
        <div class="space-x-6">
            <a href="dashboard.php" class="hover:text-yellow-400 font-medium">Dashboard</a>
            <a href="manage_users.php" class="hover:text-yellow-400 font-medium">Users</a>
            <a href="manage_rides.php" class="hover:text-yellow-400 font-medium">Rides</a>
            <a href="manage_bookings.php" class="hover:text-yellow-400 font-medium">Bookings</a>
            <a href="manage_payments.php" class="hover:text-yellow-400 font-medium">Payments</a>
            <a href="verify_driver.php" class="hover:text-yellow-400 font-medium">Approve Driver</a>
            <a href="./logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold transition">Logout</a>
        </div>
    </div>
</nav>
