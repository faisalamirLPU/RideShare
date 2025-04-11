<?php
session_start();
require_once 'database/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql_user = "SELECT name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Fetch upcoming bookings
$sql_bookings = "SELECT r.pickup_location, r.drop_location, r.travel_date, r.travel_time, 
                        b.payment_status, b.seats_booked
                 FROM bookings b
                 JOIN rides r ON b.ride_id = r.id
                 WHERE b.passenger_id = ? AND b.booking_status = 'confirmed'
                 ORDER BY r.travel_date ASC";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $user_id);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

// ✅ Updated ride query to join with drivers table
$sql_rides = "SELECT r.pickup_location, r.drop_location, r.travel_date, r.travel_time, r.seats_available 
              FROM rides r
              JOIN drivers d ON r.driver_id = d.id
              WHERE d.user_id = ?
              ORDER BY r.travel_date ASC";
$stmt_rides = $conn->prepare($sql_rides);
$stmt_rides->bind_param("i", $user_id);
$stmt_rides->execute();
$result_rides = $stmt_rides->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - RideShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('../assets/img/car.png');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>
<body class="text-white min-h-screen bg-black bg-opacity-50 backdrop-blur-md">
  <?php include './includes/header.php'; ?>

  <div class="max-w-5xl mx-auto px-6 py-12">
    <h2 class="text-4xl font-bold mb-8">Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>

    <div class="grid md:grid-cols-2 gap-10">
      <!-- Book a Ride -->
      <div class="bg-white/10 border border-white/20 p-6 rounded-2xl shadow-lg backdrop-blur-md">
        <h3 class="text-2xl font-semibold mb-4">🚗 Upcoming Bookings</h3>
        <?php if ($result_bookings->num_rows > 0): ?>
          <ul class="space-y-4">
            <?php while($booking = $result_bookings->fetch_assoc()): ?>
              <li class="p-4 bg-white/10 border border-white/20 rounded-xl">
                <div><strong>From:</strong> <?php echo $booking['pickup_location']; ?></div>
                <div><strong>To:</strong> <?php echo $booking['drop_location']; ?></div>
                <div><strong>Date:</strong> <?php echo $booking['travel_date']; ?> @ <?php echo $booking['travel_time']; ?></div>
                <div><strong>Seats:</strong> <?php echo $booking['seats_booked']; ?></div>
                <div><strong>Status:</strong> <?php echo ucfirst($booking['payment_status']); ?></div>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p class="text-gray-300">No upcoming bookings found.</p>
        <?php endif; ?>
      </div>

      <!-- Rides Offered -->
      <div class="bg-white/10 border border-white/20 p-6 rounded-2xl shadow-lg backdrop-blur-md">
        <h3 class="text-2xl font-semibold mb-4">🛣️ Rides You've Offered</h3>
        <?php if ($result_rides->num_rows > 0): ?>
          <ul class="space-y-4">
            <?php while($ride = $result_rides->fetch_assoc()): ?>
              <li class="p-4 bg-white/10 border border-white/20 rounded-xl">
                <div><strong>From:</strong> <?php echo $ride['pickup_location']; ?></div>
                <div><strong>To:</strong> <?php echo $ride['drop_location']; ?></div>
                <div><strong>Date:</strong> <?php echo $ride['travel_date']; ?> @ <?php echo $ride['travel_time']; ?></div>
                <div><strong>Seats Available:</strong> <?php echo $ride['seats_available']; ?></div>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p class="text-gray-300">You haven't posted any rides yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Chatbot -->
  <div class="fixed bottom-6 right-6 z-50">
    <div id="chat-box" class="hidden bg-white/90 text-black rounded-xl w-80 h-96 p-4 shadow-xl flex flex-col justify-between">
      <div class="overflow-y-auto h-full space-y-2 text-sm" id="messages">
        <div class="text-gray-600">🤖 Hello! How can I help you today?</div>
      </div>
      <div class="flex mt-2">
        <input id="chat-input" type="text" placeholder="Type your message..." class="flex-1 p-2 rounded-l-lg border border-gray-300">
        <button onclick="sendMessage()" class="bg-yellow-400 px-4 rounded-r-lg">Send</button>
      </div>
    </div>
    <button onclick="toggleChat()" class="bg-yellow-400 text-black p-4 rounded-full shadow-md hover:bg-yellow-500">
      💬 Chat
    </button>
  </div>

  <footer class="text-center text-sm text-white py-6 bg-black/60">
    &copy; 2025 RideShare. All rights reserved.
  </footer>

  <script>
    function toggleChat() {
      const chatBox = document.getElementById('chat-box');
      chatBox.classList.toggle('hidden');
    }

    function sendMessage() {
      const input = document.getElementById('chat-input');
      const messages = document.getElementById('messages');
      const userMsg = input.value.trim();

      if (userMsg !== '') {
        const userDiv = document.createElement('div');
        userDiv.className = 'text-right text-blue-600';
        userDiv.textContent = '🧑‍💻 ' + userMsg;
        messages.appendChild(userDiv);

        fetch('../chatbot/chatbot.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'message=' + encodeURIComponent(userMsg)
        })
        .then(response => response.text())
        .then(reply => {
          const botDiv = document.createElement('div');
          botDiv.className = 'text-left text-gray-700';
          botDiv.textContent = '🤖 ' + reply;
          messages.appendChild(botDiv);
          messages.scrollTop = messages.scrollHeight;
        });

        input.value = '';
      }
    }

    document.getElementById("chat-input").addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        sendMessage();
      }
    });
  </script>
</body>
</html>
