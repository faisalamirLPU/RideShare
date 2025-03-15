<?php
session_start();
require_once "../database/db_config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long!";
    } else {
        // Check if email exists
        $query = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $admin = $result->fetch_assoc();
        $query->close();

        if ($admin) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $update_query = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
            $update_query->bind_param("ss", $hashed_password, $email);
            if ($update_query->execute()) {
                $message = "Password changed successfully! <a href='admin_login.php'>Login here</a>";
            } else {
                $message = "Error updating password!";
            }
            $update_query->close();
        } else {
            $message = "Email not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="change-password-container">
    <h2>Admin Change Password</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    
    <form method="POST">
        <label>Enter Your Email:</label>
        <input type="email" name="email" required>

        <label>New Password:</label>
        <input type="password" name="new_password" required>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Change Password</button>
    </form>
</div>

</body>
</html>
