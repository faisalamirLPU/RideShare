<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>RideShare - Book Your City Ride</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('car.png');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      
    }
    .z-index{
      z-index: 9999999999999999;
    }
  </style>
</head>
<body>
<nav class="backdrop-blur-md bg-black/60 p-5 px-10 flex justify-between items-center shadow-lg">
  <h1 class="text-4xl font-bold tracking-wider text-yellow-400"><a href="/rideshare/index.php">Ride<span class="text-white">Share</a></span></h1>

  <ul class="flex space-x-8 text-lg font-semibold items-center text-white relative">
    <li><a href="/rideshare/index.php" class="hover:text-yellow-400 transition">Home</a></li>
    <li><a href="/rideshare/rides/book_ride.php" class="hover:text-yellow-400 transition">Book</a></li>
    <li><a href="/rideshare/rides/post_ride.php" class="hover:text-yellow-400 transition">Offer Ride</a></li>

    <?php if ($userLoggedIn): ?>
      <!-- Profile Dropdown with Toggle -->
      <li class="relative">
        <button onclick="toggleDropdown()" class="flex items-center space-x-2 hover:text-yellow-400 transition focus:outline-none">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=000000&color=fff&size=32" class="rounded-full w-8 h-8" alt="Profile" />
          <span><?= htmlspecialchars($userName) ?></span>
          <svg class="w-4 h-4 fill-current mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.25 7.5L10 12.25L14.75 7.5H5.25Z"/></svg>
        </button>
        <ul id="dropdownMenu" class="z-index absolute right-0 mt-2 bg-white text-black rounded shadow-lg hidden min-w-[150px] z-50">
          <li><a href="/rideshare/dashboard.php" class="block px-4 py-2 hover:bg-yellow-100">Dashboard</a></li>
          <li><a href="/rideshare/users/my_bookings.php" class="block px-4 py-2 hover:bg-yellow-100">My Rides</a></li>
          <li><a href="/rideshare/users/my_offered_rides.php" class="block px-4 py-2 hover:bg-yellow-100">My Offered Rides</a></li>
          <li><a href="/rideshare/users/drivers_view_profile.php" class="block px-4 py-2 hover:bg-yellow-100">My Driver Profile</a></li>

          
          <li><a href="/rideshare/auth/logout.php" class="block px-4 py-2 hover:bg-yellow-100">Logout</a></li>
        </ul>
      </li>
    <?php else: ?>
      <!-- Show Login/Register if not logged in -->
      <li><a href="/rideshare/auth/login.php" class="hover:text-yellow-400 transition">Login</a></li>
      <li><a href="/rideshare/auth/register.php" class="hover:text-yellow-400 transition">Register</a></li>
    <?php endif; ?>
  </ul>
</nav>

<script>
  function toggleDropdown() {
    const dropdown = document.getElementById('dropdownMenu');
    dropdown.classList.toggle('hidden');
  }

  // Optional: Hide dropdown if clicked outside
  document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('dropdownMenu');
    const button = event.target.closest('button');

    if (!event.target.closest('li') || !button) {
      if (!event.target.closest('#dropdownMenu')) {
        dropdown?.classList.add('hidden');
      }
    }
  });
</script>
