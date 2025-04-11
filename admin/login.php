<?php
session_start();
require_once '../database/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['name'] = $user['name'];

                if ($user['user_type'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    header("Location: ../admin/index.php");
                } else {
                    header("Location: ../users/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid credentials.";
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - RideShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-10 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">🚗 RideShare Login</h2>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- ✅ CORRECT FORM TAG -->
    <form action="" method="POST" class="space-y-5">
      <div>
        <label class="block mb-1 font-semibold text-gray-700">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Enter your email" />
      </div>
      <div>
        <label class="block mb-1 font-semibold text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Enter your password" />
      </div>
      <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 rounded-lg transition">Login</button>
    </form>

    <div class="text-center mt-4">
        <a href="change_password.php" class="text-sm text-blue-600 hover:underline">Forgot password? Reset</a>
    </div>
  </div>
</body>
</html>
