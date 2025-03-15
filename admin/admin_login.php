<?php
session_start();
require_once "../database/db_config.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $query = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION["admin_id"] = $admin["id"];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }

    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form action="admin_login.php" method="POST">
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
