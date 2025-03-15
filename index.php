<?php
session_start();
require_once "database/db_config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Sharing Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include "includes/user_header.php"; ?>

<section class="hero-section">
    <h1>Find a Ride or Share Your Ride</h1>
    <p>Safe, Reliable, and Affordable Carpooling</p>
    <a href="rides/book_ride.php" class="btn">Find a Ride</a>
    <a href="rides/post_ride.php" class="btn">Offer a Ride</a>
</section>

<section class="ride-list">
    <h2>Available Rides</h2>
    <div class="rides-container">
        <?php
        // Fetch available rides
        $query = $conn->query("SELECT r.*, d.vehicle_model, u.name AS driver_name 
                               FROM rides r
                               JOIN drivers d ON r.driver_id = d.id
                               JOIN users u ON d.user_id = u.id
                               WHERE r.status = 'active'
                               ORDER BY r.created_at DESC
                               LIMIT 5");

        // Check if rides are available
        if ($query->num_rows > 0) {  // ✅ Fixed from rowCount() to num_rows
            while ($ride = $query->fetch_assoc()) {  // ✅ Use fetch_assoc() for MySQLi
                echo "<div class='ride-card'>
                        <h3>{$ride['pickup_location']} → {$ride['drop_location']}</h3>
                        <p>Driver: {$ride['driver_name']}</p>
                        <p>Vehicle: {$ride['vehicle_model']}</p>
                        <p>Fare: ₹{$ride['fare']}</p>
                        <p>Seats Available: {$ride['seats_available']}</p>
                        <a href='rides/ride_details.php?id={$ride['id']}' class='btn'>View Details</a>
                      </div>";
            }
        } else {
            echo "<p>No rides available at the moment.</p>";
        }
        ?>
    </div>
</section>

<?php include "includes/user_footer.php"; ?>

</body>
</html>
