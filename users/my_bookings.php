<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$passenger_id = intval($_SESSION['user_id']);
$message = "";

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);

    $update = $conn->query("UPDATE bookings SET booking_status = 'cancelled' WHERE id = $booking_id");

    if ($update) {
        $conn->query("UPDATE rides r 
                      JOIN bookings b ON r.id = b.ride_id 
                      SET r.seats_available = r.seats_available + b.seats_booked 
                      WHERE b.id = $booking_id");
        $message = "Booking cancelled successfully!";
    } else {
        $message = "Error cancelling booking.";
    }
}

// Fetch passenger's booked rides
$sql = "SELECT b.id AS booking_id, r.pickup_location, r.drop_location, r.travel_date, r.travel_time, r.fare, 
        u.name AS driver_name, b.booking_status 
        FROM bookings b
        JOIN rides r ON b.ride_id = r.id
        JOIN users u ON r.driver_id = u.id
        WHERE b.passenger_id = $passenger_id
        ORDER BY FIELD(b.booking_status, 'pending', 'confirmed', 'completed', 'cancelled')";

$result = $conn->query($sql);

if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - RideShare</title>
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
  <?php include '../includes/header.php'; ?>

  <div class="max-w-5xl mx-auto px-6 py-12">
    <h2 class="text-4xl font-bold mb-8">📋 My Bookings</h2>

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-4 bg-green-600 text-white rounded-lg shadow">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
      <ul class="space-y-6">
        <?php while($row = $result->fetch_assoc()): ?>
          <li class="bg-white/10 border border-white/20 rounded-2xl p-6 shadow-md">
            <div><strong>Driver:</strong> <?php echo htmlspecialchars($row['driver_name']); ?></div>
            <div><strong>From:</strong> <?php echo htmlspecialchars($row['pickup_location']); ?></div>
            <div><strong>To:</strong> <?php echo htmlspecialchars($row['drop_location']); ?></div>
            <div><strong>Date:</strong> <?php echo $row['travel_date']; ?> @ <?php echo $row['travel_time']; ?></div>
            <div><strong>Fare:</strong> ₹<?php echo number_format($row['fare'], 2); ?></div>
            <div><strong>Status:</strong> <?php echo ucfirst($row['booking_status']); ?></div>

            <?php if ($row['booking_status'] === 'pending' || $row['booking_status'] === 'confirmed'): ?>
              <form method="POST" class="mt-4">
                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                <button type="submit" name="cancel_booking" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Cancel Booking</button>
              </form>
            <?php endif; ?>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <p class="text-gray-300 text-lg">You have no bookings yet.</p>
    <?php endif; ?>
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
