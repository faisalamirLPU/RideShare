<?php
session_start();
include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];

                if ($user['user_type'] === 'driver') {
                    header("Location: /rideshare/dashboard.php");
                } else {
                    header("Location: /rideshare/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - RideShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('../car.png');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>

<body class="text-white min-h-screen bg-black bg-opacity-40 backdrop-blur-md">
  <!-- Navbar -->
  <nav class="backdrop-blur-md bg-black/60 p-5 px-10 flex justify-between items-center shadow-lg">
    <h1 class="text-4xl font-bold tracking-wider text-yellow-400">Ride<span class="text-white">Share</span></h1>
    <ul class="flex space-x-8 text-lg font-semibold">
      <li><a href="../index.php" class="hover:text-yellow-400 transition">Home</a></li>
      <li><a href="../rides/book_ride.php" class="hover:text-yellow-400 transition">Book</a></li>
      <li><a href="login.php" class="hover:text-yellow-400 transition">Login</a></li>
      <li><a href="register.php" class="hover:text-yellow-400 transition">Register</a></li>
      <li><a href="../rides/offer_ride.php" class="hover:text-yellow-400 transition">Offer Ride</a></li>
    </ul>
  </nav>

  <!-- Login Form -->
  <section class="flex justify-center items-center h-screen -mt-20">
    <div class="bg-white/10 backdrop-blur-md p-10 rounded-3xl shadow-2xl border border-white/20 w-full max-w-md">
      <h2 class="text-3xl font-bold mb-6 text-center">Login</h2>
      <?php if (isset($error)) echo "<p class='text-red-400 text-sm mb-4 text-center'>$error</p>"; ?>
      <form method="POST" class="space-y-6">
        <div>
          <label class="block mb-2 font-semibold">Email</label>
          <input type="email" name="email" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter Email" required>
        </div>
        <div>
          <label class="block mb-2 font-semibold">Password</label>
          <input type="password" name="password" class="w-full p-4 rounded-xl bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-yellow-400 border border-white/30" placeholder="Enter Password" required>
        </div>
        <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black p-4 rounded-full font-bold text-lg transition">Login</button>
      </form>
      <p class="text-sm text-center mt-4">Don't have an account? <a href="register.php" class="text-yellow-400 hover:underline">Register</a></p>
    </div>
  </section>
</body>

</html>