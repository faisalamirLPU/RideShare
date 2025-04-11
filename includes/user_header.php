<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? null; // 'passenger' or 'driver'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Sharing</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
            <a href="../index.php">RideShare</a>
        </div>
        <ul class="nav-links">
            <li><a href="../rides/book_ride.php">Find a Ride</a></li>
            <?php if ($user_logged_in): ?>
                <?php if ($user_type === 'driver'): ?>
                    <li><a href="../rides/post_ride.php">Post a Ride</a></li>
                    <li><a href="../users/my_rides.php">My Rides</a></li>
                    <li><a href="../rides/choose_ride.php">Manage Requests</a></li>
                <?php else: ?>

                    <li><a href="../users/my_bookings.php">My Bookings</a></li>
                <?php endif; ?>
                <!-- <li><a href="../users/dashboard.php">Dashboard</a></li> -->
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="./auth/login.php">Login</a></li>
                <li><a href="./auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>


