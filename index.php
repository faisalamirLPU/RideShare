<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RideShare - Book Your City Ride</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('car.png');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>

<body class="text-white min-h-screen bg-black bg-opacity-40 backdrop-blur-md">
  <!-- Navbar -->
  <?php include('./includes/header.php'); ?>

  <!-- Hero Section -->
  <section class="text-center pt-28 pb-16 px-4">
    <h2 class="text-6xl font-extrabold drop-shadow-lg leading-snug">Your City, <span class="text-yellow-400">Your Ride</span></h2>
    <p class="text-xl mt-4 max-w-2xl mx-auto text-gray-200 drop-shadow-sm">Book a car or bus in seconds. Reliable, safe and budget-friendly rides at your fingertips.</p>
    <a href="#booking" class="mt-10 inline-block bg-yellow-400 hover:bg-yellow-500 text-black font-bold px-8 py-3 rounded-full shadow-md transition">🚖 Book Now</a>
  </section>

  <!-- Booking Form -->
  <section id="booking" class="max-w-4xl mx-auto bg-white/10 backdrop-blur-md p-10 rounded-3xl shadow-2xl my-12 border border-white/20">
    <h3 class="text-3xl font-bold mb-8 text-center text-white">Book a Ride</h3>
    <form action="rides/book_ride.php" method="POST" class="space-y-6">
      <div>
        <label class="block font-semibold text-white mb-2">Pickup Location</label>
        <input type="text" name="pickup" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter pickup point" required>
      </div>
      <div>
        <label class="block font-semibold text-white mb-2">Drop Location</label>
        <input type="text" name="drop" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter destination" required>
      </div>
      
      <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black p-4 rounded-full font-bold text-lg transition">🔍 Search Rides</button>
    </form>
    <!-- <form action="rides/search_rides.php" method="GET" class="space-y-6">
      <div>
        <label class="block font-semibold text-white mb-2">Pickup Location</label>
        <input type="text" name="pickup" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter pickup point" required>
      </div>
      <div>
        <label class="block font-semibold text-white mb-2">Drop Location</label>
        <input type="text" name="drop" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter destination" required>
      </div>
      
      <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black p-4 rounded-full font-bold text-lg transition">🔍 Search Rides</button>
    </form> -->

  </section>

 <?php include('./includes/footer.php') ?>

</body>

</html>