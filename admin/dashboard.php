<?php
session_start();
require_once "../database/db_config.php";

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch counts for dashboard statistics
$total_users = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$total_rides = $conn->query("SELECT COUNT(*) AS count FROM rides")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) AS count FROM bookings")->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE payment_status = 'success'");
$total_payments = $result->fetch_assoc()['total'] ?? 0; // Handle NULL values

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="admin-dashboard">
    <h1>Welcome, Admin</h1>
    
    <div class="dashboard-stats">
    <div class="stat-box">
            <h2><a href="admin_verify.php">Verify Driver</a></h2>
            
        </div>
        <div class="stat-box">
            <h2>Total Users</h2>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-box">
            <h2>Total Rides</h2>
            <p><?php echo $total_rides; ?></p>
        </div>
        <div class="stat-box">
            <h2>Total Bookings</h2>
            <p><?php echo $total_bookings; ?></p>
        </div>
        <div class="stat-box">
            <h2>Total Revenue</h2>
            <p>₹<?php echo number_format($total_payments, 2); ?></p>
        </div>
    </div>

    <div class="dashboard-links">
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_rides.php">Manage Rides</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="manage_payments.php">Manage Payments</a>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

</body>
</html>
