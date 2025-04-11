<?php
require_once '../database/db_config.php';

$message = "";
$message_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $message = "❌ All fields are required.";
        $message_class = "text-red-600";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
        $message_class = "text-red-600";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
        $message_class = "text-red-600";
    } else {
        // Check if admin email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id);
            $stmt->fetch();

            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $admin_id);

            if ($update->execute()) {
                $message = "✅ Password reset successfully.";
                $message_class = "text-green-600";
            } else {
                $message = "❌ Failed to reset password.";
                $message_class = "text-red-600";
            }
            $update->close();
        } else {
            $message = "❌ Admin email not found.";
            $message_class = "text-red-600";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Admin</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">

<div class="max-w-xl mx-auto mt-24 bg-white shadow-lg p-8 rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">🔑 Reset Admin Password</h2>

    <?php if ($message): ?>
        <div class="mb-4 text-center font-semibold <?= htmlspecialchars($message_class) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block font-medium mb-1">Admin Email</label>
            <input type="email" name="email" required class="w-full border p-3 rounded" placeholder="Enter admin email">
        </div>
        <div>
            <label class="block font-medium mb-1">New Password</label>
            <input type="password" name="new_password" required class="w-full border p-3 rounded">
        </div>
        <div>
            <label class="block font-medium mb-1">Confirm New Password</label>
            <input type="password" name="confirm_password" required class="w-full border p-3 rounded">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 transition">
            Reset Password
        </button>
    </form>
</div>

</body>
</html>
