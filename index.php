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

    .autocomplete-suggestions {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background-color: white;
      border: 1px solid #ddd;
      max-height: 200px;
      overflow-y: auto;
      z-index: 10;
    }

    .autocomplete-suggestions li {
      padding: 8px;
      cursor: pointer;
      background-color: white;
      color: #333;
    }

    .autocomplete-suggestions li:hover {
      background-color: #f3f3f3;
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
        <input type="text" name="pickup" id="pickup" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter pickup point" required>
      </div>
      <div>
        <label class="block font-semibold text-white mb-2">Drop Location</label>
        <input type="text" name="drop" id="drop" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter destination" required>
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

 <script>
  <script>
  // Add event listeners for pickup and drop inputs
  document.getElementById('pickup').addEventListener('input', function() {
    let pickupInput = this.value;
    if (pickupInput.length >= 3) {
      fetchLocations(pickupInput, 'pickup');
    }
  });

  document.getElementById('drop').addEventListener('input', function() {
    let dropInput = this.value;
    if (dropInput.length >= 3) {
      fetchLocations(dropInput, 'drop');
    }
  });

  // Fetch locations from the PHP script
  function fetchLocations(query, type) {
    fetch('search_locations.php?query=' + query + '&type=' + type)
      .then(response => response.json())
      .then(data => {
        showSuggestions(data, type);
      })
      .catch(error => console.error('Error fetching locations:', error));
  }

  // Display the fetched suggestions in the dropdown
  function showSuggestions(locations, type) {
    const inputElement = type === 'pickup' ? document.getElementById('pickup') : document.getElementById('drop');
    
    // Remove any existing suggestions list
    const existingSuggestions = inputElement.parentNode.querySelector('ul');
    if (existingSuggestions) {
      existingSuggestions.remove();
    }

    if (locations.length === 0) {
      return; // No suggestions to display
    }

    // Create the suggestion list
    const suggestionList = document.createElement('ul');
    suggestionList.classList.add('autocomplete-suggestions'); // You can reuse your existing styles

    locations.forEach(location => {
      const listItem = document.createElement('li');
      listItem.classList.add('px-4', 'py-2', 'cursor-pointer');
      listItem.innerText = location;

      listItem.addEventListener('click', function() {
        inputElement.value = location; // Set input value when item is clicked
        suggestionList.remove(); // Remove the suggestions
      });

      suggestionList.appendChild(listItem);
    });

    inputElement.parentNode.appendChild(suggestionList);
  }
</script>

 </script>

</body>

</html>