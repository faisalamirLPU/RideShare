<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <nav class="admin-navbar">
        <div class="logo">
            <a href="dashboard.php">Admin Panel</a>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_users.php">Users</a></li>
            <li><a href="manage_rides.php">Rides</a></li>
            <li><a href="manage_bookings.php">Bookings</a></li>
            <li><a href="manage_payments.php">Payments</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>
