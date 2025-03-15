<?php
// auth/register.php
include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = trim($_POST['user_type']); // 'passenger' or 'driver'

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
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="register-container">
    <h2>Register for RideShare</h2>
    <form action="register.php" method="POST" onsubmit="return validateForm()">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="user_type">Register as:</label>
        <select id="user_type" name="user_type" required>
            <option value="passenger">Passenger</option>
            <option value="driver">Driver</option>
        </select>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
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
