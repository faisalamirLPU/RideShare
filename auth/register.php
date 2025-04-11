<?php
// auth/register.php
include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = 'user'; // Default user type

    // Check if email or phone already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    if (!$checkUser) {
        die("Prepare failed: " . $conn->error);
    }

    $checkUser->bind_param("ss", $email, $phone);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "<script>alert('Email or phone number already registered!'); window.location.href='register.php';</script>";
        $checkUser->close();
        exit;
    }
    $checkUser->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $name, $email, $phone, $password, $user_type);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! Please log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | RideShare</title>
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

<body class="text-white min-h-screen bg-black bg-opacity-40 backdrop-blur-md flex items-center justify-center">
  <div class="bg-white/10 backdrop-blur-lg p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
    <h2 class="text-3xl font-bold text-center mb-6 text-yellow-400">Register for RideShare</h2>

    <form action="register.php" method="POST" onsubmit="return validateForm()" class="space-y-5">
      <div>
        <label for="name" class="block text-white font-semibold mb-2">Full Name</label>
        <input type="text" id="name" name="name" required class="w-full p-3 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30">
      </div>

      <div>
        <label for="email" class="block text-white font-semibold mb-2">Email</label>
        <input type="email" id="email" name="email" required class="w-full p-3 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30">
      </div>

      <div>
        <label for="phone" class="block text-white font-semibold mb-2">Phone Number</label>
        <input type="text" id="phone" name="phone" required class="w-full p-3 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30">
      </div>

      <div>
        <label for="password" class="block text-white font-semibold mb-2">Password</label>
        <input type="password" id="password" name="password" required class="w-full p-3 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30">
      </div>

      <div>
        <label for="confirm_password" class="block text-white font-semibold mb-2">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-3 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30">
      </div>

      <!-- <div>
        <label for="user_type" class="block text-white font-semibold mb-2">Register as:</label>
        <select id="user_type" name="user_type" required class="w-full p-3 rounded-xl bg-white/20 text-white focus:ring-2 focus:ring-yellow-400 border border-white/30">
          <option value="passenger">Passenger</option>
          <option value="driver">Driver</option>
        </select>
      </div> -->

      <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black p-3 rounded-full font-bold text-lg transition">Register</button>
    </form>

    <p class="text-center text-sm mt-4 text-white">Already have an account? <a href="login.php" class="text-yellow-400 hover:underline">Login here</a></p>
  </div>

  <script>
    function validateForm() {
      let password = document.getElementById("password").value;
      let confirmPassword = document.getElementById("confirm_password").value;
      if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
      }
      return true;
    }
  </script>
</body>

</html>