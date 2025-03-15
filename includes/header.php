<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../views/index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../views/dashboard.php">Dashboard</a>
                <a href="../views/profile.php">Profile</a>
                <a href="../views/logout.php">Logout</a>
            <?php else: ?>
                <a href="../views/login.php">Login</a>
                <a href="../views/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
