<?php
session_start();
require_once '../database/db_config.php';

// Optional: Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users
$sql = "SELECT id, name, email, phone, user_type, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Users - Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <?php include 'include/header.php'; ?>

  <main class="max-w-6xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">👥 Manage Users</h1>

    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100 text-gray-600">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-medium">#</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Email</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Phone</th>
            <th class="px-6 py-3 text-left text-sm font-medium">User Type</th>
            <th class="px-6 py-3 text-left text-sm font-medium">Joined On</th>
            <th class="px-6 py-3 text-center text-sm font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
          <?php if ($result->num_rows > 0): ?>
            <?php $i = 1; while ($user = $result->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?php echo $i++; ?></td>
                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['name']); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['phone']); ?></td>
                <td class="px-6 py-4 text-sm capitalize"><?php echo $user['user_type']; ?></td>
                <td class="px-6 py-4 text-sm"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                <td class="px-6 py-4 text-center">
                  <!-- Placeholder for actions like Edit/Delete -->
                  <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:underline text-sm mr-3">Edit</a>
                  <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="text-red-600 hover:underline text-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">No users found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <?php include 'include/footer.php'; ?>
</body>
</html>
